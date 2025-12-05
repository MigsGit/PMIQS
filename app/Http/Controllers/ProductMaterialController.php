<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\PmItem;
use App\Models\PmApproval;

use Illuminate\Http\Request;
use App\Models\PmDescription;
use App\Models\PmClassification;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PmItemRequest;
use App\Http\Resources\ItemResource;
use App\Interfaces\ResourceInterface;
use Illuminate\Support\Facades\Cache;
use App\Interfaces\PdfCustomInterface;
use App\Http\Resources\PmApprovalResource;
use App\Http\Requests\PmDescriptionRequest;
use App\Http\Requests\PmClassificationRequest;

class ProductMaterialController extends Controller
{
    protected $resourceInterface;
    protected $pdfCustomInterface;
    public function __construct(ResourceInterface $resourceInterface, CommonInterface $commonInterface,PdfCustomInterface $pdfCustomInterface){
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
        $this->pdfCustomInterface = $pdfCustomInterface;
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
                DB::commit();
                return response()->json(['isSuccess' => 'true']);
            }

            if(filled($pmApprovalNext)){ //<<<<<<< HEAD
                DB::commit();
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
            }
            DB::commit();
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveClassificationQty(Request $request,PmClassificationRequest $pmClassificationRequest){
        try {
            return 'true';
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            PmClassification::whereIn('pm_descriptions_id',$request->descriptionsId)->delete();
            $classificationData =collect($request->descriptionsId)->map(function($item, $key) use ($request){
                $rowClassificationData = [
                    'pm_descriptions_id' => $item,
                    'classification' => $request->classification[$key],
                    // 'qty' => $request->qty[$key],
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

           $itemResourceCollectionCache = Cache::remember('pmItemCache', now()->addMinutes(10), function () use ($itemResourceCollection) {
                return collect($itemResourceCollection);
            });

            return DataTables::of($itemResourceCollectionCache)
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
                    'descriptions',
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
    public function viewPmItemRef(Request $request){
        // return 'true' ; //PdfCustomInterface
        try {
            $data = [
                'to' => "Yamaichi Electronics Co.",
                'attn' => "Mr. Nishi / Ms. Chiba",
                'cc' => "Ms. Ogawa / Mr. Uchida / Mr. Watanabe",
                'subject' => "Quotation for TR405-1040 & TR407-1040",
                'date' => "April 29, 2025",

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

                'terms' => [
                    "Mass Production Leadtime: 2-3 weeks upon receipt of PO with 3 months forecast.",
                    "Terms of Payment: 30 days after end of month.",
                    "Quotation valid until new quotation is issued.",
                    "Other conditions subject to supplier regulation."
                ],

                'prepared_by' => "Loida Canponpon, PPC-CN Sr. Supervisor",
                'checked_by' => "Michelle De Olino, PPC Asst. Manager",
                'noted_by' => "Ms. Lyn S., PPC Asst. VP",
            ];
            $generatePdfProductMaterial= $this->pdfCustomInterface->generatePdfProductMaterial($data);
            return response($generatePdfProductMaterial)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="quotation.pdf"');
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function savePdfEmailFormat(Request $request){
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
}
