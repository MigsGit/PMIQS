<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\PmItem;

use Illuminate\Http\Request;
use App\Models\PmDescription;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\PmItemRequest;
use App\Http\Resources\ItemResource;
use App\Interfaces\ResourceInterface;

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
            $pmItemRequestValidated = $pmItemRequest->validated();
            if( $request->itemsId === "null" ){ //Add
                $pmItemRequestValidated['control_no'] = $pmItemRequest->controlNo;
                $pmItemRequestValidated['category'] = $pmItemRequest->category;
                $pmItemRequestValidated['division'] = $pmItemRequest->division??'TS';
                $pmItemRequestValidated['remarks'] = $pmItemRequest->remarks;
                $pmItemRequestValidated['created_by'] = session('rapidx_user_id');
                // return $pmItemRequestValidated;
                $pmItemsId = $this->resourceInterface->create(PmItem::class,$pmItemRequestValidated);
                $itemsId = $pmItemsId['dataId'];
            }else{ //Edit
                $itemsId = decrypt($request->itemsId);
            }
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
            DB::commit();
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
                $result .= "<li> <button items-id='".encrypt($row['id'])."' item-status='".$row['status']."' class='dropdown-item' id='btnGetMaterialById'> <i class='fa-solid fa-pen-to-square'></i> Edit</button> </li>";
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
                ['descriptions'],
                ['pm_items_id' => decrypt($request->itemsId)],
            );
            $pmItems =  $data->get();
            $itemCollection = ItemResource::collection($pmItems)->resolve();
            $description = collect($itemCollection[0]['descriptions'])->groupBy('itemNo');
            return response()->json([
                'isSuccess' => 'true',
                'itemCollection' => $itemCollection,
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
