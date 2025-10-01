<?php

namespace App\Http\Controllers;
use App\Models\Ecr;
use App\Models\Man;
use App\Models\Method;
use App\Models\Machine;
use App\Models\Material;
use App\Models\EcrDetail;
use App\Models\ManDetail;
use App\Exports\EcrExport;
use App\Models\RapidxUser;
use App\Models\EcrApproval;
use App\Models\Environment;
use App\Models\ManApproval;
use App\Models\PmiApproval;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DropdownMaster;
use App\Models\EcrRequirement;
use App\Http\Requests\EcrRequest;
use App\Interfaces\EmailInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Models\DropdownMasterDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\EcrFileRequest;
use App\Interfaces\ResourceInterface;
use App\Http\Requests\EcrDetailRequest;
use App\Http\Requests\EcrApprovalRequest;
use App\Http\Requests\PmiApprovalRequest;
use App\Models\ClassificationRequirement;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\EcrRequirementFileRequest;
use App\Http\Requests\PmiExternalApprovalRequest;

class EcrController extends Controller
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
    public function downloadEcrExcelByEcrsId(Request $request){
        try {

            $ecrsId = decrypt($request->ecrsId);
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,[],
            [
                'ecr_approvals',
                'ecr_approvals.rapidx_user',
                'ecr_details.dropdown_master_detail_description_of_change',
                'ecr_details.dropdown_master_detail_reason_of_change',
            ],
            [
                'id'=> $ecrsId
            ]);
            $ecrDetails = $ecr->get();
            $ecrCollection = collect($ecrDetails)
            ->flatMap(function ($ecrCollectionRow){
                $ecrApprovals = $ecrCollectionRow->ecr_approvals ?? '';
                //Get the Department / Section of the user
                $requestedByDeptCollection = collect($ecrApprovals)->map(function ($ecrApprovalsRow){
                    $departmentId = $ecrApprovalsRow->rapidx_user->department_id ?? '';
                    return $requestedByDept = $this->commonInterface->getRapidxUserDeptByDeptId($departmentId);

                });
                return [
                    'requestedByDeptCollection' => $requestedByDeptCollection,
                    'ecrCollection' => $ecrCollectionRow,
                ];
            });

            return Excel::download(new EcrExport($ecrCollection),"ECR.xlsx");
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveEcr(Request $request,EcrApprovalRequest $ecrApprovalRequest, EcrRequest $ecrRequest,PmiApprovalRequest $pmiApprovalRequest,EcrFileRequest $ecrFileRequest){
    // public function saveEcr(Request $request,EcrFileRequest $ecrFileRequest){ //nmodify
        date_default_timezone_set('Asia/Manila');

        try {
            //TODO:  DELETE, InsertById, N/A in Dropdown
            DB::beginTransaction();

            $generatedControlNumber =  $this->generateControlNumber();
            $ecrsId = $request->ecrs_id;
            $ecrRequest = $ecrRequest->validated();
            $ecrConditions = [
                'id' => $ecrsId
            ];

            if( isset($ecrsId) ){ //Edit
                //Validate Before Edit: On going approval cannot update
                $ecrEcrApproval = EcrApproval::where('id',$ecrsId)
                ->where('status','PEN')
                ->where('approval_status','OTRB')
                ->count();

                if ( $ecrEcrApproval === 1 ){
                    DB::rollback();
                    return response()->json(['isSuccess' => 'false','msg' => "On going approval ! You cannot update this request "],500);
                }
                $ecr = Ecr::where('id',$ecrsId)
                ->where('created_by',session('rapidx_user_id'))
                ->count();
                if ( $ecr === 0 ){
                    DB::rollback();
                    return response()->json(['isSuccess' => 'false','msg' => "Invalid User ! You cannot update this request "],500);
                }
                $ecrRequest['status'] = 'IA';
                $ecrRequest['approval_status'] = 'OTRB';
                // $ecrRequest['ecr_no'] = $generatedControlNumber['currentCtrlNo'];
                $this->resourceInterface->updateConditions(Ecr::class,$ecrConditions,$ecrRequest);
                $currenErcId = $ecrsId;
            }else{ //Add
                $ecrRequest['created_at'] = now();
                $ecrRequest['ecr_no'] = $generatedControlNumber['currentCtrlNo'];
                $ecrRequest['created_by'] = session('rapidx_user_id');

                $ecr =  $this->resourceInterface->create(Ecr::class,$ecrRequest);
                $currenErcId = $ecr['data_id'];
            }

            //File Upload & Updat eEcr
            $ecrFileRequestValidated = [];
            $ecrRFile = $ecrFileRequest->ecr_ref;
            $path = "ecr/".$request->category."/".$currenErcId."/";
            if($ecrFileRequest->hasfile('ecr_ref')){
                // $arrUploadFile = $this->commonInterface->uploadFileEcrRequirement($ecrRFile,$path);
                $arrUploadFile = $this->commonInterface->uploadFileEcrRequirement($ecrRFile,$path);
                $impOriginalFilename = implode(' | ',$arrUploadFile['arr_original_filename']);
                $impFilteredDocumentName = implode(' | ',$arrUploadFile['arr_filtered_document_name']);

                $ecrFileRequestValidated['original_filename'] = $impOriginalFilename;
                $ecrFileRequestValidated['filtered_document_name'] = $impFilteredDocumentName;
                $ecrFileRequestValidated['updated_by'] = session('rapidx_user_id');
                $this->resourceInterface->updateConditions(Ecr::class,['id'=>$currenErcId],$ecrFileRequestValidated);
            }

            $ecrDetailRequest = collect($request->description_of_change)->map(function ($description_of_change,$index) use ($request,$currenErcId){
                return [
                    'ecrs_id' =>  $currenErcId,
                    'description_of_change' => $description_of_change,
                    'reason_of_change' => $request->reason_of_change[$index],
                    'created_at' => now(),
                ];
            });
            EcrDetail::where('ecrs_id', $currenErcId)->delete();
            foreach ($ecrDetailRequest as $ecrDetailRequestValue) {
               $this->resourceInterface->create(EcrDetail::class, $ecrDetailRequestValue);
            }
            //Requested by, Engg, Heads, QA Approval
            $ecrApprovalTypes = [
                'OTRB' => $request->requested_by,
                'OTTE' => $request->technical_evaluation,
                'OTRVB' => $request->reviewed_by,
                'QACB' => $request->qad_checked_by,
                'QAIN' => $request->qad_approved_by_internal,
            ];
            $ecrApprovalRequestCtr = 0; //assigned counter
            $ecrApprovalRequest = collect($ecrApprovalTypes)->flatMap(function ($users,$approval_status) use ($request,&$ecrApprovalRequestCtr,$currenErcId){
                    return collect($users)->map(function ($userId) use ($request,$approval_status,&$ecrApprovalRequestCtr,$currenErcId){
                        return [
                            'ecrs_id' =>  $currenErcId,
                            'rapidx_user_id' => $userId == 0 ? NULL : $userId,
                            'approval_status' => $approval_status,
                            'counter' => $ecrApprovalRequestCtr++,
                            'remarks' => $request->remarks,
                            'created_at' => now(),
                        ];
                    });

            })->toArray();
            //Delete previous ecr approval,save,update status to Pending
            EcrApproval::where('ecrs_id', $currenErcId)->delete();
            EcrApproval::insert($ecrApprovalRequest);
            EcrApproval::where('counter', 0)
            ->where('ecrs_id', $currenErcId)
            ->update(['status'=>'PEN']);
            //PMI Approvers
            $approval_status = [
                'PB' => $request->prepared_by,
                'CB' => $request->checked_by,
                'AB' => $request->approved_by,
            ];
            $pmiApprovalRequestCtr = 0;
            $pmiApprovalRequest = collect($approval_status)->flatMap(function ($users,$approval_status) use ($request,&$pmiApprovalRequestCtr,$currenErcId){
                //return array users id as array value
                return collect($users)->map(function ($userId) use ($approval_status, $request,&$pmiApprovalRequestCtr,$currenErcId) {
                    // $approval_status as a array name
                    //return array users id, defined type by use keyword,
                    return [
                        'ecrs_id' => $currenErcId,
                        'rapidx_user_id' =>  $userId == 0 ? NULL : $userId,
                        'approval_status' => $approval_status,
                        'counter' => $pmiApprovalRequestCtr++,
                        'remarks' => $request->remarks,
                        'created_at' => now(),
                    ];
                });
            })->toArray();

            //Save PMI Internal Approval
            PmiApproval::where('ecrs_id', $currenErcId)->delete();
            PmiApproval::insert($pmiApprovalRequest);
            PmiApproval::where('counter', 0)
            ->where('ecrs_id', $currenErcId)
            ->update(['status'=>'PEN']);
            if($request->internal_external === "External"){

                $validator = Validator::make($request->all(), (new PmiExternalApprovalRequest)->rules());

                if ($validator->fails()) {
                    return response()->json([
                        'isSuccess' => 'false',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $external_approval_status = [
                    'EXQC' => $request->external_prepared_by,
                    'EXOH' => $request->external_checked_by,
                    'EXQA' => $request->external_approved_by,
                ];

               $pmiApprovalRequest = collect($external_approval_status)->flatMap(function ($users,$approval_status) use ($request,&$pmiApprovalRequestCtr,$currenErcId){
                    //return array users id as array value
                    return collect($users)->map(function ($userId) use ($approval_status, $request,&$pmiApprovalRequestCtr,$currenErcId) {
                        // $approval_status as a array name
                        //return array users id, defined type by use keyword,
                        return [
                            'ecrs_id' => $currenErcId,
                            'rapidx_user_id' =>  $userId == 0 ? NULL : $userId,
                            'approval_status' => $approval_status,
                            'counter' => $pmiApprovalRequestCtr++,
                            'remarks' => $request->remarks,
                            'created_at' => now(),
                        ];
                    });
                })->toArray();
                //Save PMI Internal Approval
                PmiApproval::insert($pmiApprovalRequest);
            }
            DB::commit();

            //Send For Approval Email to Next Approver
            $ecrApprovalCurrent = EcrApproval::where('ecrs_id',$currenErcId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            $ecrCurrentApproval = $this->emailInterface->getEmailByRapidxUserId( $ecrApprovalCurrent->rapidx_user_id);
            $to = $ecrCurrentApproval['email'] ?? '';
            $from = 'issinfoservice@pricon.ph';
            $subject = "FOR APPROVAL: Engineering Change Request (ECR)";
            $from_name = "4M Change Control Management System";
            $msg = $this->emailInterface->ecrEmailMsg($currenErcId);
            $emailData = [
                "to" =>$to,
                "cc" =>"",
                "bcc" =>"mclegaspi@pricon.ph,rdahorro@pricon.ph,jggabuat@pricon.ph",
                "from" => $from,
                "from_name" =>$from_name ?? "4M Change Control Management System",
                "subject" =>$subject,
                "message" =>  $msg,
                "attachment_filename" => "",
                "attachment" => "",
                "send_date_time" => now(),
                "date_time_sent" => "",
                "date_created" => now(),
                "created_by" => session('rapidx_username'),
                "system_name" => "rapidx_4M",
            ];
            $this->emailInterface->sendEmail($emailData);
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveEcrApproval(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $ecrsId = $request->ecrs_id;

            //Get Current Ecr Approval is equal to Current Session
            $ecrApprovalCurrent = EcrApproval::where('ecrs_id',$ecrsId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            if($ecrApprovalCurrent->rapidx_user_id != session('rapidx_user_id')){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }
            //Get Current Status
            $ecrDetails= Ecr::where('id',$ecrsId)->get(['id','approval_status','status','category','ecr_no','created_by']);
            //Verify if the ECR Requirement is Completed.
            $isCompletedEcrRequirementComplete = $this->isCompletedEcrRequirementComplete($ecrsId);

            // === TODO:QA Requirements
            // if($ecrApprovalCurrent->approval_status === 'QACB' || $ecrApprovalCurrent->approval_status === 'QAIN'){
            if(  $isCompletedEcrRequirementComplete === 'false' && $request->status === 'APP'){
                // return response()->json(['isSuccess' => 'false','msg' => 'Incomplete details, Please fill up the ECR Requirement!'],500);
            }
            // }

            //Update the ECR Approval Status
            $ecrApprovalCurrent->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);

            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
            $ecrApproval = EcrApproval::where('ecrs_id',$ecrsId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','-')
            ->limit(1)
            ->get(['id','approval_status','rapidx_user_id']);

            //Initialize the Email Address of the User
            $requestedBy = $this->emailInterface->getEmailByRapidxUserId($ecrDetails[0]->created_by);
            //DISAPPROVED ECR
            if ( $request->status === "DIS" ){
                $EcrConditions = [
                    'id' => $request->ecrs_id,
                ];
                $ecrValidated = [
                    'status' => 'DIS',
                ];
                $this->resourceInterface->updateConditions(Ecr::class,$EcrConditions,$ecrValidated);
                //Send DISAPPROVED Email to Requestor
                $to = $requestedBy['email'] ?? '';
                $currentSession = $this->emailInterface->getEmailByRapidxUserId( session('rapidx_user_id'));
                $from =$currentSession['email'] ?? '';
                $from_name = $currentSession['fullName'];
                $subject = "DISAPPROVED: Engineering Change Request (ECR)";
                $msg = $this->emailInterface->ecrEmailMsg($ecrsId);

                //Reset EcrRequirement
                EcrRequirement::where('ecrs_id',$ecrsId)->delete();
                DB::commit();
                $emailData = [
                    "to" =>$to,
                    "cc" =>"",
                    "bcc" =>"mclegaspi@pricon.ph,rdahorro@pricon.ph,jggabuat@pricon.ph",
                    "from" => $from,
                    "from_name" =>$from_name ?? "4M Change Control Management System",
                    "subject" =>$subject,
                    "message" =>  $msg,
                    "attachment_filename" => "",
                    "attachment" => "",
                    "send_date_time" => now(),
                    "date_time_sent" => "",
                    "date_created" => now(),
                    "created_by" => session('rapidx_username'),
                    "system_name" => "rapidx_4M",
                ];
                $this->emailInterface->sendEmail($emailData);
                return response()->json(['isSuccess' => 'true']);
            }
            //If the ECR is Approved, Save the ECR Details by Category
            if ( count($ecrApproval) === 0){

                $EcrConditions = [
                    'id' => $request->ecrs_id,
                ];
                $ecrValidated = [
                    'status' => 'OK', //APPROVED ECR
                    'approval_status' => 'OK',
                ];
                $this->resourceInterface->updateConditions(Ecr::class,$EcrConditions,$ecrValidated);
                // If approved, Save Man, Method, Machine, Material, Environment
                $this->saveDetailsByCategory($ecrDetails[0]->category,$ecrsId);
                //Send Approved Email to the Requestor
                $to = $requestedBy['email'] ?? '';
                // $to =  'cpagtalunan@pricon.ph",';
                $from = 'issinfoservice@pricon.ph';
                $msg = $this->emailInterface->ecrEmailMsg($ecrsId);
                $msgEcrRequirement = $this->emailInterface->ecrEmailMsgEcrRequirement($ecrsId);
                $subject = "APPROVED: Engineering Change Request (ECR)";
                $subjectEcr = "ECR Requirements: " .$ecrDetails[0]->ecr_no;
                $from_name = "4M Change Control Management System";
                $emailDataEcrRequirement = [
                    // "to" =>"cpagtalunan@pricon.ph",
                    // "bcc" =>"mclegaspi@pricon.ph",

                    "to" =>$to,
                    "cc" =>"",
                    "bcc" =>"mclegaspi@pricon.ph,rdahorro@pricon.ph,jggabuat@pricon.ph",
                    "from" => $from,
                    "from_name" =>$from_name ?? "4M Change Control Management System",
                    "subject" =>$subjectEcr,
                    "message" =>  $msgEcrRequirement,
                    "attachment_filename" => "",
                    "attachment" => "",
                    "send_date_time" => now(),
                    "date_time_sent" => "",
                    "date_created" => now(),
                    "created_by" => session('rapidx_username'),
                    "system_name" => "rapidx_4M",
                ];
            }

            if ( count($ecrApproval) != 0 ){
                $ecrCurrentApproval = $this->emailInterface->getEmailByRapidxUserId($ecrApproval[0]->rapidx_user_id);

                $ecrApprovalValidated = [
                    'status' => 'PEN',
                ];
                $ecrApprovalConditions = [
                    'id' => $ecrApproval[0]->id,
                ];
                $this->resourceInterface->updateConditions(EcrApproval::class,$ecrApprovalConditions,$ecrApprovalValidated);

                //Update the ECR Approval Status
                $EcrConditions = [
                    'id' => $request->ecrs_id,
                ];
                $ecrValidated = [
                    'approval_status' => $ecrApproval[0]->approval_status,
                ];
                //Change QA Status
                if (str_contains($ecrApproval[0]->approval_status, 'QA')) {
                    $ecrValidated = [
                        'approval_status' => $ecrApproval[0]->approval_status,
                        'status' => 'QA',
                    ];
                }
                $this->resourceInterface->updateConditions(Ecr::class,$EcrConditions,$ecrValidated);
                //Send Approval Email
                $msg = $this->emailInterface->ecrEmailMsg($ecrsId);
                //Send For Approval Email to Next Approver
                $to = $ecrCurrentApproval['email'] ?? '';
                $from = $requestedBy['email'] ?? '';
                $subject = "FOR APPROVAL: Engineering Change Request (ECR)";
                $from_name = "4M Change Control Management System";
            }
            $emailData = [
                "to" =>$to,
                "cc" =>"",
                "bcc" =>"mclegaspi@pricon.ph,rdahorro@pricon.ph,jggabuat@pricon.ph",
                "from" => $from,
                "from_name" =>$from_name ?? "4M Change Control Management System",
                "subject" =>$subject,
                "message" =>  $msg,
                "attachment_filename" => "",
                "attachment" => "",
                "send_date_time" => now(),
                "date_time_sent" => "",
                "date_created" => now(),
                "created_by" => session('rapidx_username'),
                "system_name" => "rapidx_4M",
            ];

            DB::commit();
            if ( count($ecrApproval) === 0){
                $this->emailInterface->sendEmail($emailDataEcrRequirement);
            }
            $this->emailInterface->sendEmail($emailData);
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveEcrDetails(Request $request, EcrDetailRequest $ecrDetailRequest){
        date_default_timezone_set('Asia/Manila');
        try {
             $ecrDetailRequestValidated = $ecrDetailRequest->validated();
             $ecrDetailRequestValidated['customer_approval'] = $request->customer_approval ?? NULL;
             $ecrDetailRequestValidated['remarks'] = $request->remarks;
             $conditions = [
                 'id' => $request->ecr_details_id
             ];
             // return $ecrDetailRequestValidated;
             $this->resourceInterface->updateConditions(EcrDetail::class,$conditions,$ecrDetailRequestValidated);
             return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
             throw $e;
        }
    }
    public function loadEcr(Request $request){
        try {
            $status = explode(',',$request->status) ?? "";
            $adminAccess = $request->adminAccess;
            $data = [];
            $relations = [
                'ecr_approval_pending',
                'rapidx_user_created_by'
            ];
            $conditions = [];
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,$data,$relations,$conditions);

            if( $adminAccess === 'null' || blank($adminAccess) ){
                $ecr->whereIn('status',$status)
                ->whereHas('ecr_approval',function($query) use ($request,$status){
                    // if is adminAccess exist deactivate the session condition
                    $query->where('status','PEN');
                    $query->where('rapidx_user_id',session('rapidx_user_id'));
                })->get();
            }
            if( $adminAccess === 'created'){
                $ecr->whereIn('status',$status)
                ->where('created_by' , session('rapidx_user_id'))
                ->get();
            }
            if( $adminAccess === 'all') {

                $status =  array_merge($status,['OK']);
                $ecr->whereIn('status',$status)
                ->get();
            }
            $ecr->whereNull('deleted_at');
            $ecr->orderBy('id','DESC');
            return DataTables($ecr)
            ->addColumn('get_actions',function ($row){
                $result = "";
                $result .= '<center>';
                $result .= '<div class="btn-group dropstart mt-4">';
                $result .= "<button ecr-id='".$row->id."' ecr-status='".$row->status."'  type='button' class='btn btn-secondary dropdown-toggle btn-sm' data-bs-toggle='dropdown' aria-expanded='false'>";
                $result .= '    Action';
                $result .= '</button>';
                $result .= '<ul class="dropdown-menu">';
                if($row->status === "IA" && $row->created_by === session('rapidx_user_id')){
                    $result .= "<li> <button ecr-id='".$row->id."' ecr-status='".$row->status."' class='dropdown-item' id='btnGetEcrId'> <i class='fa-solid fa-pen-to-square'></i> Edit</button> </li>";
                }
                if($row->status === "DIS" && $row->created_by === session('rapidx_user_id')){
                    $result .= "<li> <button ecr-id='".$row->id."' ecr-status='".$row->status."' class='dropdown-item' id='btnGetEcrId'> <i class='fa-solid fa-pen-to-square'></i> Edit</button> </li>";
                }
                // if($row->pmi_approvals_pending[0]->rapidx_user->id === session('rapidx_user_id')){
                    $result .= "<li> <button ecr-id='".$row->id."' ecr-status='".$row->status."' class='dropdown-item'  id='btnViewEcrId'> <i class='fa-solid fa-eye'></i> View/Approval</button>
                    </li>";
                // }
                $result .= '</ul>';
                $result .= '</div>';
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_status',function ($row): string{
                // return $row->status;
                $currentApprover = $row->ecr_approval_pending['rapidx_user']['name'] ?? '';

                $getStatus = $this->commonInterface->getEcrStatus($row->status);
                $getApprovalStatus = $this->commonInterface->getEcrApprovalStatus($row->approval_status);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
                $result .= '<br>';
                if($row->status === 'OK'){
                   return  $result .= '';
                }
                if($row->status != 'DIS'){
                    $result .= '<span class="badge rounded-pill bg-danger"> '.$getApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                }

                $result .= '</center>';
                $result .= '</br>';
                return $result;
            })->addColumn('get_details',function ($row) use($request) {
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
                $status = $row->status ?? "";
                $result = '';
                $result .= '<center>';
                $result .= '<a class="btn btn-outline-danger btn-sm mr-1 mt-3" type="button" ecrs-id="'.$row->id.'" ecr-status= "'.$status.'" ecrs-id-encrypted="'.encrypt($row->id).'" id="btnViewEcrRef"><i class="fa-solid fa-download"></i>Attachment</a>';
                $result .= '</center>';
                return $result;
            })
            ->rawColumns([
                'get_actions',
                'get_status',
                'get_attachment',
                'get_details'
            ])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function loadEcrApprovalSummary(Request $request){
        try {
            $ecrsId = $request->ecrsId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'ecrs_id' => $ecrsId
            ];

            $ecr = $this->resourceInterface->readCustomEloquent(EcrApproval::class,$data,$relations,$conditions);
            $ecr = $ecr
            ->whereNotNull('rapidx_user_id')
            ->orderBy('counter','asc')
            ->get();
            return DataTables($ecr)
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
                $getApprovalStatus = $this->commonInterface->getEcrApprovalStatus($row->approval_status);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="badge rounded-pill bg-primary"> '.$getApprovalStatus['approvalStatus'].' </span>';
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
    public function loadEcrDetailsByEcrId(Request $request){
        try {
            $data = [];
            $relations = [
                'dropdown_master_detail_description_of_change',
                'dropdown_master_detail_reason_of_change',
                'dropdown_master_detail_type_of_part',
                'ecr',

            ];
            $conditions = [
                'ecrs_id' => $request->ecr_id
            ];
            $ecrDetail = $this->resourceInterface->readWithRelationsConditionsActive(EcrDetail::class,$data,$relations,$conditions);
            return DataTables($ecrDetail)
            ->addColumn('get_actions',function ($row){
                if($row->ecr->created_by === session('rapidx_user_id')){
                    $result = '';
                    $result .= '<center>';
                    $result .= "<button class='btn btn-outline-info btn-sm mr-1 btn-get-ecr-id' ecr-details-id='".$row->id."' id='btnGetEcrDetailsId'> <i class='fa-solid fa-pen-to-square'></i></button>";
                    $result .= '</center>';
                    return $result;
                }
            })
            ->addColumn('reason_of_change',function ($row){
                $result = '';
                $result .= $row->dropdown_master_detail_reason_of_change->dropdown_masters_details ?? '';
                return $result;
            })
            ->addColumn('description_of_change',function ($row){
                $result = '';
                $result .= $row->dropdown_master_detail_description_of_change->dropdown_masters_details ?? '';
                return $result;
            })
            ->addColumn('type_of_part',function ($row){
                $result = '';
                $result .= $row->dropdown_master_detail_type_of_part->dropdown_masters_details ?? '';
                return $result;
            })
            ->addColumn('get_customer_approval',function ($row){
                $result = '';
                $result .= $row->customer_approval;
                $result = '';
                switch ($row->customer_approval) {
                    case 'R':
                        $bgColor = 'bg-success text-white';
                        $customerApproval = 'REQUIRED';
                        break;
                    case 'NR':
                        $bgColor = 'bg-warning text-white';
                        $customerApproval = 'NOT REQUIRED';
                        break;
                    default:
                        $bgColor = 'bg-secondary text-white';
                        $customerApproval = 'N/A';
                        break;
                }
                $result .='<span class="badge '.$bgColor.'"> '.$customerApproval.' </span>';
                return $result;
            })
            ->rawColumns([
                'get_actions',
                'reason_of_change',
                'description_of_change',
                'type_of_part',
                'get_customer_approval',
            ])
            ->make(true);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function loadEcrRequirements(Request $request){
        try {
            $category4M = $request->category4M ?? NULL;
            $ecrsId = $request->ecrsId;
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,[],[],[
               'id' =>  $ecrsId,
               'status' =>  'OK'
            ]);
            $ecrApprovedCount = $ecr->count();

            $data = [];
            $relations = [
                'ecr_requirement',
            ];
            $conditions = [
                'classifications_id' => $request->category
            ];

            $classificationRequirement = $this->resourceInterface->readCustomEloquent(ClassificationRequirement::class,$data,$relations,$conditions);
            //If ECR Approved, show the CHECK decision only per Category
            if( $ecrApprovedCount === 1){
                $classificationRequirement = $classificationRequirement->whereHas('ecr_requirement', function ($query) use ($ecrsId) {
                    $query->where('decision', 'C');
                    $query->where('ecrs_id', $ecrsId);
                })->with(['ecr_requirement' => function ($query) use ($ecrsId) {
                    $query->where('decision', 'C');
                    $query->where('ecrs_id', $ecrsId);
                }])->get();
           }else{
                $classificationRequirement = $classificationRequirement
                ->get();
           }

            $ecrRequirement = $this->resourceInterface->readWithRelationsConditionsActive(EcrRequirement::class,[],[],
                [
                    'ecrs_id' => $request->ecrsId ?? ""
                ]
            );
            return DataTables($classificationRequirement)
            ->addColumn('get_actions',function ($row) use($ecrRequirement,$request) {
                $ecrRequirementCollection = collect($ecrRequirement);
                $ecrRequirementMatch = $ecrRequirementCollection->firstWhere('classification_requirements_id', $row->id);
                $ecrRequirementId = $ecrRequirementMatch['id'] ?? '';
                $cSelected = $ecrRequirementMatch['decision'] === 'C' ? 'selected' : '';
                $xSelected = $ecrRequirementMatch['decision'] === 'X' ? 'selected' : '';
                $naSelected = $ecrRequirementMatch['decision'] === 'N/A' ? 'selected' : '';
                if($ecrRequirementId === ''){
                    $isValid = "is-invalid";
                    $emptySelected = "selected";
                }else{
                    $isValid = "";
                    $emptySelected = "";
                }
                $result = '';
                $result .= '<center>';
                $ecr = Ecr::where('id',$request->ecrsId)->first(['status']);
                $ecrApprovalPendingCount = EcrApproval::where('ecrs_id',$request->ecrsId)
                ->where('status','PEN')
                ->where('rapidx_user_id',session('rapidx_user_id'))
                ->count();
                $enabledDisabledSelect ='';
                if($ecr['status'] === 'DIS' || $ecr['status'] === 'OK'){
                    $enabledDisabledSelect = 'disabled';
                }
                if($ecrApprovalPendingCount === 0){
                    $enabledDisabledSelect = 'disabled';
                }

                $result .= "<select ".$enabledDisabledSelect." id='btnChangeEcrReqDecision' class='form-select btn-change-ecr-req-decision ".$isValid."' ref=btnChangeEcrReqDecision ecr-requirements-id ='".$ecrRequirementId."' classification-requirement-id='".$row->id."'>";
                $result .=  "<option value='' ".$emptySelected." disabled> --Select-- </option>";
                $result .=  "<option value='N/A' ".$naSelected."> N/A </option>";
                $result .=  "<option value='C' ".$cSelected."> √ </option>";
                $result .=  "<option value='X' ".$xSelected."> X </option>";
                $result .=  "</select>";
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_view_ecr_req_ref',function ($row) {
                $filteredDocumentName = $row->ecr_requirement->filtered_document_name ?? null;
                $result = "";
                if($filteredDocumentName != null){
                    $result .= ' <a ecr-requirements-id="'.$row->ecr_requirement->id.'" ecrs-id="'.$row->ecr_requirement->ecrs_id.'" href="#" id="btnViewEcrRequirementRef" class="link-primary"> View Reference </a>';
                }
                return $result;
            })
            ->rawColumns([
                'get_actions',
                'get_view_ecr_req_ref',
            ])
            ->make(true);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getFilteredSection($department){
        try {
            if ( Str::contains($department, "LQC")) {
                $filteredSection = "LQC";
            } elseif (Str::contains($department, "Engineering")) {
                $filteredSection = "ENG'G";
            } elseif (Str::contains($department, "Production")) {
                $filteredSection = "PROD";
            }elseif (Str::contains($department, "-")) {
                $filteredSection = "LOG-PCH";
            }elseif (Str::contains($department, "Quality Management Department")) {
                $filteredSection = "QA";
            }else {
                $filteredSection = "???";
            }
            return $filteredSection;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function generateControlNumber(){
        date_default_timezone_set('Asia/Manila');
        //Systemon HRIS / Subcon
        $rapidx_user = DB::connection('mysql_rapidx')
        ->select(" SELECT department_group
            FROM departments
            WHERE department_id = '".session('rapidx_department_id')."'
        ");
        $hris_data = DB::connection('mysql_systemone_hris')
        ->select("SELECT Department,Division,Section FROM vw_employeeinfo WHERE EmpNo = '".session('rapidx_employee_number')."'");
        $subcon_data = DB::connection('mysql_systemone_subcon')
        ->select("SELECT Department,Division,Section FROM vw_employeeinfo WHERE EmpNo = '".session('rapidx_employee_number')."'");
        if(count($hris_data) > 0 && count($rapidx_user)> 0){
            $vwEmployeeinfo =  $hris_data;
            $filteredSection = str_replace("'", "", $this->getFilteredSection($vwEmployeeinfo[0]->Department));
            $division =($rapidx_user[0]->department_group == "PPS" || $rapidx_user[0]->department_group == "PPD") ? "PPD" : (($rapidx_user[0]->department_group == "LOG" || $rapidx_user[0]->department_group == "ISS" || $rapidx_user[0]->department_group == "FIN" ) ? "ADMIN" :
            $rapidx_user[0]->department_group);
        }
        if(count($subcon_data) > 0 && count($rapidx_user) > 0){
            $vwEmployeeinfo =  $subcon_data;
            $filteredSection = str_replace("'", "", $this->getFilteredSection($vwEmployeeinfo[0]->Department));
            $division = ($rapidx_user[0]->department_group == "PPS" || $rapidx_user[0]->department_group == "PPD") ? "PPD" : (($rapidx_user[0]->department_group == "LOG" || $rapidx_user[0]->department_group == "ISS" || $rapidx_user[0]->department_group == "FIN")  ? "ADMIN" :
            $rapidx_user[0]->department_group);
        }
        // Check if the Created At & App No / Division / Material Category is exisiting
        // Example:TS-ADMIN-LOG-PCH-25-01-001
        $ecr = Ecr::orderBy('id','desc')->whereYear('created_at',now())
            ->whereNull('deleted_at')
            ->limit(1)->get(['ecr_no']);
        //If not exist reset the ecr to 1 ???
        if(count( $ecr ) != 0){
            $currentCtrlNo = explode('-',$ecr[0]->ecr_no);
            $arrCtrNo		 	= end($currentCtrlNo);
            $series 	 	= str_pad(($arrCtrNo+1),3,"0",STR_PAD_LEFT);
            $currentCtrlNo = $division."-".$filteredSection."-".date('m').date('y').'-'.$series;

        }else{
            $currentCtrlNo = $division."-".$filteredSection."-".date('m').date('y').'-001';
        }
        return [
            'currentCtrlNo' => $currentCtrlNo
        ];
    }
    public function isCompletedEcrRequirementComplete($ecrsId){ //Requirement
        $classificationRequirementCount =  ClassificationRequirement::whereIn('classifications_id',[1,2,3,4,5])->count();
        $ecrRequirementCount = EcrRequirement::where('ecrs_id',$ecrsId)->count();
        return $classificationRequirementCount === $ecrRequirementCount ? 'true' : 'false';
    }
    public function getDropdownMasterByOpt(Request $request){
        try {
            $data = [];

            $relations = [
                'dropdown_master_details'
            ];

            $conditions = [
                'table_reference' => $request->tblReference,
                'deleted_at' => NULL,
            ];

            $dropdownMasterByOpt = $this->resourceInterface->readWithRelationsConditions(DropdownMaster::class,$data,$relations,$conditions);
            $dropdownMasterByOpt = $dropdownMasterByOpt[0]->dropdown_master_details;
           return response()->json(['is_success' => 'true','dropdownMasterByOpt' => $dropdownMasterByOpt]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getEcrById(Request $request){
        try {
            // return 'true' ;
            $data = [];
            $relations = [
                'ecr_details',
                'ecr_approvals',
                'pmi_approvals',

            ];
            $conditions = [
                'id' => $request->ecr_id
            ];

            $ecr = $this->resourceInterface->readWithRelationsConditionsActive(Ecr::class,$data,$relations,$conditions);
            $ecrApprovalCollection = collect($ecr[0]->ecr_approvals)->groupBy('approval_status')->toArray();
            $pmiApprovalCollection = collect($ecr[0]->pmi_approvals)->groupBy('approval_status')->toArray();
            return response()->json(['is_success' => 'true', 'ecr' => $ecr[0] ,
                'ecrApprovalCollection' => $ecrApprovalCollection,
                'pmiApprovalCollection'=>$pmiApprovalCollection
            ]);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getEcrDetailsId(Request $request){
        try {
            $data = [];
            $conditions = [
                'id' => $request->ecrDetailsId
            ];

            $relations = [
                'dropdown_master_detail_description_of_change',
                'dropdown_master_detail_reason_of_change',
                'dropdown_master_detail_type_of_part',
            ];
            $ecrDetail = $this->resourceInterface->readWithRelationsConditionsActive(EcrDetail::class,$data,$relations,$conditions);
            return response()->json(['is_success' => 'true', 'ecrDetail' => $ecrDetail[0] ,
            ]);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function ecrReqDecisionChange(Request $request){
        try {
            if( isset($request->ecr_req_id) ){ //edit
                $conditions = [
                    'id' => $request->ecr_req_id
                ];
                $data = [
                    'classification_requirements_id' => $request->classification_requirement_id,
                    'decision' => $request->ecr_req_value,
                    'updated_by' => session('rapidx_user_id'),
                ];

                $this->resourceInterface->updateConditions(EcrRequirement::class,$conditions,$data);
            }else{ //add
                $data = [
                    'classification_requirements_id' => $request->classification_requirement_id,
                    'decision' => $request->ecr_req_value,
                    'ecrs_id' => $request->ecrsId,
                ];
                $this->resourceInterface->create(EcrRequirement::class,$data);
            }

            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {

            throw $e;
        }
    }
   public function getPmiApprovalStatus($approval_status){
       try {
            switch ($approval_status) {
                case 'PB':
                    $approvalStatus = 'Prepared by:';
                    break;
                case 'CB':
                    $approvalStatus = 'Checked by:';
                    break;
                case 'AP':
                    $approvalStatus = 'Approved by:';
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
   public function index(Request $request){
       return 'true' ;
       try {
           date_default_timezone_set('Asia/Manila');
           DB::beginTransaction();
           DB::commit();
           return response()->json(['is_success' => 'true']);
       } catch (Exception $e) {
           DB::rollback();
           throw $e;
       }
   }
   public function saveDetailsByCategory($category,$ecrsId){
       try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $ecr = Ecr::find($ecrsId);
            switch  ($category) {
                case 'Man':
                    $currentModel = Man::class;
                    $manApproval = ManApproval::class;
                    $requestValidated = [
                        'ecrs_id' => $ecrsId,
                        'approval_status' => 'RUP',
                        'created_at' => now(),
                    ];
                    $approvalRequest = [
                        'ecrs_id' => $ecrsId,
                        'rapidx_user_id' => $ecr->created_by,
                        'approval_status' => 'RUP',
                        'created_at' => now(),
                    ];
                    $currentModel::where('ecrs_id', $ecrsId)->delete();
                    $this->resourceInterface->create($currentModel,$requestValidated);
                    $manApproval::where('ecrs_id', $ecrsId)->delete();
                    $manApproval::insert($approvalRequest);
                    $manApproval::where('ecrs_id', $ecrsId)
                    ->update(['status'=>'PEN']);
                    break;
                case 'Material':
                    $currentModel = Material::class;
                    $requestValidated = [
                        'ecrs_id' => $ecrsId,
                        'status' => 'RUP',
                        'approval_status' => 'RUP',
                        'created_at' => now(),
                    ];
                    $currentModel::where('ecrs_id', $ecrsId)->delete();
                    $this->resourceInterface->create($currentModel,$requestValidated);
                    break;
                case 'Machine':
                    $currentModel = Machine::class;
                    $requestValidated = [
                        'ecrs_id' => $ecrsId,
                        'status' => 'RUP',
                        'approval_status' => 'RUP',
                        'created_at' => now(),
                    ];
                    $currentModel::where('ecrs_id', $ecrsId)->delete();
                    $this->resourceInterface->create($currentModel,$requestValidated);
                    break;
                case 'Method':
                    $currentModel = Method::class;
                    $requestValidated = [
                        'ecrs_id' => $ecrsId,
                        'status' => 'RUP',
                        'approval_status' => 'RUP',
                        'created_at' => now(),
                    ];
                    $currentModel::where('ecrs_id', $ecrsId)->delete();
                    $this->resourceInterface->create($currentModel,$requestValidated);
                    break;
                case 'Environment':
                    $currentModel = Environment::class;
                    $requestValidated = [
                        'ecrs_id' => $ecrsId,
                        'status' => 'RUP',
                        'created_at' => now(),
                    ];
                    $currentModel::where('ecrs_id', $ecrsId)->delete();
                    $this->resourceInterface->create($currentModel,$requestValidated);
                    break;
                default:
                    //TODO:Error Handling
                    break;
            }
            DB::commit();
       } catch (Exception $e) {
           throw $e;
           DB::rollback();
       }
   }
   public function uploadEcrRequirementRef(EcrRequirementFileRequest $ecrRequirementFileRequest){
       try { //nmodify
           date_default_timezone_set('Asia/Manila');
           DB::beginTransaction();
            $ecrsId = $ecrRequirementFileRequest->ecrsId;
            $ecrRequirementId = $ecrRequirementFileRequest->ecrRequirementId;
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,['category'],[],
                [
                    'id' => $ecrsId,
                ]
            );
            $ecr = $ecr->first();

            $ecrRequirementFile = $ecrRequirementFileRequest->ecrRequirementFile;
            $path = "ecr_requirement/".$ecr->category."/".$ecrRequirementId."/";
            if($ecrRequirementFileRequest->hasfile('ecrRequirementFile')){
                $arrUploadFile = $this->commonInterface->uploadFileEcrRequirement($ecrRequirementFile,$path);
                $impOriginalFilename = implode(' | ',$arrUploadFile['arr_original_filename']);
                $impFilteredDocumentName = implode(' | ',$arrUploadFile['arr_filtered_document_name']);

                $ecrRequirementFileRequestValidated['original_filename'] = $impOriginalFilename;
                $ecrRequirementFileRequestValidated['filtered_document_name'] = $impFilteredDocumentName;
                $ecrRequirementFileRequestValidated['updated_by'] = session('rapidx_user_id');
            }
             $conditions = [
                 'id' =>  $ecrRequirementId
             ];
             $this->resourceInterface->updateConditions(EcrRequirement::class,$conditions,$ecrRequirementFileRequestValidated);
           DB::commit();
           return response()->json(['is_success' => 'true']);
       } catch (Exception $e) {
           DB::rollback();
           throw $e;
       }
   }
   public function getEcrRequirementRefById(Request $request){
       try {
            $ecrRequirementsId = $request->ecrRequirementsId;
            $conditions = [
                'id' => $ecrRequirementsId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(EcrRequirement::class,[],[],$conditions);
            $ecrRequirementRefById = $data
            ->get([
                'id',
                'original_filename',
            ]);
            return response()->json([
                'isSuccess' => 'true',
                'originalFilename'=> explode(' | ',$ecrRequirementRefById[0]->original_filename),
                'ecrRequirementsId'=> encrypt($ecrRequirementRefById[0]->id),
            ]);
        } catch (Exception $e) {
            throw $e;
        }
   }
   public function viewEcrRequirementRef(Request $request){
    try {
        $ecrRequirementsId = decrypt($request->ecrRequirementsId);
        //Get the EcrRequirement Filtered Document
        $conditions = [
            'id' => $ecrRequirementsId,
        ];
        $data = $this->resourceInterface->readCustomEloquent(EcrRequirement::class,[],[],$conditions);
        $materialRefByEcrsId = $data
        ->first([
            'filtered_document_name',
            'ecrs_id',
        ]);
        //Get the Category
        $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,['category'],[],
            [
                'id' => $materialRefByEcrsId->ecrs_id  ?? 0,
            ]
        );
        $ecr = $ecr->first();
        $path = "ecr_requirement/".$ecr->category."/".$ecrRequirementsId."/";

        if(filled($materialRefByEcrsId)){
            $arrFilteredDocumentName = explode(' | ' ,$materialRefByEcrsId->filtered_document_name);
            $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
            $filePathWithEcrRequirementsId = $path;
            $pdfPath = storage_path("app/public/".$filePathWithEcrRequirementsId.$selectedFilteredDocumentName);
            $this->commonInterface->viewPdfFile($pdfPath);
        }
    } catch (Exception $e) {
        throw $e;
    }
   }
   public function getEcrRefDownload(Request $request){
        try {

            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,[],[

            ],['id' => decrypt($request->ecrsId)]);
            $ecr = $ecr->first();
            return response()->json([
                'originalFilename' => explode(' | ',$ecr->original_filename),
                'filteredDocumentName' => explode(' | ',$ecr->filtered_document_name),
                'isSuccess' => 'true',
                'ersIdEncryted' => encrypt($ecr->id),
        ]);
        } catch (Exception $e) {
            throw $e;
        }
   }
   public function viewEcrRef(Request $request){
        try {
            $ecrsId = decrypt($request->ecrsId);
            $conditions = [
                'id' => $ecrsId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(Ecr::class,[],[],$conditions);
          $ecrRefByEcrsId = $data
            ->get([
                'filtered_document_name',
                'category',
            ]);
            if(count($ecrRefByEcrsId) != 0){
                $arrFilteredDocumentName = explode(' | ' ,$ecrRefByEcrsId[0]->filtered_document_name);
                $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
                $filePathWithEcrsId = $ecrRefByEcrsId[0]->file_path."/".$ecrsId."/".$selectedFilteredDocumentName;
                $path = "app/public/ecr/".$ecrRefByEcrsId[0]->category."/".$filePathWithEcrsId;
                $pdfPath = storage_path($path);
                $this->commonInterface->viewPdfFile($pdfPath);
            }
        } catch (Exception $e) {
            throw $e;
        }
   }



}
