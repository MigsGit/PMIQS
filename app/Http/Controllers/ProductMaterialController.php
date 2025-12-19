<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\PmItem;
use App\Models\PmApproval;
use App\Services\PdfService;

use Illuminate\Http\Request;
use App\Models\PmDescription;
use App\Models\PmClassification;
use App\Interfaces\EmailInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PmItemRequest;
use App\Http\Resources\ItemResource;
use App\Interfaces\ResourceInterface;
use App\Models\PmCustomerGroupDetail;
use Illuminate\Support\Facades\Cache;
use App\Interfaces\PdfCustomInterface;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PmApprovalResource;
use App\Http\Requests\PmDescriptionRequest;
use App\Http\Requests\PmClassificationRequest;
use App\Http\Requests\PmCustomerGroupDetailRequest;
use App\Http\Resources\PmCustomerGroupDetailResource;

class ProductMaterialController extends Controller
{
    protected $resourceInterface;
    protected $pdfCustomInterface;
    protected $commonInterface;
    protected $emailInterface;
    public function __construct(ResourceInterface $resourceInterface, CommonInterface $commonInterface,PdfCustomInterface $pdfCustomInterface,EmailInterface $emailInterface){
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
        $this->pdfCustomInterface = $pdfCustomInterface;
        $this->emailInterface = $emailInterface;

    }
    public function generateControlNumber(Request $request){
        try {
            return $generateControlNumber = $this->commonInterface->generateControlNumber($request->division);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function saveItem(Request $request, PmItemRequest $pmItemRequest,PmDescriptionRequest $pmDescriptionRequest){
        try {
            $generateControlNumber = $this->commonInterface->generateControlNumber($pmItemRequest->division);
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $pmItemRequestValidated = [];
            $pmItemRequestValidated['category'] = $pmItemRequest->category;
            $pmItemRequestValidated['division'] = $pmItemRequest->division;
            $pmItemRequestValidated['remarks'] = $pmItemRequest->remarks;
            if( blank($request->itemsId) ){ //Add
                // return 'add';
                $pmItemRequestValidated['created_by'] = session('rapidx_user_id');
                $pmItemRequestValidated['control_no'] = $generateControlNumber['currentCtrlNo'];
                $pmItemsId = $this->resourceInterface->create(PmItem::class,$pmItemRequestValidated);
               $itemsId = $pmItemsId['dataId'];
            }else{ //Edit
                // return 'Edit';
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

            PmApproval::where('pm_items_id',$itemsId)->delete();
            $pmApprovalStatus = [
                'PREPBY' => $request->preparedBy,
                'CHCKBY' => $request->checkedBy,
                'NOTEDBY' => $request->notedBy,
                'APPBY1' => $request->approvedByOne,
                'APPBY2' => $request->approvedByTwo,
            ];
           $pmApprovalRequest= collect($pmApprovalStatus)->flatMap(function($users,$approvalStatus) use ($pmItemRequest,$itemsId){
                return collect($users)->map(function ($userId) use ($approvalStatus, $pmItemRequest,$itemsId): array {
                    // $approval_status as a array name
                    //return array users id, defined type by use keyword,
                    return [
                        'pm_items_id' => $itemsId,
                        'rapidx_user_id' =>  $userId == 0 ? NULL : $userId,
                        'approval_status' => $approvalStatus,
                        'remarks' => $pmItemRequest->remarks,
                        'created_at' => now(),
                    ];
                });
            })->toArray();
            PmApproval::insert($pmApprovalRequest);
            PmApproval::where('approval_status', 'PREPBY')
            ->where('pm_items_id', $itemsId)
            ->first()
            ->update(['status'=>'PEN']);

            Cache::forget('pmItemCache');
            DB::commit();
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveForApproval(Request $request){
        try {
            DB::beginTransaction();
            date_default_timezone_set('Asia/Manila');
            $selectedItemsId = decrypt($request->selectedItemsId);
            $status = $request->approverDecision;

            //Get Current Ecr Approval is equal to Current Session
            $pmApprovalCurrent = PmApproval::where('pm_items_id',$selectedItemsId)
            ->where('rapidx_user_id',session('rapidx_user_id'))
            ->where('status','PEN')
            ->first();
            if(blank($pmApprovalCurrent)){
                return response()->json(['isSuccess' => 'false','msg' => 'You are not the current approver !'],500);
            }
            //Get the ECR Approval Status & Id, Update the Approval Status as PENDING
            $pmApprovalNext = PmApproval::where('pm_items_id',$selectedItemsId)
            ->whereNotNull('rapidx_user_id')
            ->where('status','-')
            ->first(['pm_approvals_id','approval_status','rapidx_user_id']);

            if ( $status === "DIS" ){  //DISAPPROVED
                $pmApprovalCurrent->update([
                    'status' => $status,
                    'remarks' => $request->approverRemarks,
                ]);
                $this->resourceInterface->updateConditions(pmItem::class,[
                    'pm_items_id' => $selectedItemsId
                ],[
                    'status' => 'DIS',
                    'approval_status' => 'DIS',
                ]);
                //Send DISAPPROVED Email to Requestor
                $currentSession = $this->emailInterface->getEmailByRapidxUserId( session('rapidx_user_id'));
                $pmApprovalEmailMsg = $this->emailInterface->pmApprovalEmailMsg($selectedItemsId);
                $msg = $pmApprovalEmailMsg['msg'];
                $to = $pmApprovalEmailMsg['itemResource']['rapidx_user_created_by']['email'] ?? '';
                $from =$currentSession['email'] ?? '';
                $from_name = $currentSession['fullName'];
                $subject = "DISAPPROVED: PMI Quotation Request";

                //Reset EcrRequirement
                $emailData = [

                     // "to" =>$to,
                    "to" =>'cdcasuyon@pricon.ph',
                    "cc" =>"",
                    "bcc" =>"mclegaspi@pricon.ph",
                    // "from" => $from,
                    "from" => "cbretusto@pricon.ph",
                    "from_name" =>$from_name ?? "PMI Quotation System",
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
                // $this->emailInterface->sendEmail($emailData);
                // DB::commit();
                return response()->json(['isSuccess' => 'true']);
            }
            if(filled($pmApprovalNext)){ //Update APPROVED and Next PENDING Approval
                $pmApprovalCurrent->update([
                    'status' => $status,
                    'remarks' => $request->approverRemarks,
                ]);

                $pmApprovalNext->update([
                    'status' => 'PEN',
                ]);

                $this->resourceInterface->updateConditions(pmItem::class,[
                    'pm_items_id' => $selectedItemsId
                ],[
                    'status' => 'FORAPP',
                    'approval_status' => $pmApprovalNext->approval_status,
                ]);
                // $to = $ecrCurrentApproval['email'] ?? '';

                $pmApprovalEmailMsg = $this->emailInterface->pmApprovalEmailMsg($selectedItemsId);
                $msg = $pmApprovalEmailMsg['msg'];
                $from = 'issinfoservice@pricon.ph';
                $subject = "FOR APPROVAL: PMI Quotation System";
                $from_name = "PMI Quotation System (PMIQS)";
                //Send For Approval Email to Next Approver
            }
            if(blank($pmApprovalNext)){  //Update APPROVED status of Item
                $this->resourceInterface->updateConditions(pmItem::class,[
                    'pm_items_id' => $selectedItemsId
                ],[
                    'status' => 'OK',
                    'approval_status' => 'OK',
                ]);
                $pmApprovalCurrent->update([
                    'status' => $status,
                    'remarks' => $request->approverRemarks,
                ]);

                $pmApprovalEmailMsg = $this->emailInterface->pmApprovalEmailMsg($selectedItemsId);
                //Send APPROVED Email to Requestor
                $msg    = $pmApprovalEmailMsg['msg'];
                $to     = $pmApprovalEmailMsg['itemResource']['rapidx_user_created_by']['email'] ?? '';
                $from   = 'issinfoservice@pricon.ph';
                $subject = "APPROVED: PMI Quotation Request";
                $from_name = "PMI Quotation System (PMIQS)";
            }
            $emailData = [
                // "to" =>$to,
                "to" =>'cdcasuyon@pricon.ph',
                "cc" =>"",
                "bcc" =>"mclegaspi@pricon.ph",
                // "from" => $from,
                "from" => "cbretusto@pricon.ph",
                "from_name" =>$from_name ?? "PMI Quotation System (PMIQS)",
                "subject" =>$subject,
                "message" =>  $msg,
                "attachment_filename" => "",
                "attachment" => "",
                "send_date_time" => now(),
                "date_time_sent" => "",
                "date_created" => now(),
                "created_by" => session('rapidx_username'),
                "system_name" => "rapidx_PMIQS",
            ];
            // return $this->emailInterface->sendEmail($emailData);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveClassificationQty(Request $request,PmClassificationRequest $pmClassificationRequest){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            PmClassification::whereIn('pm_descriptions_id',$request->descriptionsId)->delete();
            $classificationData =collect($request->descriptionsId)->map(function($item, $key) use ($request){
                $rowClassificationData = [
                    'pm_descriptions_id' => $item,
                    'classification' => $request->classification[$key],
                    'qty' => $request->qty[$key],
                    'uom' => $request->uom[$key],
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
            return response()->json(['isSuccess' => 'true']);
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
            PmDescription::where('item_no',$request->selectedItemNo)->update([
                'deleted_at' => now()
            ]);
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
            return response()->json(['isSuccess' => 'true']);
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
                [
                    'descriptions',
                    'pm_approval_pending.rapidx_user_rapidx_user_id'
                ],
                [],
            );

            $pmItems =  $data->get();
            $itemResource = ItemResource::collection($pmItems)->resolve();
            $itemResourceCollection = json_decode(json_encode($itemResource), true);

        //    $itemResourceCollectionCache = Cache::remember('pmItemCache', now()->addMinutes(10), function () use ($itemResourceCollection) {
        //         return collect($itemResourceCollection);
        //     });

            return DataTables::of($itemResourceCollection)
            ->addColumn('getActions',function ($row){
                $result = "";
                // $result .= '<center>';
                $result .= '<div class="btn-group dropstart mt-4">';
                $result .= "<button  type='button' class='btn btn-secondary dropdown-toggle btn-sm' data-bs-toggle='dropdown' aria-expanded='false'>";
                $result .= '    Action';
                $result .= '</button>';
                $result .= '<ul class="dropdown-menu">';
                $result .= "<li> <button items-id='".encrypt($row['id'])."' pm-item-status='".$row['status']."' item-status='".$row['status']."' class='dropdown-item' id='btnGetMaterialById'> <i class='fa-solid fa-pen-to-square'></i> Edit Items</button> </li>";
                $result .= "<li> <button items-id='".encrypt($row['id'])."' pm-item-status='".$row['status']."' item-status='".$row['status']."' class='dropdown-item' id='btnGetClassificationQtyByItemsId'> <i class='fa-solid fa-pen-to-square'></i> Edit Qty</button> </li>";
                $result .= "<li> <button items-id='".encrypt($row['id'])."' pm-item-status='".$row['status']."' item-status='".$row['status']."' class='dropdown-item' id='btnSendProductMaterial'> <i class='fa-solid fa-paper-plane'></i> Send Disposition</button> </li>";
                $result .= '</ul>';
                $result .= '</div>';
                // $result .= '</center>';
                return $result;
            })->addColumn('getStatus',function ($row): string{
                $currentApprover = $row['pm_approval_pending']['rapidx_user_rapidx_user_id']['name'] ?? '';
                $status = $row['status'];
                $getStatus = $this->commonInterface->getPmItemStatus($status);
                $getApprovalStatus = $this->commonInterface->getPmApprovalStatus($row['approvalStatus']);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
                $result .= '<br>';
                if($status === 'OK'){
                   return  $result .= '';
                }
                if($status != 'DIS'){
                    $result .= '<span class="badge rounded-pill bg-danger"> '.$getApprovalStatus['approvalStatus'].' '.$currentApprover.' </span>';
                }

                $result .= '</center>';
                $result .= '</br>';
                return $result;
            })->addColumn('getAttachment',function ($row){
                $result = '';
                $result .= '<center>';
                $result .= "<a class='btn btn-outline-danger btn-sm mr-1 mt-3 btn-get-ecr-id' items-id='".encrypt($row['id'])."' id='btnViewPmItemRef'> <i class='fa-solid fa-file-pdf'></i> Download</a>";
                $result .= '</center>';
                return $result;
            })->rawColumns([
                'getActions',
                'getStatus',
                'getAttachment',
            ])->make(true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function loadPmApprovalSummary(Request $request){
        $itemsId = $request->itemsId;
        try {
            if(filled($itemsId)){
                $itemsId = decrypt($request->itemsId);
            }else{
                $itemsId = 0;
            }
            $pmApproval = $this->resourceInterface->readCustomEloquent(
                PmApproval::class,
                [],
                ['rapidx_user_rapidx_user_id'],
                ['pm_items_id' => $itemsId ]
            );
            $pmApprovalData = $pmApproval->get();
            $pmApprovalResource = pmApprovalResource::collection($pmApprovalData)->resolve();
            $pmApprovalResourceCollection = json_decode(json_encode($pmApprovalResource), true); //updated_at  remarks
            return DataTables($pmApprovalResourceCollection)
            ->addColumn('getCount',function ($row) use(&$ctr){
                $ctr++;
                $result = '';
                $result .= $ctr;
                $result .= '</br>';
                return $result;
            })
            ->addColumn('getApproverName',function ($row){
                $result = '';
                $result .= $row['rapidx_user_rapidx_user_id']['name'];
                $result .= '</br>';
                return $result;
            })
            ->addColumn('getRole',function ($row){
                $getApprovalStatus = $this->commonInterface->getPmApprovalStatus($row['approvalStatus']);
                $result = '';
                $result .= '<center>';
                $result .= '<span class="badge rounded-pill bg-primary"> '.$getApprovalStatus['approvalStatus'].' </span>';
                $result .= '<center>';
                $result .= '</br>';
                return $result;
            })
            ->addColumn('getRemarks',function ($row){
                $result = "";
                $result .= $row->remarks ?? "N/A";
                return $result;
            })
            ->addColumn('getStatus',function ($row){
                switch ($row['status']) {
                    case 'PEN':
                        $status = 'PENDING';
                        $bgColor = 'badge rounded-pill bg-warning';
                        break;
                    case 'APP':
                        $status = 'APPROVED - '.$row['updatedAt'];
                        // $status = 'APPROVED";
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
            ->rawColumns(['getCount','getStatus','getApproverName','getRemarks','getRole'])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getItemsById(Request $request){
        try {
            $itemsId = decrypt($request->itemsId);
            $data = $this->resourceInterface->readCustomEloquent(
                PmItem::class,
                [],
                [
                    'descriptions',
                    'rapidx_user_created_by',
                    'descriptions.classifications',
                    'pm_approvals.rapidx_user_rapidx_user_id',
                ],
                ['pm_items_id' => $itemsId],
            );
            $pmItems =  $data->get();
            $itemCollection = ItemResource::collection($pmItems)->resolve();
            $description = collect($itemCollection[0]['descriptions'])->groupBy('itemNo');
            $ecrApprovalCurrentCount = $this->resourceInterface->readCustomEloquent(
                PmApproval::class,
                [],
                [
                    'rapidx_user_created_by',
                    'descriptions.classifications',
                    'pm_approvals.rapidx_user_rapidx_user_id',
                ],
                [
                    'pm_items_id' =>$itemsId,
                    'rapidx_user_id' => session('rapidx_user_id'),
                    'status' => 'PEN',
                ],
            );

            $status = $itemCollection[0]['status'];
            $getStatus = $this->commonInterface->getPmItemStatus($status);
            $status = '';
            $status .= '<center>';
            $status .= '<span class="'.$getStatus['bgStatus'].'"> '.$getStatus['status'].' </span>';
            $status .= '</center>';
            $status .= '</br>';
            // return  PmApprovalResource::collection($pmItems)->resolve();
            return response()->json([
                'isSuccess' => 'true',
                'status' => $status,
                'itemCollection' => $itemCollection,
                'createdBy' => $itemCollection[0]['rapidx_user_created_by']['name'],
                'pmApprovals' => $itemCollection[0]['pm_approvals'],
                'description' => $description,
                'descriptionCount' => $description->count(),
                'ecrApprovalCurrentCount' => $ecrApprovalCurrentCount->count(),
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getDescriptionByItemsId(Request $request){
        return 'true' ;

        try {
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function viewPmItemRefv1(Request $request){
        try {
            $itemsId= decrypt($request->itemsId);
            $data = $this->resourceInterface->readCustomEloquent(
                PmItem::class,
                [],
                [
                    'descriptions.classifications',
                    'rapidx_user_created_by',
                    'pm_approvals.rapidx_user_rapidx_user_id',
                    'pm_approvals.pm_user',
                    'pm_customer_group_detail.dropdown_customer_group',
                ],
                ['pm_items_id' => $itemsId],
            );
            $pmItems =  $data->get();
            $itemCollection = ItemResource::collection($pmItems)->resolve();
            $descriptions = collect($itemCollection[0]['descriptions'])->groupBy('itemNo');
            // department_position
            $controlNo = $itemCollection[0]['controlNo'];
            // $descriptions = $itemCollection[0]['descriptions'];
            $pmApprovalsData = $itemCollection[0]['pm_approvals'];

            $preparedBy = $pmApprovalsData[0]['rapidx_user_rapidx_user_id']['name']?? '';
            $checkedBy = $pmApprovalsData[1]['rapidx_user_rapidx_user_id']['name']?? '';
            $notedBy = $pmApprovalsData[2]['rapidx_user_rapidx_user_id']['name']?? '';
            $appovedBy1 = $pmApprovalsData[3]['rapidx_user_rapidx_user_id']['name']?? '';
            $appovedBy2 = $pmApprovalsData[4]['rapidx_user_rapidx_user_id']['name']?? '';

            $preparedByPosition = $pmApprovalsData[0]['pm_user']['department_position'] ?? '';
            $checkedByPosition = $pmApprovalsData[1]['pm_user']['department_position'] ?? '';
            $notedByPosition = $pmApprovalsData[2]['pm_user']['department_position'] ?? '';
            $appovedBy1Position = $pmApprovalsData[3]['pm_user']['department_position'] ?? '';
            $appovedBy2Position = $pmApprovalsData[4]['pm_user']['department_position'] ?? '';

            $pmCustomerGroupDetailData = $itemCollection[0]['rapidx_user_created_by']['name'];
            $pmCustomerGroupDetailData = $itemCollection[0]['pm_customer_group_detail'][0];
            $customerName = $pmCustomerGroupDetailData['dropdown_customer_group'][0]['customer'];
            $attentionName = $pmCustomerGroupDetailData['attention_name'];
            $ccName = $pmCustomerGroupDetailData['cc_name'];
            $subject = $pmCustomerGroupDetailData['subject'];
            $additionalMessage = $pmCustomerGroupDetailData['additional_message'];
            $termsCondition = $pmCustomerGroupDetailData['terms_condition'];
            //'true' ; //PdfCustomInterface
            $data = [
                'to' => "Yamaichi Electronics Co.",
                'attn' => $attentionName,
                'cc' => $ccName,
                'subject' => $subject,
                'date' => "April 29, 2025",
                'message' => [
                  $additionalMessage
                ],
                'descriptions' => $descriptions,
                'item1' => [
                    [
                        'description' => "TR405-1040 Base & Cover Tray",
                        'specs' => "360 x 175 x 40.9",
                        'material' => "APET 1.0mm",
                        'price' => "$1.2669",
                    ],
                ],
                'item2' => [
                    [
                        'description' => "TR407-1040 Base & Cover Tray",
                        'specs' => "360 x 175 x 40.9",
                        'material' => "APET 1.0mm",
                        'price' => "$1.2669",
                    ],
                ],

                // 'terms' => [
                //     "Mass Production Leadtime: 2-3 weeks upon receipt of PO with 3 months forecast.",
                //     "Terms of Payment: 30 days after end of month.",
                //     "Quotation valid until new quotation is issued.",
                //     "Other conditions subject to supplier regulation."
                // ],
                'terms' => [
                    $termsCondition
                ],

                'prepared_by' => $preparedBy,
                'checked_by' => $checkedBy,
                'noted_by' => $notedBy,
                'approved_by1' => $appovedBy1,
                'approved_by2' => $appovedBy2,

                'prepared_by_position' => $preparedByPosition,
                'checked_by_position' => $checkedByPosition,
                'noted_by_position' => $notedByPosition,
                'appoved_by1_position' => $appovedBy1Position,
                'appoved_by2_position' => $appovedBy2Position,
            ];
            // return $data;
            $generatePdfProductMaterial= $this->pdfCustomInterface->generatePdfProductMaterial($data);
            return response($generatePdfProductMaterial)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="quotation.pdf"');
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function viewPmItemRef(Request $request){
        try {
            $itemsId= decrypt($request->itemsId);
            $data = $this->resourceInterface->readCustomEloquent(
                PmItem::class,
                [],
                [
                    'descriptions.classifications',
                    'rapidx_user_created_by',
                    'pm_approvals.rapidx_user_rapidx_user_id',
                    'pm_approvals.pm_user',
                    'pm_customer_group_detail.dropdown_customer_group',
                ],
                ['pm_items_id' => $itemsId],
            );
            $pmItems =  $data->get();
            $itemCollection = ItemResource::collection($pmItems)->resolve();
            $category = $itemCollection[0]['category'];
            $descriptions = collect($itemCollection[0]['descriptions']);
            $controlNo = $itemCollection[0]['controlNo'];
            // $descriptions = $itemCollection[0]['descriptions'];
            $pmApprovalsData = $itemCollection[0]['pm_approvals'];

            $preparedBy = $pmApprovalsData[0]['rapidx_user_rapidx_user_id']['name']?? '';
            $checkedBy = $pmApprovalsData[1]['rapidx_user_rapidx_user_id']['name']?? '';
            $notedBy = $pmApprovalsData[2]['rapidx_user_rapidx_user_id']['name']?? '';
            $appovedBy1 = $pmApprovalsData[3]['rapidx_user_rapidx_user_id']['name']?? '';
            $appovedBy2 = $pmApprovalsData[4]['rapidx_user_rapidx_user_id']['name']?? '';

            $preparedByPosition = $pmApprovalsData[0]['pm_user']['department_position'] ?? '';
            $checkedByPosition = $pmApprovalsData[1]['pm_user']['department_position'] ?? '';
            $notedByPosition = $pmApprovalsData[2]['pm_user']['department_position'] ?? '';
            $appovedBy1Position = $pmApprovalsData[3]['pm_user']['department_position'] ?? '';
            $appovedBy2Position = $pmApprovalsData[4]['pm_user']['department_position'] ?? '';

            $pmCustomerGroupDetailData = $itemCollection[0]['rapidx_user_created_by']['name'];
            $pmCustomerGroupDetailData = $itemCollection[0]['pm_customer_group_detail'][0];
            $customerName = $pmCustomerGroupDetailData['dropdown_customer_group'][0]['customer'];
            $attentionName = $pmCustomerGroupDetailData['attention_name'];
            $ccName = $pmCustomerGroupDetailData['cc_name'];
            $subject = $pmCustomerGroupDetailData['subject'];
            $additionalMessage = $pmCustomerGroupDetailData['additional_message'];
            $termsCondition = $pmCustomerGroupDetailData['terms_condition'];

           $arrDescriptions = collect($descriptions)->map(function ($item) {
                return [
                    "itemsId"     => [$item['itemsId']],
                    "itemNo"      => [$item['itemNo']],
                    "part_code"    => [$item['partCode']],
                    "description" => [$item['descriptionPartName']],
                    "length"      => [$item['matSpecsLength']],
                    "width"       => [$item['matSpecsWidth']],
                    "height"      => [$item['matSpecsHeight']],
                    "material"    => [$item['matRawType']],
                    "thickness"   => [$item['matRawThickness']],
                    "material_w"  => [$item['matRawWidth']],
                    // Transform nested prices → [qty, "pcs", "$ 0.00"]
                    "prices" => collect($item['classifications'])->map(function ($p) {
                        return [
                            $p['classification'],
                            $p['qty'],
                            "pcs",
                            "$" . number_format($p['unitPrice'], 4)
                        ];
                    })->values()->toArray(),
                ];
            })->values()->groupBy('itemNo')->toArray();

            $data = [
                'to' => "Yamaichi Electronics Co.",
                "category"    => $category,
                'attn' => $attentionName,
                'cc' => $ccName,
                'subject' => $subject,
                'date' => "April 29, 2025",
                'message' => [
                  $additionalMessage
                ],
                'descriptions' => $arrDescriptions,
                // 'terms' => [
                //     "Mass Production Leadtime: 2-3 weeks upon receipt of PO with 3 months forecast.",
                //     "Terms of Payment: 30 days after end of month.",
                //     "Quotation valid until new quotation is issued.",
                //     "Other conditions subject to supplier regulation."
                // ],
                'terms' => [
                    $termsCondition
                ],

                'prepared_by' => $preparedBy,
                'checked_by' => $checkedBy,
                'noted_by' => $notedBy,
                'approved_by1' => $appovedBy1,
                'approved_by2' => $appovedBy2,

                'prepared_by_position' => $preparedByPosition,
                'checked_by_position' => $checkedByPosition,
                'noted_by_position' => $notedByPosition,
                'appoved_by1_position' => $appovedBy1Position,
                'appoved_by2_position' => $appovedBy2Position,
            ];
            // return $data;
            $generatePdfProductMaterial= $this->pdfCustomInterface->generatePdfProductMaterial($data);
            return response($generatePdfProductMaterial)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="quotation.pdf"');
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function savePdfEmailFormat(Request $request,PmCustomerGroupDetailRequest $pmCustomerGroupDetailRequest){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $pmCustomerGroupDetailRequestValidated = [];
            //pm_customer_group_details_id
            //dd_customer_groups_id
            $pmCustomerGroupDetailRequestValidated["pm_items_id"] = decrypt($request->selectedItemsId);
            $pmCustomerGroupDetailRequestValidated["dd_customer_groups_id"] = $request->pdfToGroup;
            $pmCustomerGroupDetailRequestValidated["attention_name"] = $request->pdfAttnName;
            $pmCustomerGroupDetailRequestValidated["cc_name"] = $request->pdfCcName;
            $pmCustomerGroupDetailRequestValidated["subject"] = $request->pdfSubject;
            $pmCustomerGroupDetailRequestValidated["additional_message"] = $request->pdfAdditionalMsg;
            $pmCustomerGroupDetailRequestValidated["terms_condition"] = $request->pdfTermsCondition;
            $pmCustomerGroupDetailRequestValidated["created_at"] = now();
            $this->resourceInterface->create(PmCustomerGroupDetail::class,$pmCustomerGroupDetailRequestValidated);
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function sendDisposition(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            $itemsId =  decrypt($request->selectedItemsId);
            $msg =  $this->emailInterface->ecrEmailMsg($request->pdfAdditionalMsg);
            $subject =  $request->pdfSubject;
            $pmAttachment =  $request->pmAttachment;
            $pdfAttn =  $request->pdfAttn;
             $pdfCc =  $request->pdfCc;
            DB::beginTransaction();
            $path = "public/product_material/$itemsId/";
            if($request->hasfile('pmAttachment')){

                $arrUploadFile = $this->commonInterface->uploadFileEcrRequirement($pmAttachment,$path);
                $impOriginalFilename = implode(' | ',$arrUploadFile['arr_original_filename']);
                $impFilteredDocumentName = implode(' | ',$arrUploadFile['arr_filtered_document_name']);
                return;
                // return $url = Storage::url('PQS/filename.ext');
                $contents = Storage::get($path.'/1_wbs_double_data.pdf'); //
                // Check if the file exists before attempting to read it
                if (file_exists($contents)) {
                    return $fileContents = file_get_contents($contents);
                    // Use $fileContents...
                } else {
                    // Handle the case where the file is not found
                }
            //  return   $contents = Storage::disk('public')->get($path);

                $emailData = [
                    // "to" =>$to,
                    "to" =>'cdcasuyon@pricon.ph',
                    "cc" =>"",
                    "bcc" =>"mclegaspi@pricon.ph",
                    // "from" => $from,
                    "from" => "cbretusto@pricon.ph",
                    "from_name" =>$from_name ?? "PMI Quotation System (PMIQS)",
                    "subject" =>$subject,
                    "message" =>  $msg,
                    "attachment_filename" => "",
                    "attachment" => "",
                    "send_date_time" => now(),
                    "date_time_sent" => "",
                    "date_created" => now(),
                    "created_by" => session('rapidx_username'),
                    "system_name" => "rapidx_PMIQS",
                ];
            //    return $this->emailInterface->sendEmail($emailData);
                DB::commit();
            }

            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
