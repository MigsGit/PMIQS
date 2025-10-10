<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\PmItem;

use Illuminate\Http\Request;
use App\Models\PmDescription;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Interfaces\ResourceInterface;

class ProductMaterialController extends Controller
{
    protected $resourceInterface;
    public function __construct(ResourceInterface $resourceInterface){
        $this->resourceInterface = $resourceInterface;
    }

    public function saveItem(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $itemsId = decrypt($request->itemsId);
            foreach ($request->itemNo as $key => $value) {
                $request->partcodeType[$key];
                $request->descriptionItemName[$key];
            }
            PmDescription::where('pm_items_id',$itemsId)->delete();
            collect($request->itemNo)->map(function($item, $key) use ($request, $itemsId){
               $data = [
                    'pm_items_id' => $itemsId,
                    'item_no' => $item,
                    'part_code' => $request->partcodeType[$key],
                    'description_part_name' => $request->descriptionItemName[$key],
                    'created_at' => now(),
                ];
                $this->resourceInterface->create(
                    PmDescription::class,
                    $data
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
