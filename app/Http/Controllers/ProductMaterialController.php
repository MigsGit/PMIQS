<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\PmItem;

use Illuminate\Http\Request;
use App\Models\PmDescription;
use App\Models\PmClassification;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PmItemRequest;
use App\Http\Resources\ItemResource;
use App\Interfaces\ResourceInterface;
use App\Http\Requests\PmDescriptionRequest;

class ProductMaterialController extends Controller
{
    protected $resourceInterface;
    public function __construct(ResourceInterface $resourceInterface, CommonInterface $commonInterface){
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
    }
    public function generateControlNumber(Request $request){
        try {
            return $generateControlNumber = $this->commonInterface->generateControlNumber($request->division);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function saveItem(Request $request, PmItemRequest $pmItemRequest){
        try {

            $generateControlNumber = $this->commonInterface->generateControlNumber($pmItemRequest->division);
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $pmItemRequestValidated = [];
            $pmItemRequestValidated['control_no'] = $pmItemRequest->controlNo;
            $pmItemRequestValidated['category'] = $pmItemRequest->category;
            $pmItemRequestValidated['division'] = $pmItemRequest->division;
            $pmItemRequestValidated['remarks'] = $pmItemRequest->remarks;
            if( $request->itemsId === "null" ){ //Add
                $pmItemRequestValidated['created_by'] = session('rapidx_user_id');
                $pmItemsId = $this->resourceInterface->create(PmItem::class,$pmItemRequestValidated);
                $itemsId = $pmItemsId['dataId'];
            }else{ //Edit
                $itemsId = decrypt($request->itemsId);
                $pmItemRequestValidated;
                $pmItemRequestValidated['updated_by'] = session('rapidx_user_id');
                $this->resourceInterface->updateConditions(
                    PmItem::class,
                    ['pm_items_id' => $itemsId],
                    $pmItemRequestValidated
                );
            }
            if( $request->itemsId === "null" ){ //Add
                //Save the Items & Descriptions
                PmDescription::where('pm_items_id',$itemsId)->delete();
                $productData =collect($request->itemNo)->map(function($item, $key) use ($pmItemRequest, $itemsId){
                    $productData = [
                        'pm_items_id' => $itemsId,
                        'item_no' => $item,
                        'part_code' => $pmItemRequest->partcodeType[$key],
                        'description_part_name' => $pmItemRequest->descriptionItemName[$key],
                        'created_at' => now(),
                    ];
                    if($pmItemRequest->category == 'RM'){
                        $rawMatData = [
                            'mat_specs_length' => $pmItemRequest->matSpecsLength[$key],
                            'mat_specs_width' => $pmItemRequest->matSpecsWidth[$key],
                            'mat_specs_height' => $pmItemRequest->matSpecsHeight[$key],
                            'mat_raw_type' => $pmItemRequest->matRawType[$key],
                            'mat_raw_thickness' => $pmItemRequest->matRawThickness[$key],
                            'mat_raw_width' => $pmItemRequest->matRawWidth[$key],
                        ];
                    }
                    $this->resourceInterface->create
                    (
                        PmDescription::class,
                        array_merge($productData,$rawMatData ?? [])
                    );
                });
            }
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveClassificationQty(Request $request){

        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $request->descriptionsId;
            PmClassification::whereIn('pm_descriptions_id',$request->descriptionsId)->delete();
            $classificationData =collect($request->descriptionsId)->map(function($item, $key) use ($request){
                $rowClassificationData = [
                    'pm_descriptions_id' => $item,
                    'classification' => $request->classification[$key],
                    // 'qty' => $request->qty[$key],
                    'qty' => $request->qty[$key],
                    'uom' => 1,
                    'unit_price' => $request->unitPrice[$key],
                    'remarks' => $request->remarks[$key],
                ];
                $this->resourceInterface->create
                (
                    PmClassification::class,
                    $rowClassificationData,
                );
            });
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveItemNo(Request $request,PmDescriptionRequest $pmDescriptionRequest){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $request->selectedItemNo;
            $itemsId = decrypt($request->itemsId);
            // Delete the Description by Item No & ave the Descriptions
            PmDescription::where('item_no',$request->selectedItemNo)->delete();
            $productData =collect($request->itemNo)->map(function($item, $key) use ($pmDescriptionRequest, $itemsId){
               $productData = [
                    'pm_items_id' => $itemsId,
                    'item_no' => $item,
                    'part_code' => $pmDescriptionRequest->partcodeType[$key],
                    'description_part_name' => $pmDescriptionRequest->descriptionItemName[$key],
                    'created_at' => now(),
                ];
                if($pmDescriptionRequest->category == 'RM'){
                    $rawMatData = [
                        'mat_specs_length' => $pmDescriptionRequest->matSpecsLength[$key],
                        'mat_specs_width' => $pmDescriptionRequest->matSpecsWidth[$key],
                        'mat_specs_height' => $pmDescriptionRequest->matSpecsHeight[$key],
                        'mat_raw_type' => $pmDescriptionRequest->matRawType[$key],
                        'mat_raw_thickness' => $pmDescriptionRequest->matRawThickness[$key],
                        'mat_raw_width' => $pmDescriptionRequest->matRawWidth[$key],
                    ];
                }
                $this->resourceInterface->create
                (
                    PmDescription::class,
                    array_merge($productData,$rawMatData ?? [])
                );
            });
            DB::commit();
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
    
    public function loadProductMaterial(Request $request){
        try {
            $data = $this->resourceInterface->readCustomEloquent(
                PmItem::class,
                [],
                ['descriptions'],
                [],
            );

            $pmItems =  $data->get();
            $itemResource = ItemResource::collection($pmItems)->resolve();
            $itemResourceCollection = json_decode(json_encode($itemResource), true);

            return DataTables::of($itemResourceCollection)
            ->addColumn('getActions',function ($row){
                $result = "";
                // $result .= '<center>';
                $result .= '<div class="btn-group dropstart mt-4">';
                $result .= "<button  type='button' class='btn btn-secondary dropdown-toggle btn-sm' data-bs-toggle='dropdown' aria-expanded='false'>";
                $result .= '    Action';
                $result .= '</button>';
                $result .= '<ul class="dropdown-menu">';
                $result .= "<li> <button items-id='".encrypt($row['id'])."' item-status='".$row['status']."' class='dropdown-item' id='btnGetMaterialById'> <i class='fa-solid fa-pen-to-square'></i> Edit Items</button> </li>";
                $result .= "<li> <button items-id='".encrypt($row['id'])."' item-status='".$row['status']."' class='dropdown-item' id='btnGetClassificationQtyByItemsId'> <i class='fa-solid fa-pen-to-square'></i> Edit Qty</button> </li>";
                $result .= '</ul>';
                $result .= '</div>';
                // $result .= '</center>';
                return $result;
            })
            ->rawColumns([
                'getActions'
            ])
            ->make(true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getItemsById(Request $request){
        try {
            $data = $this->resourceInterface->readCustomEloquent(
                PmItem::class,
                [],
                [
                    'descriptions',
                    'rapidx_user_created_by'
                ],
                ['pm_items_id' => decrypt($request->itemsId)],
            );
            $pmItems =  $data->get();
            $itemCollection = ItemResource::collection($pmItems)->resolve();
            $description = collect($itemCollection[0]['descriptions'])->groupBy('itemNo');
            return response()->json([
                'isSuccess' => 'true',
                'itemCollection' => $itemCollection,
                'createdBy' => $itemCollection[0]['rapidx_user_created_by']['name'],
                'description' => $description,
                'descriptionCount' => $description->count(),
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getDescriptionByItemsId(Request $request){
        return 'true' ;
        try {
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
