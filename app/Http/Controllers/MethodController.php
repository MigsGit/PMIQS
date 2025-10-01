<?php

namespace App\Http\Controllers;

use App\Models\Ecr;
use App\Models\Method;
use Illuminate\Http\Request;
use App\Models\MethodApproval;
use App\Models\MachineApproval;
use App\Interfaces\EmailInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Models\ExternalDisposition;
use App\Http\Controllers\Controller;
use App\Interfaces\ResourceInterface;
use App\Http\Requests\MethodFileRequest;

class MethodController extends Controller
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
    public function saveMethod(Request $request, MethodFileRequest $methodFileRequest){
        try {
            DB::beginTransaction();
            $methodRequestValidated = [];
            $ecrsId = $methodFileRequest->ecrsId;
            $methodsId = $methodFileRequest->methodsId;

            if($methodFileRequest->hasfile('methodRefBefore') && $methodFileRequest->hasfile('methodRefAfter')){
               $arrUploadFile = $this->commonInterface->uploadFileImg($methodFileRequest->methodRefBefore,$methodFileRequest->methodRefAfter,$methodsId,'method');
                $impOriginalFilenameBefore = implode(' | ',$arrUploadFile['arr_original_filename_before']);
                $impFilteredDocumentNameBefore = implode(' | ',$arrUploadFile['arr_filtered_document_name_before']);
                $impOriginalFilenameAfter = implode(' | ',$arrUploadFile['arr_original_filename_after']);
                $impFilteredDocumentNameAfter = implode(' | ',$arrUploadFile['arr_filtered_document_name_after']);

                $methodRequestValidated['original_filename_before'] = $impOriginalFilenameBefore;
                $methodRequestValidated['filtered_document_name_before'] = $impFilteredDocumentNameBefore;
                $methodRequestValidated['original_filename_after'] = $impOriginalFilenameAfter;
                $methodRequestValidated['filtered_document_name_after'] = $impFilteredDocumentNameAfter;

            }
            $conditions = [
                'id' =>  $methodsId
            ];
            $this->resourceInterface->updateConditions(Method::class,$conditions,$methodRequestValidated);
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

           $methodApprovalValidated = collect($arrMachineApprovalRequest)->flatMap(function ($users,$approvalStatus) use ($request,$ecrsId){
                return collect($users)->map(function ($userId) use ($request,$approvalStatus,&$ecrsId){
                    return [
                        'ecrs_id' => $ecrsId,
                        'methods_id' => $request->methodsId,
                        'rapidx_user_id' => $userId == 0 ? NULL : $userId,
                        'approval_status' => $approvalStatus,
                        'created_at' => now(),
                    ];
                });

            })->toArray();
            MethodApproval::where('methods_id',$methodsId)->delete();
            MethodApproval::insert($methodApprovalValidated);
            $methodApproval =  MethodApproval::whereNotNull('rapidx_user_id')
            ->whereNull('deleted_at')
            ->where('methods_id', $methodsId)
            ->first();
            if ($methodApproval) {
                $methodApproval->update(['status' => 'PEN']);
                Method::where('id', $methodsId)->first()
                ->update([
                    'approval_status' => $methodApproval->approval_status,
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
           $ecrApprovalCurrent = MethodApproval::where('ecrs_id',$ecrsId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            $ecrCurrentApproval = $this->emailInterface->getEmailByRapidxUserId( $ecrApprovalCurrent->rapidx_user_id);
            $to = $ecrCurrentApproval['email'] ?? '';
            $from = 'issinfoservice@pricon.ph';
            $subject = "FOR APPROVAL: Method (4M)";
            $from_name = "4M Change Control Management System";
            $msg = $this->emailInterface->ecrEmailMsgByCategory($ecrsId,'METHOD');
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
            $this->emailInterface->sendEmail($emailData);
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveMethodTest(Request $request, MethodFileRequest $methodFileRequest){
        try {
            DB::beginTransaction();
            $methodRequestValidated = [];
            $ecrsId = $methodFileRequest->ecrsId;
            $methodsId = $methodFileRequest->methodsId;

            if($methodFileRequest->hasfile('methodRefBefore') && $methodFileRequest->hasfile('methodRefAfter')){
               $arrUploadFile = $this->commonInterface->uploadFileImg($methodFileRequest->methodRefBefore,$methodFileRequest->methodRefAfter,$methodsId,'method');
                $impOriginalFilenameBefore = implode(' | ',$arrUploadFile['arr_original_filename_before']);
                $impFilteredDocumentNameBefore = implode(' | ',$arrUploadFile['arr_filtered_document_name_before']);
                $impOriginalFilenameAfter = implode(' | ',$arrUploadFile['arr_original_filename_after']);
                $impFilteredDocumentNameAfter = implode(' | ',$arrUploadFile['arr_filtered_document_name_after']);

                $methodRequestValidated['original_filename_before'] = $impOriginalFilenameBefore;
                $methodRequestValidated['filtered_document_name_before'] = $impFilteredDocumentNameBefore;
                $methodRequestValidated['original_filename_after'] = $impOriginalFilenameAfter;
                $methodRequestValidated['filtered_document_name_after'] = $impFilteredDocumentNameAfter;

            }
            $conditions = [
                'id' =>  $methodsId
            ];
            $this->resourceInterface->updateConditions(Method::class,$conditions,$methodRequestValidated);
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

           $methodApprovalValidated = collect($arrMachineApprovalRequest)->flatMap(function ($users,$approvalStatus) use ($request,$ecrsId){
                return collect($users)->map(function ($userId) use ($request,$approvalStatus,&$ecrsId){
                    return [
                        'ecrs_id' => $ecrsId,
                        'methods_id' => $request->methodsId,
                        'rapidx_user_id' => $userId == 0 ? NULL : $userId,
                        'approval_status' => $approvalStatus,
                        'created_at' => now(),
                    ];
                });

            })->toArray();
            MethodApproval::where('methods_id',$methodsId)->delete();
            MethodApproval::insert($methodApprovalValidated);
            $methodApproval =  MethodApproval::whereNotNull('rapidx_user_id')
            ->whereNull('deleted_at')
            ->where('methods_id', $methodsId)
            ->first();
            if ($methodApproval) {
                $methodApproval->update(['status' => 'PEN']);
                Method::where('id', $methodsId)->first()
                ->update([
                    'approval_status' => $methodApproval->approval_status,
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

    public function saveMethodApprovalTest1(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $selectedId = $request->selectedId;
            //Get Current Ecr Approval is equal to Current Session
            $methodApprovalCurrent = MethodApproval::where('methods_id',$selectedId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            if($methodApprovalCurrent->rapidx_user_id != session('rapidx_user_id')){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }
            //Update the machine Approval Status
            $methodApprovalCurrent->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);
            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
           $methodApproval = MethodApproval::where('methods_id',$selectedId)
           ->whereNotNull('rapidx_user_id')
           ->where('status','-')
           ->limit(1)
           ->get(['id','approval_status']);
            if ( count($methodApproval) != 0){
                $methodApprovalValidated = [
                    'status' => 'PEN',
                ];
                $methodApprovalConditions = [
                    'id' => $methodApproval[0]->id,
                ];
                $this->resourceInterface->updateConditions(MethodApproval::class,$methodApprovalConditions,$methodApprovalValidated);
                //Update the ECR Approval Status
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'approval_status' => $methodApproval[0]->approval_status,
                ];
                $this->resourceInterface->updateConditions(Method::class,$enviromentConditions,$enviromentValidated);
            }else{
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'status' => 'PMIAPP',
                    'approval_status' => 'PB',
                ];
                $this->resourceInterface->updateConditions(Method::class,$enviromentConditions,$enviromentValidated);
            }
             //DISAPPROVED ECR
             if($request->status === "DIS"){
                $conditions = [
                    'id' => $selectedId,
                ];
                $requestValidated = [
                    'status' => 'DIS',
                    'approval_status' => 'DIS', //Repeat the status
                ];
                $this->resourceInterface->updateConditions(Method::class,$conditions,$requestValidated);
            }
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveMethodApproval(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $selectedId = $request->selectedId;
            //Get Current Ecr Approval is equal to Current Session
            $methodApprovalCurrent = MethodApproval::where('methods_id',$selectedId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','PEN')
            ->first();
            $methodCurrent = Method::findOrFail($selectedId);
            //Get Current Status
            $ecrDetails= Ecr::where('id',$methodCurrent->ecrs_id)->get(['id','approval_status','status','category','ecr_no','created_by']);
            $createdByEmail= $this->emailInterface->getEmailByRapidxUserId($ecrDetails[0]->created_by ?? '');
            if($methodApprovalCurrent->rapidx_user_id != session('rapidx_user_id')){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }

            //Update the machine Approval Status
            $methodApprovalCurrent->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);
            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
           $methodApproval = MethodApproval::where('methods_id',$selectedId)
           ->whereNotNull('rapidx_user_id')
           ->where('status','-')
           ->limit(1)
           ->get(['id','approval_status','rapidx_user_id']);
            if ( count($methodApproval) != 0){
                $ecrCurrentApproval = $this->emailInterface->getEmailByRapidxUserId($methodApproval[0]->rapidx_user_id);
                $methodApprovalValidated = [
                    'status' => 'PEN',
                ];
                $methodApprovalConditions = [
                    'id' => $methodApproval[0]->id,
                ];
                $this->resourceInterface->updateConditions(MethodApproval::class,$methodApprovalConditions,$methodApprovalValidated);
                //Update the ECR Approval Status
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'approval_status' => $methodApproval[0]->approval_status,
                ];
                $this->resourceInterface->updateConditions(Method::class,$enviromentConditions,$enviromentValidated);

                //Send Approval Email
                $msg = $this->emailInterface->ecrEmailMsgByCategory($methodCurrent->ecrs_id,'METHOD');
                //Send For Approval Email to Next Approver
                $to = $ecrCurrentApproval['email'] ?? '';
                $from = $createdByEmail['email'] ?? '';
                $subject = "FOR APPROVAL:  Method (4M)";
                $from_name = "4M Change Control Management System";
            }else{
                $enviromentConditions = [
                    'id' => $selectedId,
                ];
                $enviromentValidated = [
                    'status' => 'PMIAPP',
                    'approval_status' => 'PB',
                ];
                $this->resourceInterface->updateConditions(Method::class,$enviromentConditions,$enviromentValidated);

            }
             //DISAPPROVED ECR
             if($request->status === "DIS"){
                $conditions = [
                    'id' => $selectedId,
                ];
                $requestValidated = [
                    'status' => 'DIS',
                    'approval_status' => 'DIS', //Repeat the status
                ];
                $this->resourceInterface->updateConditions(Method::class,$conditions,$requestValidated);

                // $to = $requestedBy['email'] ?? '';
                // $currentSession = $this->emailInterface->getEmailByRapidxUserId( session('rapidx_user_id'));
                // $from =$currentSession['email'] ?? '';
                // $from_name = $currentSession['fullName'];
                // $subject = "DISAPPROVED: Engineering Change Request (ECR)";
                // $msg = $this->emailInterface->ecrEmailMsg($ecrsId);

                // $emailData = [
                //     "to" =>$to,
                //     "cc" =>"",
                //     "bcc" =>"mclegaspi@pricon.ph,rdahorro@pricon.ph,jggabuat@pricon.ph",
                //     "from" => $from,
                //     "from_name" =>$from_name ?? "4M Change Control Management System",
                //     "subject" =>$subject,
                //     "message" =>  $msg,
                //     "attachment_filename" => "",
                //     "attachment" => "",
                //     "send_date_time" => now(),
                //     "date_time_sent" => "",
                //     "date_created" => now(),
                //     "created_by" => session('rapidx_username'),
                //     "system_name" => "rapidx_4M",
                // ];
                // $this->emailInterface->sendEmail($emailData);
                    return response()->json(['isSuccess' => 'true']);
                }
                $emailData = [
                   "to" => $to,
                    "cc" => $from,
                    "bcc" =>"mclegaspi@pricon.ph,rdahorro@pricon.ph,jggabuat@pricon.ph",
                    // "bcc" =>"mrronquez@pricon.ph",
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
            $this->emailInterface->sendEmail($emailData);
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function loadMachineApproverSummaryMaterialId (Request $request){
        try {
            $methodsId = $request->methodsId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'methods_id' => $methodsId
            ];
            $methodApproval = $this->resourceInterface->readCustomEloquent(MethodApproval::class,$data,$relations,$conditions);
            $methodApproval = $methodApproval
            ->whereNotNull('rapidx_user_id')
            ->whereNull('deleted_at')
            ->orderBy('id','asc')
            ->get();
            return DataTables($methodApproval)
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
    public function loadMethodEcrByStatus(Request $request){
        try {
            $adminAccess = $request->adminAccess;
            $data = [];
            $relations = [
                'method.method_approvals_pending',
                'method',
            ];
            $conditions = [
                'status' => 'OK',
                'category' => $request->category
            ];
            $ecr = $this->resourceInterface->readCustomEloquent(Ecr::class,$data,$relations,$conditions);
            $ecr->whereNull('deleted_at');

            if( $adminAccess === 'null' || blank($adminAccess) ){
                return $ecr->whereHas('method.method_approvals_pending',function($query){
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
                    'method',
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
                $methodStatus = $row->method->status ?? "";
                $currentApprover = $row->method->method_approvals_pending[0]['rapidx_user']['id'] ?? '';

                $pmiApprovalsPending = $row->pmi_approvals_pending[0]->rapidx_user->id ?? "";
                $result = "";
                $result .= '<center>';
                $result .= '<div class="btn-group dropstart mt-4">';
                $result .= '<button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">';
                $result .= '    Action';
                $result .= '</button>';
                $result .= '<ul class="dropdown-menu">';
                if($methodStatus === "EXDISPO" || $methodStatus === "OK"){
                    //Upload External Disposition
                    // $result .= '<li><button class="dropdown-item" type="button" ecrs-id="'.$row->id.'"id="btnViewDispotionById"><i class="fa-solid fa-file"></i> &nbsp;Upload Disposition</button></li>';
                    $result .= '<li><button class="dropdown-item" type="button" methods-id="'.$row->method->id.'" ecrs-id="'.$row->id.'" method-status= "'.$methodStatus.'" id="btnViewMethodById"><i class="fa-solid fa-eye"></i> &nbsp;View/Approval</button></li>';
                    return $result;
                }
                if($methodStatus === "RUP" && $row->created_by === session('rapidx_user_id')){
                    $result .= '   <li><button class="dropdown-item" type="button" methods-id="'.$row->method->id.'" ecrs-id="'.$row->id.'" method-status= "'.$methodStatus.'" id="btnGetEcrId"><i class="fa-solid fa-edit"></i> &nbsp;Edit</button></li>';
                }
                if($pmiApprovalsPending === session('rapidx_user_id') || $currentApprover ===  session('rapidx_user_id')){
                    $result .= '<li><button class="dropdown-item" type="button" methods-id="'.$row->method->id.'" ecrs-id="'.$row->id.'" method-status= "'.$methodStatus.'" id="btnViewMethodById"><i class="fa-solid fa-eye"></i> &nbsp;View/Approval</button></li>';
                }

                $result .= '</ul>';
                $result .= '</div>';
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_status',function ($row) use($request){
                $methodStatus = $row->method->status ?? "";
                $currentApprover = $row->method->method_approvals_pending[0]['rapidx_user']['name'] ?? '';
                $getStatus = $this->getStatus($methodStatus);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
                $result .= '<br>';
                $getApprovalStatus = $this->getApprovalStatus($row->method->approval_status);
                if($row->status != 'DIS' && $currentApprover != ''){
                    $result .= '<span class="badge rounded-pill bg-danger"> '.$getApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                }
                if( $methodStatus === 'PMIAPP' ){ //TODO: Last Status PMI Internal
                    $currentApprover = $row->pmi_approvals_pending[0]['rapidx_user']['name'] ?? '';
                    $approvalStatus = $row->method->approval_status;
                    $getPmiApprovalStatus = $this->commonInterface->getPmiApprovalStatus($approvalStatus);
                    $result .= '<span class="badge rounded-pill bg-danger"> '.$getPmiApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                }
                $result .= '</center>';
                $result .= '</br>';
                return $result;
            })
            ->addColumn('get_attachment',function ($row) use ($request){
                $methodStatus = $row->method->status ?? "";
                $result = '';
                $result .= '<center>';
                $result .= '<a class="btn btn-outline-danger btn-sm mr-1 mt-3" type="button" methods-id="'.$row->method->id.'" ecrs-id="'.$row->id.'" method-status= "'.$methodStatus.'" id="btnViewMethodRef"><i class="fa-solid fa-download"></i>Attachment</a>';
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
    public function loadMethodApproverSummaryMaterialId (Request $request){
        try {
            $methodsId = $request->methodsId ?? "";
            $data = [];
            $relations = [
                'rapidx_user'
            ];
            $conditions = [
                'methods_id' => $methodsId
            ];
            $methodpproval = $this->resourceInterface->readCustomEloquent(MethodApproval::class,$data,$relations,$conditions);
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
    public function viewMethodRef(Request $request){ //nmodify
        try {
            $methodsId = decrypt($request->methodsId);
            $conditions = [
                'id' => $methodsId,
            ];
           $data = $this->resourceInterface->readCustomEloquent(Method::class,[],[],$conditions);
          $methodRefByEcrsId = $data
            ->get([
                'filtered_document_name_before',
                'filtered_document_name_after',
                'file_path',
            ]);

            if( filled($methodRefByEcrsId) ){
                if ($request->imageType === "before"){
                    return  $arrFilteredDocumentName = explode(' | ' ,$methodRefByEcrsId[0]->filtered_document_name_before);
                    $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
                    $filePathWithEcrsId = $methodRefByEcrsId[0]->file_path."/".$methodsId."/". "$request->imageType"."/".$selectedFilteredDocumentName;
                    $filePath = "app/public/".$filePathWithEcrsId."";
                }
                if ($request->imageType === "after"){
                   $arrFilteredDocumentName = explode(' | ' ,$methodRefByEcrsId[0]->filtered_document_name_after);
                    $selectedFilteredDocumentName =  $arrFilteredDocumentName[$request->index];
                    $filePathWithEcrsId = $methodRefByEcrsId[0]->file_path."/".$methodsId."/". "$request->imageType"."/".$selectedFilteredDocumentName;
                    return  $filePath = "app/public/".$filePathWithEcrsId."";
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
    public function getMethodRefById(Request $request){
        try {
            $conditions = [
                'id' => $request->methodsId,
            ];
            $data = $this->resourceInterface->readCustomEloquent(Method::class,[],[],$conditions);
            $methodRefByEcrsId = $data
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
            if ( filled($methodRefByEcrsId) ){
                $arrMethodRefResponse = [
                    'originalFilenameBefore'=> explode(' | ',$methodRefByEcrsId[0]->original_filename_before),
                    'originalFilenameAfter'=> explode(' | ',$methodRefByEcrsId[0]->original_filename_after),
                    'methodsId'=> encrypt($methodRefByEcrsId[0]->id),
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
