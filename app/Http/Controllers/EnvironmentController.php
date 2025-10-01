<?php

namespace App\Http\Controllers;

use App\Models\Ecr;
use App\Models\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Interfaces\ResourceInterface;
use App\Http\Requests\EnvironmentRequest;

class EnvironmentController extends Controller
{
    protected $resourceInterface;
    protected $commonInterface;
    public function __construct(ResourceInterface $resourceInterface,CommonInterface $commonInterface) {
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
    }
    public function loadEcrEnvironmentByStatus(Request $request){
        try {
            $adminAccess = $request->adminAccess;
            $data = [];
            $relations = [
                'pmi_approvals_pending',
                'environment',
            ];
            $conditions = [
                'status' => 'OK',
                'category' => $request->category
            ];
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,$data,$relations,$conditions);

            $ecr->whereNull('deleted_at');
            if( $adminAccess === 'null' || blank($adminAccess) || $adminAccess === 'pmi' ){
                $ecr->whereHas('pmi_approvals_pending', function ($query) {
                    $query->where('rapidx_user_id',session('rapidx_user_id'));
                });
            }
            if( $adminAccess === 'created'){
                $ecr->where('created_by' , session('rapidx_user_id'))
                ->get();
            }
            if( $adminAccess === 'all') {
                $ecr->get();
            }
            return DataTables($ecr)
            ->addColumn('get_actions',function ($row) use ($request){
                $result = "";
                $result .= '<center>';
                $result .= '<div class="btn-group dropstart mt-4">';
                $result .= '<button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">';
                $result .= '    Action';
                $result .= '</button>';
                $result .= '<ul class="dropdown-menu">';
                if($row->approval_status === "PB" && $row->created_by === session('rapidx_user_id')){
                    $result .= '   <li><button class="dropdown-item" type="button" ecr-id="'.$row->id.'" id="btnGetEcrId"><i class="fa-solid fa-edit"></i> &nbsp;Edit</button></li>';
                    $result .= '   <li><button class="dropdown-item" type="button" ecr-id="'.$row->id.'" id="btnDownloadEnvironmentRef"><i class="fa-solid fa-upload"></i> &nbsp;Upload File</button></li>';
                }
                // if($row->pmi_approvals_pending[0]->rapidx_user->id === session('rapidx_user_id')){
                    $result .= '   <li><button class="dropdown-item" type="button" ecr-id="'.$row->id.'" id="btnViewEcrById"><i class="fa-solid fa-eye"></i> &nbsp;View/Approval</button></li>';
                // }
                $result .= '</ul>';
                $result .= '</div>';
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_status',function ($row) use($request){
                $currentApprover = $row->pmi_approvals_pending[0]['rapidx_user']['name'] ?? '';
                $approvalStatus = $row->environment->approval_status;
                $getApprovalStatus = $this->commonInterface->getPmiApprovalStatus($approvalStatus);
                $result = '';
                $result .= '<center>';
                // $result .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
                $result .= '<br>';
                $result .= '<span class="badge rounded-pill bg-danger"> '.$getApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                $result .= '</center>';
                $result .= '</br>';
                return $result;
            })
            ->addColumn('get_details',function ($row) use($request){
                $result = '';
                $result .= '<p class="card-text"><strong>Customer Name:</strong> ' . $row->customer_name . '</p>';
                $result .= '<p class="card-text"><strong>Part Number:</strong> ' . $row->part_no . '</p>';
                $result .= '<p class="card-text"><strong>Part Name:</strong> ' . $row->part_name . '</p>';
                $result .= '<p class="card-text"><strong>Device Code:</strong> ' . $row->device_name . '</p>';
                $result .= '<p class="card-text"><strong>Product Line:</strong> ' . $row->product_line . '</p>';
                $result .= '<p class="card-text"><strong>Date of Request:</strong> ' . $row->date_of_request . '</p>';
                $result .= '<p class="card-text"><strong>Created By:</strong> ' . $row->rapidx_user_created_by->name ?? '' . '</p>';
                return $result;
            })
            ->addColumn('get_attachment',function ($row) use ($request){
                $result = '';
                $result .= '<center>';
                if($request->category  === 'Environment'){
                    $result .= "<a class='btn btn-outline-danger btn-sm mr-1 mt-3 btn-get-ecr-id' ecr-id='".$row->id."' id='btnViewEnvironmentRef'><i class='fa-solid fa-file-pdf'></i></a>";
                }
                $result .= '</center>';
                return $result;
            })
            ->rawColumns([
                'get_actions',
                'get_status',
                'get_attachment',
                'get_details',
            ])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function uploadEnvironmentRef(EnvironmentRequest $environmentRequest){
        date_default_timezone_set('Asia/Manila');
        DB::beginTransaction();
        try {
            if($environmentRequest->hasfile('environment_ref') ){
                $arrUploadFile = $this->commonInterface->uploadFile($environmentRequest->environment_ref,$environmentRequest->ecrsId,'environment');
                $impOriginalFilename = implode(' | ',$arrUploadFile['arr_original_filename']);
                $impFilteredDocumentName = implode(' | ',$arrUploadFile['arr_filtered_document_name']);

                $conditions = [
                   'ecrs_id' =>  $environmentRequest->ecrs_id
                ];
                $environmentRequestValidated['original_filename'] = $impOriginalFilename;
                $environmentRequestValidated['filtered_document_name'] = $impFilteredDocumentName;
                $this->resourceInterface->updateConditions(Environment::class,$conditions,$environmentRequestValidated);
            }
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function viewEnvironmentRef(Request $request){
        try {
            $ecrsId = decrypt($request->ecrsId);
            $conditions = [
                'ecrs_id' => $ecrsId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(Environment::class,[],[],$conditions);
            $environmentRefByEcrsId = $data
            ->get([
                'filtered_document_name',
                'file_path',
            ]);
            if(count($environmentRefByEcrsId) != 0){
                $arrFilteredDocumentName = explode(' | ' ,$environmentRefByEcrsId[0]->filtered_document_name);
                $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
                $filePathWithEcrsId = $environmentRefByEcrsId[0]->file_path."/".$ecrsId."/".$selectedFilteredDocumentName;
                $pdfPath = storage_path("app/public/".$filePathWithEcrsId."");
                $this->commonInterface->viewPdfFile($pdfPath);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getEnvironmentRefByEcrsId(Request $request){
        try {
            $ecrsId = $request->ecrsId;
            $conditions = [
                'ecrs_id' => $ecrsId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(Environment::class,[],[],$conditions);
            $environmentRefByEcrsId = $data
            ->get([
                'id',
                'ecrs_id',
                'original_filename',
            ]);
            return response()->json([
                'isSuccess' => 'true',
                'originalFilename'=> explode(' | ',$environmentRefByEcrsId[0]->original_filename),
                'ecrsId'=> encrypt($environmentRefByEcrsId[0]->ecrs_id),
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
