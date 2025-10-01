<?php

namespace App\Http\Controllers;

use App\Models\Ecr;
use App\Models\Man;
use App\Models\EcrDetail;
use App\Models\ManDetail;
use App\Models\ManApproval;
use App\Models\ManChecklist;
use Illuminate\Http\Request;
use App\Http\Requests\ManRequest;
use App\Models\SpecialInspection;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Models\DropdownMasterDetail;
use App\Interfaces\ResourceInterface;

class ManController extends Controller
{
    protected $resourceInterface;
    protected $commonInterface;
    public function __construct(ResourceInterface $resourceInterface,CommonInterface $commonInterface) {
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
    }
    public function saveMan(Request $request,ManRequest $manRequest){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $manDetailModel = ManDetail::class;
            $manModel = Man::class;
            $ecrsId = $request->ecrs_id;
            $manRequestValidated = $manRequest->validated();
            if ( filled($request->man_id)){ //Edit

                $trnrCount = $manModel::where('approval_status','TRNR')->exists();
                $lqcCount = $manModel::where('approval_status','LQCSUP')->exists();
                if($trnrCount){
                    $manRequestValidated['trainer_sample_size'] = $request->trainer_sample_size;
                    $manRequestValidated['trainer_result'] = $request->trainer_result;
                }
                if($lqcCount){
                    $manRequestValidated['lqc_sample_size'] = $request->lqc_sample_size;
                    $manRequestValidated['lqc_result'] = $request->lqc_result;
                }
                $manRequestValidated['process_change_factor'] = $request->process_change_factor;
                $conditions = [
                    'id' => $request->man_id
                ];
                $this->resourceInterface->updateConditions($manDetailModel,$conditions,$manRequestValidated);
            }else{ //Add
                $man =  $this->resourceInterface->create($manDetailModel,$manRequestValidated);
            }
            $manApprovalTypes = [
                'RUP' => session('rapidx_user_id'),
                'TRNR' => $request->trainer,
                'LQCSUP' => $request->lqc_supervisor,
                'CHCK' => session('rapidx_user_id'), //Checklist Update
            ];
            $manApprovalRequestCtr = 0; //assigned counter
           $manApprovalRequest = collect($manApprovalTypes)->flatMap(function ($users,$approval_status) use ($request,&$manApprovalRequestCtr,$ecrsId){
                    return collect($users)->map(function ($userId) use ($request,$approval_status,&$manApprovalRequestCtr,$ecrsId){
                        return [
                            'ecrs_id' =>  $ecrsId,
                            'rapidx_user_id' => $userId == 0 ? NULL : $userId,
                            'approval_status' => $approval_status,
                            'remarks' => $request->remarks,
                            'created_at' => now(),
                        ];
                    });

            })->toArray();
            $manDetailCount = ManDetail::where('ecrs_id', $ecrsId)
            ->whereNull('deleted_at')
            ->count();
            if($request->is_update_man_approver === 'YES'){
                ManApproval::where('ecrs_id',$ecrsId)
                ->whereNull('deleted_at')
                ->delete();
                ManApproval::insert($manApprovalRequest);
                $manApproval =  ManApproval::whereNotNull('rapidx_user_id')
                ->where('ecrs_id', $ecrsId)
                ->first();
                if ($manApproval) {
                    $manApproval->update(['status' => 'PEN']);
                    Man::where('ecrs_id', $ecrsId)->first()
                    ->update([
                        'approval_status' => 'RUP',
                        'status' => 'RUP',
                    ]);
                }
            }
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;

        }
    }
    public function saveManApproval(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $selectedId = $request->selectedId;
            //Get Current Ecr Approval is equal to Current Session
            $manApprovalCurrent = ManApproval::where('ecrs_id',$selectedId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();

            if($manApprovalCurrent->rapidx_user_id != session('rapidx_user_id')){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }

            //Update the man Approval Status
            $manApprovalCurrent->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);
            if($request->status === 'APP'){
                if($manApprovalCurrent->approval_status === 'RUP'){
                    $isManRequirementsComplete = $this->isManRequirementsComplete($selectedId);
                    if(  $isManRequirementsComplete['isSuccess'] === 'false'){
                        return response()->json(['isSuccess' => 'false','msg' => $isManRequirementsComplete['msg'] ],500);
                    }
                }
                if($manApprovalCurrent->approval_status === 'TRNR'){
                    $isManRequirementsComplete = $this->isTrainerManRequirementsComplete($selectedId);
                    if(  $isManRequirementsComplete['isSuccess'] === 'false'){
                        return response()->json(['isSuccess' => 'false','msg' => $isManRequirementsComplete['msg'] ],500);
                    }
                }
                if($manApprovalCurrent->approval_status === 'LQCSUP'){
                    $isManRequirementsComplete = $this->isLqcManRequirementsComplete($selectedId);
                    if(  $isManRequirementsComplete['isSuccess'] === 'false'){
                        return response()->json(['isSuccess' => 'false','msg' => $isManRequirementsComplete['msg'] ],500);
                    }
                }
                if($manApprovalCurrent->approval_status === 'CHCK'){
                    $isManRequirementsComplete = $this->isChecklistManRequirementsComplete($selectedId);
                    if(  $isManRequirementsComplete['isSuccess'] === 'false'){
                        return response()->json(['isSuccess' => 'false','msg' => $isManRequirementsComplete['msg'] ],500);
                    }
                }
            }
            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
            $manApproval = ManApproval::where('ecrs_id',$selectedId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','-')
            ->limit(1)
            ->get(['id','approval_status']);
            //DISAPPROVED ECR
            if($request->status === "DIS"){
                $conditions = [
                    'id' => $selectedId,
                ];
                $requestValidated = [
                    'status' => 'DIS',
                    'approval_status' => 'DIS', //Repeat the status
                ];
                $this->resourceInterface->updateConditions(Man::class,$conditions,$requestValidated);
            }
            if ( count($manApproval) === 0){
                    $manConditions = [
                        'ecrs_id' => $selectedId,
                    ];
                    $manValidated = [
                        'status' => 'PMIAPP',
                        'approval_status' => 'PB',
                    ];
                    $this->resourceInterface->updateConditions(Man::class,$manConditions,$manValidated);
            }
            if ( count($manApproval) != 0){
                $manApprovalValidated = [
                    'status' => 'PEN',
                ];
                $manApprovalConditions = [
                    'id' => $manApproval[0]->id,
                ];
                $this->resourceInterface->updateConditions(ManApproval::class,$manApprovalConditions,$manApprovalValidated);
                //Update the ECR Approval Status
                $manConditions = [
                    'ecrs_id' => $selectedId,
                ];
                $manValidated = [
                    'status' => 'FORAPP',
                    'approval_status' => $manApproval[0]->approval_status,
                ];
                $this->resourceInterface->updateConditions(Man::class,$manConditions,$manValidated);
            }

            DB::commit();
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function loadEcrManByStatus(Request $request){
        $adminAccess = $request->adminAccess;
        $data = [];
        $relations = [
            'man_detail.man_approvals_pending',
            'man_detail',
        ];
        $conditions = [
            'status' => 'OK',
            'category' => $request->category
        ];
        $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,$data,$relations,$conditions);
        //  ||
        $ecr->whereNull('deleted_at');

        if( $adminAccess === 'null' || blank($adminAccess) ){
            $ecr->whereHas('man_detail.man_approvals_pending',function($query){
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
        //If the Man Approval is OK / Zero, PMI Approvals Pending displayed
        if ( $adminAccess === 'pmi' || $ecr->count() === 0) {
            $data = [];
            $relations = [
                'pmi_approvals_pending',
                'man_detail',
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
            $result = "";
            $result .= '<center>';
            $result .= '<div class="btn-group dropstart mt-4">';
            $result .= '<button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">';
            $result .= '    Action';
            $result .= '</button>';
            $result .= '<ul class="dropdown-menu">';
            // if($row->man_detail->status === "RUP" || $row->man_detail->status === "PMIAPP"){
                $result .= '   <li><button class="dropdown-item" type="button" man-status= "'.$row->man_detail->status.'" ecrs-id="'.$row->id.'" man-details-id="'.$row->man_detail->id.'"id="btnViewManById"><i class="fa-solid fa-eye"></i> &nbsp;View/Approval</button></li>';
            // }
            if($row->man_detail->status === "RUP" && $row->created_by === session('rapidx_user_id')){
                $result .= '   <li><button class="dropdown-item" type="button" man-status= "'.$row->man_detail->status.'" ecrs-id="'.$row->id.'" id="btnGetEcrId"><i class="fa-solid fa-edit"></i> &nbsp;Edit</button></li>';
            }
            if($row->man_detail->status === "DIS" && $row->created_by === session('rapidx_user_id')){
                $result .= '   <li><button class="dropdown-item" type="button" man-status= "'.$row->man_detail->status.'" ecrs-id="'.$row->id.'" id="btnGetEcrId"><i class="fa-solid fa-edit"></i> &nbsp;Edit</button></li>';
            }

            $result .= '</ul>';
            $result .= '</div>';
            $result .= '</center>';
            return $result;
        })
        ->addColumn('get_status',function ($row) use($request){
            $currentApprover = $row->man_detail->man_approvals_pending[0]['rapidx_user']['name'] ?? '';
            $getStatus = $this->getStatus($row->man_detail->status);
            $result = '';
            $result .= '<center>';
            $result .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
            $result .= '<br>';
            if( $currentApprover != ''){
                $result .= '<span class="badge rounded-pill bg-danger"> '.$currentApprover.' </span>';
            }
            if( $row->man_detail->status === 'PMIAPP' ){ //TODO: Last Status PMI Internal
                $currentApprover = $row->pmi_approvals_pending[0]['rapidx_user']['name'] ?? '';
                $approvalStatus = $row->man_detail->approval_status;
                $getPmiApprovalStatus = $this->commonInterface->getPmiApprovalStatus($approvalStatus);
                $result .= '<span class="badge rounded-pill bg-danger"> '.$getPmiApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
            }
            $result .= '</center>';
            $result .= '</br>';
            return $result;
        })
        ->addColumn('get_details',function ($row) use($request) {
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
            'get_details'
        ])
        ->make(true);
        try {
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function loadManByEcrId(Request $request){
        try {
            $data = [];
            $relations = [
                'rapidx_user_qc_inspector_operator',
                'rapidx_user_trainer',
                'rapidx_user_lqc_supervisor',
                'man_pending_approvals',
                'man',
                'ecr',
            ];
            $conditions = [
                'ecrs_id' => $request->ecrsId ?? ''
            ];
            $ecrDetail = $this->resourceInterface->readWithRelationsConditionsActive(ManDetail::class,$data,$relations,$conditions);
            return DataTables($ecrDetail)
            ->addColumn('get_actions',function ($row){
                $currentAppprover = $row->man_pending_approvals->rapidx_user_id ?? "";
                $status = $row->man_pending_approvals->status ?? "";
                $approvalStatus = $row->man_pending_approvals->approval_status ?? "";
                $result = '';
                if($currentAppprover === session('rapidx_user_id')){
                    if(($status != 'PMIAPP' && $approvalStatus != 'CHCK')){
                        $result .= "<button class='btn btn-outline-info btn-sm mr-1 mb-3' ecrs-id = '".$row->ecrs_id."' man-details-id='".$row->id."' id='btnManDetailsId'> <i class='fa-solid fa-pen-to-square'></i></button>";
                    }

                }
                if($approvalStatus === 'CHCK' && $currentAppprover === session('rapidx_user_id')){
                    $result .= "<button class='btn btn-outline-success btn-sm mr-1' ecrs-id = '".$row->ecrs_id."' man-details-id='".$row->id."' id='btnManChecklistId'> <i class='fa-solid fa-check'></i></button>";
                    $result .= '</center>';
                }
                if($row->man->status === 'DIS' && $row->ecr->created_by === session('rapidx_user_id')){
                    $result .= "<button class='btn btn-outline-info btn-sm mr-1 mb-3' ecrs-id = '".$row->ecrs_id."' man-details-id='".$row->id."' id='btnManDetailsId'> <i class='fa-solid fa-pen-to-square'></i></button>";
                }
                return $result;
            })
            ->addColumn('qc_inspector_operator',function ($row){
                $result = '';
                $result .= $row->rapidx_user_qc_inspector_operator->name ?? null;
                return $result;
            })
            ->addColumn('trainer',function ($row){
                $result = '';
                $result .= $row->rapidx_user_trainer->name ?? null;
                return $result;
            })
            ->addColumn('lqc_supervisor',function ($row){
                $result = '';
                $result .= $row->rapidx_user_lqc_supervisor->name ?? null;
                return $result;
            })
            ->addColumn('trainer_result',function ($row){
                $result = '';
                switch ($row->trainer_result) {
                    case 'OK':
                        $bgColor = 'bg-success text-white';
                        break;
                    case 'NG':
                        $bgColor = 'bg-danger text-white';
                        break;
                    default:
                        $bgColor = 'bg-secondary text-white';
                        break;
                }
                $result .='<span class="badge '.$bgColor.'"> '.$row->trainer_result.' </span>';
                return $result;
            })
            ->addColumn('lqc_result',function ($row){
                $result = '';
                switch ($row->lqc_result) {
                    case 'PASSED':
                        $bgColor = 'bg-success text-white';
                        break;
                    case 'FAILED':
                        $bgColor = 'bg-danger text-white';
                        break;
                    default:
                        $bgColor = 'bg-secondary text-white';
                        break;
                }
                $result .='<span class="badge '.$bgColor.'"> '.$row->lqc_result.' </span>';
                return $result;
            })
            ->rawColumns([
                'get_actions',
                'qc_inspector_operator',
                'trainer',
                'trainer_result',
                'lqc_supervisor',
                'lqc_result',
            ])
            ->make(true);
        } catch (Exception $e) {
            throw $e;

        }
    }
    public function loadManChecklist(Request $request){
        try {
            $data = [];
            $relations = [];
            $conditions = [
                'dropdown_masters_id' => $request->dropdown_masters_id
            ];
            $man_checklist_data = [];
            $man_checklist_relations = [];
            $man_checklist_conditions = [
                'man_id' => $request->manDetailsId,
            ];

            $dropdownMasterDetail = $this->resourceInterface->readCustomEloquent(DropdownMasterDetail::class,$data,$relations,$conditions);
            $dropdownMasterDetail->orderBy('dropdown_masters_details');
            // return $classificationRequirement; dropdown_masters_details
            $manChecklist = $this->resourceInterface->readWithRelationsConditionsActive(ManChecklist::class,$man_checklist_data,$man_checklist_relations,$man_checklist_conditions);
            return DataTables($dropdownMasterDetail)
            ->addColumn('get_actions',function ($row) use($manChecklist) {
                // return 'true';
                $manChecklistCollection = collect($manChecklist);
                $manChecklistMatch = $manChecklistCollection->firstWhere('dropdown_master_details_id', $row->id);
                $manChecklistId = $manChecklistMatch['id'] ?? '';
                $cSelected = $manChecklistMatch['decision'] === 'C' ? 'selected' : '';
                $xSelected = $manChecklistMatch['decision'] === 'X' ? 'selected' : '';
                if($manChecklistId === ''){
                    $isValid = "is-invalid";
                    $emptySelected = "selected";
                }else{
                    $isValid = "";
                    $emptySelected = "";
                }
                $result = '';
                $result .= '<center>';
                $result .= "<select id='btnChangeManChecklistDecision' class='form-select btn-change-ecr-req-decision ".$isValid."' ref=btnChangeManChecklistDecision man-checklists-id ='".$manChecklistId."' dropdown-master-details-id='".$row->id."'>";
                $result .=  "<option value='' ".$emptySelected." disabled> --Select-- </option>";
                $result .=  "<option value='C' ".$cSelected."> √ </option>";
                $result .=  "<option value='X' ".$xSelected."> X </option>";
                $result .=  "</select>";
                $result .= '</center>';
                return $result;
            })
            ->rawColumns([
                'get_actions',
            ])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function loadManApproverSummaryEcrsId (Request $request){
        try {
            $ecrsId = $request->ecrsId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'ecrs_id' => $ecrsId
            ];
            $methodpproval = $this->resourceInterface->readCustomEloquent(ManApproval::class,$data,$relations,$conditions);
            $methodpproval = $methodpproval
            ->whereNotNull('rapidx_user_id')
            ->orderBy('id','asc')
            ->get();
            return DataTables($methodpproval)
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
    public function getManById(Request $request){
        try {
            $data = [];
            $relations = [
            ];
            $conditions = [
                'id' => $request->manId
            ];
            $man = $this->resourceInterface->readWithRelationsConditionsActive(ManDetail::class,$data,$relations,$conditions);
            return response()->json(['is_success' => 'true','man'=>$man[0]]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function manChecklistDecisionChange(Request $request){
        try {
            if( isset($request->manChecklistsId) ){ //edit
                // return 'edit';
                $conditions = [
                    'id' => $request->manChecklistsId
                ];
                $data = [
                    // 'dropdown_master_details_id' => $request->dropdownMasterDetailsId,
                    'decision' => $request->manChecklistValue,
                ];

                $this->resourceInterface->updateConditions(ManChecklist::class,$conditions,$data);
            }else{ //add
                $data = [
                    'dropdown_master_details_id' => $request->dropdownMasterDetailsId,
                    'decision' => $request->manChecklistValue,
                    'man_id' => $request->manDetailsId,
                ];
                $this->resourceInterface->create(ManChecklist::class,$data);
            }

            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {

            throw $e;
        }
    }
    public function isManRequirementsComplete($ecrsId){ //Requirement
        //ECR Details, Man Details
        $isEcrDetailsActiveCount = EcrDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at')
        ->count();
        $ecrDetails = EcrDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at');
        // ->get();
        $arrColumnEcrDetails = [
            'type_of_part',
            'change_imp_date',
            'doc_sub_date',
            'doc_to_be_sub',
            'customer_approval',
        ];
        collect($arrColumnEcrDetails)->each(function ($arrColumnEcrDetailsRows) use ($ecrDetails) {
        //    return  $ecrDetails;
            $ecrDetails->whereNotNull($arrColumnEcrDetailsRows)->count();
        });

        $ecrDetailsNotNullCount = $ecrDetails->count();
        //Ecr Details Should be Completed
        if($ecrDetailsNotNullCount != $isEcrDetailsActiveCount){
            return [
                'isSuccess' => 'false',
                'msg' => 'Please complete the ECR Details Above'
            ];
        }
        //Man Details Should be Saved
        $man = ManDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at')
        ->count();
        if($man === 0){
            return [
                'isSuccess' => 'false',
                'msg' => 'Please complete the Man Details Above'
            ];
        }
        //Man Details Should be saved
        $manApproval = ManApproval::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at')
        ->count();
        if($manApproval === 0){
            return [
                'isSuccess' => 'false',
                'msg' => 'Please save the Man Approvers'
            ];
        }

        return [
            'isSuccess' => 'true',
            'msg' => 'Ecr Details & Man Details Completed'
        ];
    }
    public function isTrainerManRequirementsComplete($ecrsId){ //Requirement
        //Man Details Should be Saved
        $isManDetailActiveCount = ManDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at')
        ->count();
        $manDetail = ManDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at');
        // ->get();
        $arrColumnManDetail = [
            'trainer_sample_size',
            'trainer_result',
        ];
        collect($arrColumnManDetail)->each(function ($arrColumnManDetailRows) use ($manDetail) {
        //    return  $ManDetail;
            $manDetail->whereNotNull($arrColumnManDetailRows)->count();
        });

        $ManDetailNotNullCount = $manDetail->count();
        //Ecr Details Should be Completed
        if($ManDetailNotNullCount != $isManDetailActiveCount){
            return [
                'isSuccess' => 'false',
                'msg' => 'Please Trainer Result and Sample Size'
            ];
        }
        return [
            'isSuccess' => 'true',
            'msg' => 'LQC Requirement Completed !'
        ];
    }
    public function isLqcManRequirementsComplete($ecrsId){ //Requirement
        //Man Details Should be Saved
        $isManDetailActiveCount = ManDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at')
        ->count();
        $manDetail = ManDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at');
        // ->get();
        $arrColumnManDetail = [
            'lqc_sample_size',
            'lqc_result',
        ];
        collect($arrColumnManDetail)->each(function ($arrColumnManDetailRows) use ($manDetail) {
        //    return  $ManDetail;
            $manDetail->whereNotNull($arrColumnManDetailRows)->count();
        });

        $ManDetailNotNullCount = $manDetail->count();
        //Ecr Details Should be Completed
        if($ManDetailNotNullCount != $isManDetailActiveCount){
            return [
                'isSuccess' => 'false',
                'msg' => 'Please LQC Result and Sample Size'
            ];
        }
        return [
            'isSuccess' => 'true',
            'msg' => 'LQC Requirement Completed !'
        ];
    }
    public function isChecklistManRequirementsComplete($ecrsId){ //Requirement
        //Man Details Should be Saved
        $man = ManDetail::where('ecrs_id',$ecrsId)
        ->whereNull('deleted_at')
        ->get(['id']);
        //Read if the All Man Details have Man Checklist
        $arrMan = collect($man)->map(function($arrMan){
           return $isManChecklistActiveCount = ManChecklist::where('man_id',$arrMan->id)
           ->whereNull('deleted_at')
           ->count();
        })->toArray();
        //Count All Empty Checklist / Zero = Empty Checklist
        $isManChecklistComplete = collect($arrMan)->filter(function($value) {
            return $value === 0;
        })->count();

        if($isManChecklistComplete > 0){
            return [
                'isSuccess' => 'false',
                'msg' => 'Please Complete the Man Checklist !'
            ];
        }
        return [
            'isSuccess' => 'true',
            'msg' => ' Man Checklist Requirement is Completed !'
        ];
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
                case 'RUP':
                    $approvalStatus = 'For Requestor Update:';
                    break;
                case 'TRNR':
                    $approvalStatus = 'Trainer:';
                    break;
                case 'LQCSUP':
                    $approvalStatus = 'LQC Supervisor:';
                    break;
                case 'CHCK':
                    $approvalStatus = 'For Checklist Update:';
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

