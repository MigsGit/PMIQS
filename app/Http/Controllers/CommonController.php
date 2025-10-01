<?php

namespace App\Http\Controllers;

use Mail;
use Helpers;
use App\Models\Ecr;
use App\Models\Man;
use App\Models\User;
use App\Models\Method;
use App\Models\Machine;
use App\Models\Material;
use App\Models\ManDetail;
use App\Models\RapidxUser;
use App\Models\EcrApproval;
use App\Models\Environment;
use App\Models\ManApproval;
use App\Models\PmiApproval;
use App\Models\RapidMailer;
use Illuminate\Http\Request;
use App\Models\MethodApproval;
use App\Models\MachineApproval;
use App\Models\MaterialApproval;
use App\Models\SpecialInspection;
use App\Exports\ExternalCcmExport;
use App\Interfaces\EmailInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Models\ExternalDisposition;
use Maatwebsite\Excel\Facades\Excel;
use App\Interfaces\ResourceInterface;
use App\Http\Requests\SpecialInspectionRequest;


class CommonController extends Controller
{
    protected $resourceInterface;
    protected $commonInterface;
    protected $emailInterface;
    public function __construct(
        ResourceInterface $resourceInterface,
        CommonInterface $commonInterface,
        EmailInterface $emailInterface
    ) {
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
        $this->emailInterface = $emailInterface;
    }
    public function loadSpecialInspectionByEcrId(Request $request){
        try {
            $ecrsId = $request->ecrsId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'ecrs_id' => $ecrsId
            ];
            $data = $this->resourceInterface->readCustomEloquent(SpecialInspection::class,$data,$relations,$conditions);
            $specialInspection = $data->get();
            return DataTables($specialInspection)
            ->addColumn('get_actions',function ($row){
                $result = '';
                $result .= '<center>';
                $result .= "<button type='button' class='btn btn-outline-info btn-sm mr-1' special-inspections-id='".$row->id."' id='btnGetSpecialInspectionId'> <i class='fa-solid fa-pen-to-square'></i></button>";
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_inspector',function ($row){
                $result = '';
                $result .= '<center>';
                $result .= $row->rapidx_user['name'] ?? "---";
                $result .= '</center>';
                return $result;
            })
            ->rawColumns([
                'get_actions',
                'get_inspector',
            ])
            ->make(true);
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function loadPmiInternalApprovalSummary(Request $request){
        try {
            $ecrsId = $request->ecrsId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'ecrs_id' => $ecrsId
            ];
            $pmiApproval = $this->resourceInterface->readCustomEloquent(PmiApproval::class,$data,$relations,$conditions);
            $pmiApproval = $pmiApproval
            ->whereNotNull('rapidx_user_id')
            ->orderBy('counter','asc')
            ->get();
            return DataTables($pmiApproval)
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
                $getApprovalStatus = $this->commonInterface->getPmiApprovalStatus($row->approval_status);
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
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getRapidxUserByIdOpt(Request $request){
        try {
            $rapidxUserDeptGroup = $request->rapidxUserDeptGroup ?? "N/A";
            $isApprover = $request->isApprover ?? "N/A";
            $rapidxUserDeptGroupQuery = $rapidxUserDeptGroup === "N/A" ? '': 'AND departments.department_group = "'.$rapidxUserDeptGroup.'"';

            $userApprover = User::where('roles','APP')->get();
            $userApproverCollection = collect($userApprover);
            $userApproverCollectionImplode = $userApproverCollection->pluck('rapidx_user_id')->implode(', ');
            $rapidxUserIdGroupQuery = $isApprover === "N/A" ? '': 'AND users.id IN('.$userApproverCollectionImplode.')';
            if($isApprover === "true"){
                $rapidxUserById = DB::connection('mysql_rapidx')->select('SELECT users.*,user_accesses.
                    module_id,departments.department_name,departments.department_group
                    FROM  users
                    LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                    LEFT JOIN departments departments ON departments.department_id = users.department_id
                    WHERE 1=1
                    -- AND departments.department_group = "'.$request->rapidxUserDeptGroup.'"
                    '.$rapidxUserDeptGroupQuery.'
                    '.$rapidxUserIdGroupQuery.'
                    AND users.user_stat = 1
                    AND user_accesses.module_id = 46'
                );
                if(count ($rapidxUserById) > 0){
                    return response()->json(['isSuccess' => 'true','rapidxUserById'=>$rapidxUserById]);
                }
                return response()->json(['isSuccess' => 'false','rapidxUserById'=>[],'msg' => 'User Not Found !',],500);
            }

            $rapidxUserById = DB::connection('mysql_rapidx')->select('SELECT users.*,user_accesses.
                module_id,departments.department_name,departments.department_group
                FROM  users
                LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                LEFT JOIN departments departments ON departments.department_id = users.department_id
                WHERE 1=1
                -- AND departments.department_group = "'.$request->rapidxUserDeptGroup.'"
                '.$rapidxUserDeptGroupQuery.'
                AND users.user_stat = 1
                AND user_accesses.module_id = 46'
            );
            if(count ($rapidxUserById) > 0){
                return response()->json(['isSuccess' => 'true','rapidxUserById'=>$rapidxUserById]);
            }
            return response()->json(['isSuccess' => 'false','rapidxUserById'=>[],'msg' => 'User Not Found !',],500);
        } catch (Exception $e) {
            return response()->json(['isSuccess' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getNoModuleRapidxUserByIdOpt(Request $request){
        try {
            $rapidxUserById = DB::connection('mysql_rapidx')->select('SELECT users.id,users.name
                FROM  users users
                LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                WHERE 1=1
                AND user_stat = 1
                -- AND user_accesses.user != users.id
                GROUP BY users.id,users.name
                '
            );
            if(count ($rapidxUserById) > 0){
                return response()->json(['isSuccess' => 'true','rapidxUserById'=>$rapidxUserById]);
            }
            return response()->json(['isSuccess' => 'false','rapidxUserById'=>[],'msg' => 'User Not Found !',],500);
        } catch (Exception $e) {
            return response()->json(['isSuccess' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getCurrentApproverSession(Request $request){
        try {
            switch  ($request->approvalType) {
                case 'ecrApproval':
                    $currentModel = EcrApproval::class;
                    $conditions = [
                        'ecrs_id' => $request->selectedId,
                        'status' => 'PEN',
                    ];
                    $data = [
                        'rapidx_user_id'
                    ];
                    break;
                case 'manApproval':
                    $currentModel = ManApproval::class;
                    $conditions = [
                        'ecrs_id' => $request->selectedId,
                        'status' => 'PEN',
                    ];
                    $data = [
                        'rapidx_user_id'
                    ];
                    break;
                case 'materialApproval':
                    $currentModel = MaterialApproval::class;
                    $conditions = [
                        'materials_id' => $request->selectedId,
                        'status' => 'PEN',
                    ];
                    $data = [
                        'rapidx_user_id'
                    ];
                    break;
                case 'machineApproval':
                    $currentModel = MachineApproval::class;
                    $conditions = [
                        'machines_id' => $request->selectedId,
                        'status' => 'PEN',
                    ];
                    $data = [
                        'rapidx_user_id'
                    ];
                    break;
                case 'methodApproval':
                    $currentModel = MethodApproval::class;
                    $conditions = [
                        'methods_id' => $request->selectedId,
                        'status' => 'PEN',
                    ];
                    $data = [
                        'rapidx_user_id'
                    ];
                    break;
                case 'pmiApproval':
                    $currentModel = PmiApproval::class;
                    $conditions = [
                        'ecrs_id' => $request->selectedId,
                        'status' => 'PEN',
                    ];
                    $data = [
                        'rapidx_user_id'
                    ];
                    break;
                default:
                    //TODO:Error Handling
                    break;
            }

            $relations = [];
            $approvalQuery = $this->resourceInterface->readCustomEloquent($currentModel,$data,$relations,$conditions);
           $approval = $approvalQuery
            ->whereNotNull('rapidx_user_id')
            ->get();
          if( count($approval) ){
            $isSessionApprover =  session('rapidx_user_id') ===  $approval[0]->rapidx_user_id ? true: false ;
          }
            // $approval[0]->rapidx_user_id;
            return response()->json(['isSuccess' => 'true','isSessionApprover'=>$isSessionApprover ?? false,'type'=>$currentModel]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getCurrentPmiInternalApprover(Request $request){
        try {
            $conditions = [
                'ecrs_id' => $request->ecrsId, //TODO: Ecr Id
                'status' => 'PEN',
            ];
            $data = [
                'rapidx_user_id'
            ];
            $relations = [

            ];
            $ecrApprovalQuery = $this->resourceInterface->readCustomEloquent(PmiApproval::class,$data,$relations,$conditions);
            $ecrApproval =  $ecrApprovalQuery->get();
            $isSessionPmiInternalApprover =  session('rapidx_user_id') ===  $ecrApproval[0]->rapidx_user_id ? true: false ;
            return response()->json(['isSuccess' => 'true','isSessionPmiInternalApprover'=>$isSessionPmiInternalApprover]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getSpecialInspectionById(Request $request){
        try {
            $conditions = [
                'id' => $request->specialInspectionsId,
            ];
            $data = [];
            $relations = [];
            $data = $this->resourceInterface->readCustomEloquent(SpecialInspection::class,$data,$relations,$conditions);
            $specialInspection =  $data->get();
            return response()->json(['isSuccess' => 'true','specialInspection'=>$specialInspection[0]]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function savePmiInternalApproval(Request $request){ //internal_external
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $ecrsId = $request->ecrsId;
            //Get Current Ecr Approval is equal to Current Session
            $pmiInternalApprovalCurrent = PmiApproval::where('ecrs_id',$ecrsId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            if($pmiInternalApprovalCurrent->rapidx_user_id != session('rapidx_user_id')){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }
            //Get the ECR Category
            $ecr = Ecr::where('id',$ecrsId)
            ->whereNull('deleted_at')
            ->limit(1)
            ->get(['category','internal_external']);
            $isCategory = $ecr[0]->category;
            switch ($isCategory) {
                case 'Man':
                    $currentModel = Man::class;
                    break;
                case 'Material':
                    $currentModel = Material::class;
                    break;
                case 'Machine':
                    $currentModel = Machine::class;
                    break;
                case 'Method':
                    $currentModel = Method::class;
                    break;
                case 'Environment':
                    $currentModel = Environment::class;
                    $isEnvironmentRefFileExist = Environment::where('ecrs_id',$ecrsId)
                    ->whereNotNull('original_filename')
                    ->count();
                    if ( $isEnvironmentRefFileExist === 0){
                        return response()->json(['isSuccess' => 'false','msg' => 'Please upload Environment Reference File !'],500);
                    }
                    break;
                default:
                    return response()->json(['isSuccess' => 'false','msg' => 'Unknown Model!'],500);
                    break;
            }
            //DISAPPROVED ECR
            if($request->status === "DIS"){
                $enviromentConditions = [
                    'ecrs_id' => $ecrsId,
                ];
                $enviromentValidated = [
                    'status' => 'DIS',
                    'approval_status' => 'PB',
                ];
                $this->resourceInterface->updateConditions($currentModel,$enviromentConditions,$enviromentValidated);
                DB::commit();
                return response()->json(['is_success' => 'true']);
            }
            //Get Current Status
            $pmiInternalApprovalCurrent->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);
            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
           $pmiInternalApproval = PmiApproval::where('ecrs_id',$ecrsId)
           ->whereNotNull('rapidx_user_id')
           ->where('status','-')
           ->limit(1)
           ->get(['id','approval_status']);
            if ( count($pmiInternalApproval) === 0){
                $enviromentConditions = [
                    'ecrs_id' => $ecrsId,
                ];
                if($ecr[0]->internal_external === "External"){
                    $enviromentValidated = [
                        'status' => 'EXDISPO',
                        'approval_status' => 'EXDISPO',
                    ];
                }
                if($ecr[0]->internal_external === "Internal"){
                    $enviromentValidated = [
                        'status' => 'OK',
                        'approval_status' => 'OK',
                    ];
                }
                $this->resourceInterface->updateConditions($currentModel,$enviromentConditions,$enviromentValidated);
            }
            //Update next approval
            if ( count($pmiInternalApproval) != 0){
                $pmiInternalApprovalValidated = [
                    'status' => 'PEN',
                ];
                $pmiInternalApprovalConditions = [
                    'id' => $pmiInternalApproval[0]->id,
                ];
                $this->resourceInterface->updateConditions(PmiApproval::class,$pmiInternalApprovalConditions,$pmiInternalApprovalValidated);
                //Update the ECR Approval Status
                $enviromentConditions = [
                    'ecrs_id' => $ecrsId,
                ];
                $enviromentValidated = [
                    'approval_status' => $pmiInternalApproval[0]->approval_status,
                ];
                $this->resourceInterface->updateConditions($currentModel,$enviromentConditions,$enviromentValidated);
            }

            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveSpecialInspection(SpecialInspectionRequest $specialInspectionRequest){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            if( isset($specialInspectionRequest->special_inspections_id)){ //Edit
                $specialInspectionRequestValidated = $specialInspectionRequest->validated();
                $conditions = [
                    'id' => $specialInspectionRequest->special_inspections_id
                ];
                $specialInspectionRequestValidated['updated_by'] = session('rapidx_user_id');
                $this->resourceInterface->updateConditions(SpecialInspection::class,$conditions,$specialInspectionRequestValidated);
            }else{ //Add
                $specialInspectionRequestValidated = $specialInspectionRequest->validated();
                $specialInspectionRequestValidated['created_at'] = now();
                $specialInspectionRequestValidated['created_by'] = session('rapidx_user_id');
                $this->resourceInterface->create(SpecialInspection::class,$specialInspectionRequestValidated);
            }
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function getApprovalStatus($approval_status){
        try {
             switch ($approval_status) {
                 case 'PB':
                     $approvalStatus = 'Prepared by:';
                     break;
                 case 'CB':
                     $approvalStatus = 'Checked by:';
                     break;
                 case 'AB':
                     $approvalStatus = 'Approved by:';
                     break;
                 case 'EXQC':
                     $approvalStatus = 'QC Head:';
                     break;
                 case 'EXOH':
                     $approvalStatus = 'Operation Head:';
                     break;
                 case 'EXQA':
                     $approvalStatus = 'QA Head:';
                     break;
                 default:
                     $approvalStatus = '---';
                     break;
             }
             return [
                 'approvalStatus' => $approvalStatus,
             ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function downloadExcelByEcrsId(Request $request){
        $iqc_dropdown_category_section = 'TS';
        $ecrsId = decrypt($request->selectedId);

       $getEcrById = $this->resourceInterface->readWithRelationsConditions(
            Ecr::class,
            [],
            [
                'rapidx_user_created_by',
                'method',
                'pmi_approvals',
                'pmi_approvals.rapidx_user',
                'machine',
            ],
            [
                'id' => $ecrsId
            ]
        );
        switch ($getEcrById[0]->category) {
            case 'Method':
                $detailsByCategory = $getEcrById[0]->method;
                break;
            case 'Machine':
                $detailsByCategory = $getEcrById[0]->machine;
                break;
            default:
                # code...
                break;
        }
        $ecrsCategoryDetailsCollection = collect($getEcrById)->flatMap(function ($ecrDetailsRow) use ($detailsByCategory){
            return [
                'ecrDetails'=> $ecrDetailsRow,
                'detailsByCategory'=> $detailsByCategory
            ];
        });


        return Excel::download(
            new ExternalCcmExport($ecrsCategoryDetailsCollection),
            $iqc_dropdown_category_section . "_4M.xlsx"
        );
    }
    public function saveExternalDisposition(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $ecrsId = $request->ecrsId;
            $ecr = Ecr::find($ecrsId,['category']);
            if($request->hasfile('externalDisposition') ){
                switch ($ecr->category) {
                    case 'Man':
                        $path = 'external_disposition/man';
                        $model = ManDetail::class;
                        break;
                    case 'Material':
                        $path = 'external_disposition/material';
                        $model = Material::class;
                        break;
                    case 'Machine':
                        $path = 'external_disposition/machine';
                        $model = Machine::class;
                        break;
                    case 'Method':
                        $path = 'external_disposition/method';
                        $model = Method::class;
                        break;
                    case 'Environment':
                        $path = 'external_disposition/environment';
                        $model = Environment::class;
                        break;
                    default:
                        return response()->json(['isSuccess' => 'false','Invalid Category'],500);
                        break;
                }
               $arrUploadFile = $this->commonInterface->uploadFile($request->externalDisposition,$ecrsId,$path);
                $impOriginalFilename = implode(' | ',$arrUploadFile['arr_original_filename']);
                $impFilteredDocumentName = implode(' | ',$arrUploadFile['arr_filtered_document_name']);

                $conditions = [
                   'ecrs_id' =>  $ecrsId
                ];
                $externalDispositionValidated['original_filename'] = $impOriginalFilename;
                $externalDispositionValidated['filtered_document_name'] = $impFilteredDocumentName;
                $externalDispositionValidated['filtered_document_name'] = $impFilteredDocumentName;
                $externalDispositionValidated['file_path'] = $path;

                $externalDisposition = ExternalDisposition::where('ecrs_id',$ecrsId)
                ->whereNull('deleted_at')
                ->count();
                if($externalDisposition === 0 ){
                    $externalDispositionValidated['ecrs_id'] = $ecrsId;
                    $externalDispositionValidated['created_at'] = now();
                    $this->resourceInterface->create(ExternalDisposition::class,$externalDispositionValidated);
                }else{
                    $this->resourceInterface->updateConditions(ExternalDisposition::class,$conditions,$externalDispositionValidated);
                }
                $ecrRequestValidated['status'] = 'OK';
                $ecrRequestValidated['approval_status'] = 'OK';
                $this->resourceInterface->updateConditions($model,$conditions,$ecrRequestValidated);
            }
            DB::commit();
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function viewExternalDisposition(Request $request){
        $ecrsId = decrypt($request->ecrsId);
        $conditions = [
            'ecrs_id' => $ecrsId,
        ];
        $data = $this->resourceInterface->readCustomEloquent(ExternalDisposition::class,[],[],$conditions);
        $externalDispositionByEcrsId = $data
        ->whereNull('deleted_at')
        ->get([
            'filtered_document_name',
            'file_path',
        ]);
        if(count($externalDispositionByEcrsId) != 0){
            $arrFilteredDocumentName = explode(' | ' ,$externalDispositionByEcrsId[0]->filtered_document_name);
            $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
            $filePathWithEcrsId = $externalDispositionByEcrsId[0]->file_path."/".$ecrsId."/".$selectedFilteredDocumentName;
            $pdfPath = storage_path("app/public/".$filePathWithEcrsId."");
            $this->commonInterface->viewPdfFile($pdfPath);
        }
    }
    public function testEmail(Request $request){ //test
        try {
            $userId = 530;
            $requestedBy = $this->emailInterface->getEmailByRapidxUserId($userId);
            return $msg = $this->emailInterface->ecrEmailMsg(1);
            $test = [
                "to" =>"mclegaspi@pricon.ph",
                "cc" =>"",
                "bcc" =>"cdcasuyon@pricon.ph",
                "from" =>"mrronquez@pricon.ph",
                "from_name" =>"4M Change Control Management System",
                "subject" =>"FOR APPROVAL: Engineering Change Request (ECR)",
                "message" =>  $msg,
                "attachment_filename" => "",
                "attachment" => "",
                "send_date_time" => now(),
                "date_time_sent" => "",
                "date_created" => now(),
                "created_by" => "mclegaspi",
                "system_name" => "rapidx_4M",
            ];
           $this->emailInterface->sendEmail($data);
           return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getApprovalCountByRapidxUserId(Request $request){
        try {
            $rapidxUserId = session('rapidx_user_id');
            $ecrApproval = EcrApproval::
            with('ecr')
            ->whereHas('ecr', function ($query) {
                $query->where('status','!=', 'DIS');
                $query->whereNull('deleted_at');

            })
            ->where('rapidx_user_id',$rapidxUserId)
            ->where('status','PEN')
            ->count();
            $manApproval = ManApproval::
            with('man_detail')
            ->whereHas('man_detail', function ($query) {
                $query->where('status','!=', 'DIS');
                $query->whereNull('deleted_at');

            })
            ->where('rapidx_user_id',$rapidxUserId)
            ->where('status','PEN')
            ->count();
            $materialApproval = MaterialApproval::
            with('material')
            ->whereHas('material', function ($query) {
                $query->where('status','!=', 'DIS');
                $query->whereNull('deleted_at');

            })
            ->where('rapidx_user_id',$rapidxUserId)
            ->where('status','PEN')->count();
            $machineApproval = MachineApproval::
            with('machine')
            ->whereHas('machine', function ($query) {
                $query->where('status','!=', 'DIS');
                $query->whereNull('deleted_at');

            })
            ->where('rapidx_user_id',$rapidxUserId)
            ->where('status','PEN')
            ->count();
            $methodApproval = MethodApproval::
            with('method')
            ->whereHas('method', function ($query) {
                $query->where('status','!=', 'DIS');
                $query->whereNull('deleted_at');

            })
            ->where('rapidx_user_id',$rapidxUserId)
            ->where('status','PEN'
            )->count();

           $pmiApproval = PmiApproval::with(
                'man_detail',
                'material',
                'machine',
                'method',
                'environment',
            )
            ->where('rapidx_user_id',$rapidxUserId)->where('status','PEN')->get();

            //Count PENDING PMI Approval with Relationship
            $relations = ['man_detail', 'material', 'machine', 'method', 'environment'];

            $relationshipCounts = [];

            foreach ($relations as $relation) {
                $relationshipCounts[$relation] = $pmiApproval->filter(function ($item) use ($relation) {
                    return !is_null($item->$relation);
                })->count();
            }


            $pendingEcr = Ecr::where('status','!=','OK')
            ->whereNull('deleted_at')
            ->count();

            $approvedEcr = Ecr::where('status','OK')
            ->whereNull('deleted_at')
            ->count();

            $pendingMan = Man::where('status','!=','OK')
            ->whereNull('deleted_at')
            ->count();

            $approvedMan = Man::where('status','OK')
            ->whereNull('deleted_at')
            ->count();

            $pendingMaterial = Material::where('status','!=','OK')
            ->whereNull('deleted_at')
            ->count();

            $approvedMaterial = Material::where('status','OK')
            ->whereNull('deleted_at')
            ->count();

            $pendingMethod = Method::where('status','!=','OK')
            ->whereNull('deleted_at')
            ->count();

            $approvedMethod = Method::where('status','OK')
            ->whereNull('deleted_at')
            ->count();

            $pendingMachine = Machine::where('status','!=','OK')
            ->whereNull('deleted_at')
            ->count();

            $approvedMachine = Machine::where('status','OK')
            ->whereNull('deleted_at')
            ->count();

            $pendingEnvironment = Environment::where('approval_status','!=','OK')
            ->whereNull('deleted_at')
            ->count();

            $approvedEnvironment = Environment::where('approval_status','OK')
            ->whereNull('deleted_at')
            ->count();

            return response()->json([
                'isSuccess' => 'true',
                'ecrApproval' => $ecrApproval,
                'manApproval' => $manApproval,
                'materialApproval' => $materialApproval,
                'machineApproval' => $machineApproval,
                'methodApproval' => $methodApproval,
                'pmiApproval' => $relationshipCounts,
                'pendingEcr' => $pendingEcr,
                'approvedEcr' => $approvedEcr,
                'pendingMan' => $pendingMan,
                'approvedMan' => $approvedMan,
                'pendingMaterial' => $pendingMaterial,
                'approvedMaterial' => $approvedMaterial,
                'pendingMethod' => $pendingMethod,
                'approvedMethod' => $approvedMethod,
                'pendingMachine' => $pendingMachine,
                'approvedMachine' => $approvedMachine,
                'pendingEnvironment' => $pendingEnvironment,
                'approvedEnvironment' => $approvedEnvironment,
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

}
