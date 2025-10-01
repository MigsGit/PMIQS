<?php

namespace App\Http\Controllers;

use App\Models\Ecr;
use App\Models\Machine;
use Illuminate\Http\Request;
use App\Models\MachineApproval;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Models\ExternalDisposition;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Interfaces\ResourceInterface;
use App\Exports\InternalMachineExport;
use App\Http\Requests\MachineFileRequest;

class MachineController extends Controller
{
    protected $resourceInterface;
    protected $commonInterface;
    public function __construct(ResourceInterface $resourceInterface,CommonInterface $commonInterface) {
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
    }
    public function saveMachine(Request $request, MachineFileRequest $machineFileRequest){
        try {
            DB::beginTransaction();
            $machineRequestValidated = [];
            $ecrsId = $machineFileRequest->ecrsId;
            $machinesId = $machineFileRequest->machinesId;

            if($machineFileRequest->hasfile('machineRefBefore') && $machineFileRequest->hasfile('machineRefAfter')){
               $arrUploadFile = $this->commonInterface->uploadFileImg($machineFileRequest->machineRefBefore,$machineFileRequest->machineRefAfter,$machinesId,'machine');
                $impOriginalFilenameBefore = implode(' | ',$arrUploadFile['arr_original_filename_before']);
                $impFilteredDocumentNameBefore = implode(' | ',$arrUploadFile['arr_filtered_document_name_before']);
                $impOriginalFilenameAfter = implode(' | ',$arrUploadFile['arr_original_filename_after']);
                $impFilteredDocumentNameAfter = implode(' | ',$arrUploadFile['arr_filtered_document_name_after']);

                $machineRequestValidated['original_filename_before'] = $impOriginalFilenameBefore;
                $machineRequestValidated['filtered_document_name_before'] = $impFilteredDocumentNameBefore;
                $machineRequestValidated['original_filename_after'] = $impOriginalFilenameAfter;
                $machineRequestValidated['filtered_document_name_after'] = $impFilteredDocumentNameAfter;

            }
            $conditions = [
                'id' =>  $machinesId
            ];
            $this->resourceInterface->updateConditions(Machine::class,$conditions,$machineRequestValidated);
            $arrMachineApprovalRequest = [
                'PRDNAB'  => $request->prdnAssessedBy,
                'PRDNCB'  => $request->prdnCheckedBy,
                'PPCAB'   => $request->ppcAssessedBy,
                'PPCCB'   => $request->ppcCheckedBy,
                'MENGAB'  => $request->proEnggAssessedBy,
                'MENGCB'  => $request->proEnggCheckedBy,
                'PENGAB'  => $request->mainEnggAssessedBy,
                'PENGCB'  => $request->mainEnggCheckedBy,
                'LQCAB'   => $request->qcAssessedBy,
                'LQCCB'   => $request->qcCheckedBy,
            ];

           $machineApprovalValidated = collect($arrMachineApprovalRequest)->flatMap(function ($users,$approvalStatus) use ($request,$ecrsId){
                return collect($users)->map(function ($userId) use ($request,$approvalStatus,&$ecrsId){
                    return [
                        'ecrs_id' => $ecrsId,
                        'machines_id' => $request->machinesId,
                        'rapidx_user_id' => $userId == 0 ? NULL : $userId,
                        'approval_status' => $approvalStatus,
                        'created_at' => now(),
                    ];
                });

            })->toArray();
            MachineApproval::where('machines_id',$machinesId)->delete();
            MachineApproval::insert($machineApprovalValidated);
            $machineApproval =  MachineApproval::whereNotNull('rapidx_user_id')
            ->where('Machines_id', $machinesId)->first();
            if ($machineApproval) {
                $machineApproval->update(['status' => 'PEN']);
                Machine::where('id', $machinesId)->first()
                ->update([
                    'approval_status' => $machineApproval->approval_status,
                    'status' => 'FORAPP', //FOR APPROVAL
                ]);
            }
            //Reset the PMI Approval
            /*
                PmiApproval::whereNotNull('rapidx_user_id')
                ->where('ecrs_id', $currentEcrsId)
                ->update([
                    'status' => '-',
                    'remarks' => '',
                ]);
                //Update Pending PMI Approval
                $firstPmiApproval =  PmiApproval::whereNotNull('rapidx_user_id')
                ->where('ecrs_id', $currentEcrsId)
                ->first();
                if ($firstPmiApproval) {
                    $firstPmiApproval->update(['status' => 'PEN']);
                }
            */
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveMachineApproval(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $selectedId = $request->selectedId;
            //Get Current Ecr Approval is equal to Current Session
            $machineApprovalCurrent = MachineApproval::where('machines_id',$selectedId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            if($machineApprovalCurrent->rapidx_user_id != session('rapidx_user_id')){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }
            //Update the machine Approval Status
            $machineApprovalCurrent->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);
            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
           $machineApproval = MachineApproval::where('machines_id',$selectedId)
           ->whereNotNull('rapidx_user_id')
           ->where('status','-')
           ->limit(1)
           ->get(['id','approval_status']);
            if ( count($machineApproval) != 0){
                $machineApprovalValidated = [
                    'status' => 'PEN',
                ];
                $machineApprovalConditions = [
                    'id' => $machineApproval[0]->id,
                ];
                $this->resourceInterface->updateConditions(MachineApproval::class,$machineApprovalConditions,$machineApprovalValidated);
                //Update the ECR Approval Status
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'approval_status' => $machineApproval[0]->approval_status,
                ];
                $this->resourceInterface->updateConditions(Machine::class,$enviromentConditions,$enviromentValidated);
            }else{
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'status' => 'PMIAPP',
                    'approval_status' => 'PB',
                ];
                $this->resourceInterface->updateConditions(Machine::class,$enviromentConditions,$enviromentValidated);
            }
             //DISAPPROVED ECR
             if($request->status === "DIS"){
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'status' => 'DIS',
                    'approval_status' => 'DIS', //Repeat the status
                ];
                $this->resourceInterface->updateConditions(Machine::class,$enviromentConditions,$enviromentValidated);
            }
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function loadMachineApproverSummaryId (Request $request){
        try {
            $machinesId = $request->machinesId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'machines_id' => $machinesId
            ];
            $machineApproval = $this->resourceInterface->readCustomEloquent(MachineApproval::class,$data,$relations,$conditions);
            $machineApproval = $machineApproval
            ->whereNotNull('rapidx_user_id')
            ->orderBy('id','asc')
            ->whereNull('deleted_at')
            ->get();
            return DataTables($machineApproval)
            ->addColumn('get_count',function ($row) use(&$ctr){
                $ctr++;
                $result = '';
                $result .= $ctr;
                $result .= '</br>';
                return $result;
            })
            ->addColumn('get_approver_name',function ($row){
                $result = '';
                $result .= $row->rapidx_user['name'];
                $result .= '</br>';
                return $result;
            })
            ->addColumn('get_role',function ($row){
                $getApprovalStatus = $this->getApprovalStatus($row->approval_status);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="badge rounded-pill bg-primary"> '.$getApprovalStatus['approvalStatus'].'</span>';
                $result .= '<center>';
                $result .= '</br>';
                return $result;
            })
            ->addColumn('get_status',function ($row){
                switch ($row->status) {

                    case 'PEN':
                        $status = 'PENDING';
                        $bgColor = 'badge rounded-pill bg-warning';
                        break;
                    case 'APP':
                        $status = 'APPROVED';
                        $bgColor = 'badge rounded-pill bg-success';
                        break;
                    case 'DIS':
                        $status = 'DISAPPROVED';
                        $bgColor = 'badge rounded-pill bg-danger';
                        break;
                    default:
                        $status = '---';
                        $bgColor = '';
                        break;
                }

                $result = '';
                $result .= '<center>';
                $result .= '<span class="'.$bgColor.'"> '.$status.' </span>';
                $result .= '<br>';
                $result .= '</br>';
                return $result;
            })
            ->rawColumns(['get_count','get_status','get_approver_name','get_role'])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function loadEcrMachineByStatus(Request $request){
        try {
            $adminAccess = $request->adminAccess;
            $data = [];
            $relations = [
                'machine.machine_approvals_pending',
                'machine',
            ];
            $conditions = [
                'status' => 'OK',
                'category' => $request->category
            ];
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,$data,$relations,$conditions);

            if( $adminAccess === 'null' || blank($adminAccess) ){
                return $ecr->whereHas('machine.machine_approvals_pending',function($query){
                    // if is adminAccess exist deactivate the session condition
                    $query->where('rapidx_user_id',session('rapidx_user_id'));
                })->get();
            }

            if( $adminAccess === 'created'){
                $ecr->where('created_by' , session('rapidx_user_id'))
                ->get();
            }
            if( $adminAccess === 'all') {
                $ecr->get();
            }
            if ( $adminAccess === 'pmi') {
                $data = [];
                $relations = [
                    'pmi_approvals_pending',
                    'machine',
                ];
                $conditions = [
                    'status' => 'OK',
                    'category' => $request->category
                ];
                $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,$data,$relations,$conditions);
                // Check PMI approvals instead
                $ecr->whereHas('pmi_approvals_pending', function ($query) {
                    $query->where('status', 'PEN')
                    ->where('rapidx_user_id',session('rapidx_user_id'));
                });
            }
            return DataTables($ecr)
            ->addColumn('get_actions',function ($row) use ($request){
                // Dropdown menu links
                $machineStatus = $row->machine->status ?? "";
                $pmiApprovalsPending = $row->pmi_approvals_pending[0]->rapidx_user->id ?? "";
                $currentApprover = $row->machine->machine_approvals_pending[0]['rapidx_user']['id'] ?? '';

                $result = "";
                $result .= '<center>';
                $result .= '<div class="btn-group dropstart mt-4">';
                $result .= '<button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">';
                $result .= '    Action';
                $result .= '</button>';
                $result .= '<ul class="dropdown-menu">';
                if($machineStatus === "EXDISPO" || $machineStatus === "OK"){
                    //Upload External Disposition
                    // $result .= '<li><button class="dropdown-item" type="button" ecrs-id="'.$row->id.'" id="btnViewDispotionById"><i class="fa-solid fa-file"></i> &nbsp;Upload Disposition</button></li>';
                    $result .= '<li><button class="dropdown-item" type="button" machines-id="'.$row->machine->id.'" ecrs-id="'.$row->id.'" machine-status= "'.$machineStatus.'" id="btnViewMachineById"><i class="fa-solid fa-eye"></i> &nbsp;View/Approval</button></li>';
                    return $result;
                }
                if($row->created_by === session('rapidx_user_id')){
                    $result .= '   <li><button class="dropdown-item" type="button" machines-id="'.$row->machine->id.'" ecrs-id="'.$row->id.'" machine-status= "'.$machineStatus.'" id="btnGetEcrId"><i class="fa-solid fa-edit"></i> &nbsp;Edit</button></li>';
                }
                if($pmiApprovalsPending === session('rapidx_user_id') || $currentApprover ===  session('rapidx_user_id')){
                    $result .= '<li><button class="dropdown-item" type="button" machines-id="'.$row->machine->id.'" ecrs-id="'.$row->id.'" machine-status= "'.$machineStatus.'" id="btnViewMachineById"><i class="fa-solid fa-eye"></i> &nbsp;View/Approval</button></li>';
                }



                $result .= '</ul>';
                $result .= '</div>';
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_status',function ($row) use($request){
                $machineStatus = $row->machine->status ?? "";
                $currentApprover = $row->machine->machine_approvals_pending[0]['rapidx_user']['name'] ?? '';
                $getStatus = $this->getStatus($machineStatus);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
                $result .= '<br>';
                $getApprovalStatus = $this->getApprovalStatus($row->machine->approval_status);
                if($row->status != 'DIS' && $currentApprover != ''){
                    $result .= '<span class="badge rounded-pill bg-danger"> '.$getApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                }
                if( $machineStatus === 'PMIAPP' ){ //TODO: Last Status PMI Internal
                    $currentApprover = $row->pmi_approvals_pending[0]['rapidx_user']['name'] ?? '';
                    $approvalStatus = $row->machine->approval_status;
                    $getPmiApprovalStatus = $this->commonInterface->getPmiApprovalStatus($approvalStatus);
                    $result .= '<span class="badge rounded-pill bg-danger"> '.$getPmiApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                }
                $result .= '</center>';
                $result .= '</br>';
                return $result;
            })
            ->addColumn('get_attachment',function ($row) use ($request){
                $machineStatus = $row->machine->status ?? "";
                $result = '';
                $result .= '<center>';
                $result .= '<a class="btn btn-outline-danger btn-sm mr-1 mt-3" type="button" machine-id="'.$row->machine->id.'" ecrs-id="'.$row->id.'" machine-status= "'.$machineStatus.'" id="btnViewMachineRef"><i class="fa-solid fa-download"></i>Attachment</a>';
                $result .= '</center>';
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
    public function getMachineRefById(Request $request){
        try {
            $machinesId = $request->machinesId;
            $conditions = [
                'id' => $machinesId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(Machine::class,[],[],$conditions);
            $machineRefById = $data
            ->get([
                'id',
                'ecrs_id',
                'original_filename_before',
                'original_filename_after',
            ]);
            $externalDispoConditions = [
                'ecrs_id' => $request->ecrsId,
            ];
            $externalDispoData = $this->resourceInterface->readCustomEloquent(ExternalDisposition::class,[],[],$externalDispoConditions);
            $externalDispoEcrsId = $externalDispoData
            ->get([
                'id',
                'ecrs_id',
                'original_filename',
            ]);
            if ( filled($machineRefById) ){
                $arrMethodRefResponse = [
                    'originalFilenameBefore'=> explode(' | ',$machineRefById[0]->original_filename_before),
                    'originalFilenameAfter'=> explode(' | ',$machineRefById[0]->original_filename_after),
                    'methodsId'=> encrypt($machineRefById[0]->id),
                ];
            }
            if ( filled($externalDispoEcrsId) ){
                $arrExternalDispoResponse = [
                    'originalFilenameExternalDisposition'=> explode(' | ',$externalDispoEcrsId[0]->original_filename),
                    'ecrsId'=> encrypt($externalDispoEcrsId[0]->ecrs_id),
                ];
            }
            return response()->json(['isSuccess' => 'true' ,array_merge($arrMethodRefResponse??[],$arrExternalDispoResponse??[])]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function viewMachineRef(Request $request){
        try {
            $machinesId = decrypt($request->machinesId);
            $conditions = [
                'id' => $machinesId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(Machine::class,[],[],$conditions);
            $materialRefById = $data
            ->get([
                'filtered_document_name_before',
                'filtered_document_name_after',
                'file_path',
            ]);

            if( filled($materialRefById) ){
                if ($request->imageType === "before"){
                   $arrFilteredDocumentName = explode(' | ' ,$materialRefById[0]->filtered_document_name_before);
                    $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
                    $filePathWithEcrsId = $materialRefById[0]->file_path."/".$machinesId."/". "$request->imageType"."/".$selectedFilteredDocumentName;
                    $filePath = "app/public/".$filePathWithEcrsId."";
                }
                if ($request->imageType === "after"){
                    $arrFilteredDocumentName = explode(' | ' ,$materialRefById[0]->filtered_document_name_after);
                    $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
                    $filePathWithEcrsId = $materialRefById[0]->file_path."/".$machinesId."/". "$request->imageType"."/".$selectedFilteredDocumentName;
                    $filePath = "app/public/".$filePathWithEcrsId."";
                }
                // $this->commonInterface->viewImageFile($filePath);
                $path = storage_path($filePath);
                if (!file_exists($path)) {
                    abort(404, 'Image not found');
                }
                return response()->file($path);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function downloadInternalMachine(Request $request){
        try {

            $iqc_dropdown_category_section = 'TS';
            $test = 'test';

            return Excel::download(
                new InternalMachineExport($test),
                $iqc_dropdown_category_section . "4M.xlsx"
            );
        } catch (Exception $e) {
            throw $e;
        }
    }
    //Common Function
    public function getStatus($status){

        try {
             switch ($status) {
                 case 'RUP':
                     $status = 'For Requestor Update';
                     $bgStatus = 'badge rounded-pill bg-primary';
                     break;
                case 'FORAPP':
                    $status = 'For Approval';
                    $bgStatus = 'badge rounded-pill bg-warning';
                    break;
                case 'PMIAPP':
                    $status = 'PMI Approval';
                    $bgStatus = 'badge rounded-pill bg-info';
                    break;
                case 'OK':
                    $status = 'Completed';
                    $bgStatus = 'badge rounded-pill bg-success';
                    break;
                case 'DIS':
                     $status = 'DISAPPROVED';
                     $bgStatus = 'badge rounded-pill bg-danger';
                     break;
                case 'EXDISPO':
                    $status = 'Waiting for External Disposition';
                    $bgStatus = 'badge rounded-pill bg-warning';
                    break;
                 default:
                     $status = '';
                     $bgStatus = '';
                     break;
             }
             return [
                 'status' => $status,
                 'bgStatus' => $bgStatus,
             ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getApprovalStatus($approval_status){
        try {
             switch ($approval_status) {
                case 'PRDNAB':
                    $approvalStatus = 'Production Assessed by:';
                    break;
                case 'PRDNCB':
                    $approvalStatus = 'Production Checked by:';
                    break;
                case 'PPCAB':
                    $approvalStatus = 'PPC Assessed by:';
                    break;
                case 'PPCCB':
                    $approvalStatus = 'PPC Checked by:';
                    break;
                case 'LQCAB':
                    $approvalStatus = 'QC Assessed by';
                    break;
                case 'LQCCB':
                    $approvalStatus = 'QC Checked by';
                    break;

                case 'MENGAB':
                    $approvalStatus = 'Maintenance Engg Assessed by';
                    break;
                case 'MENGCB':
                    $approvalStatus = 'Maintenance Engg Checked by';
                    break;
                case 'PENGAB':
                    $approvalStatus = 'Process Engg Assessed by';
                    break;
                case 'PENGCB':
                    $approvalStatus = 'Process Engg Checked by';
                    break;
                 default:
                     $approvalStatus = '';
                     break;
             }
             return [
                 'approvalStatus' => $approvalStatus,
             ];

        } catch (Exception $e) {
            throw $e;
        }
    }
}
