<?php

namespace App\Http\Controllers;

use App\Models\PmItem;
use Illuminate\Http\Request;
use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;

class ProductMaterialController extends Controller
{
    public function loadProductMaterial(Request $request){
        $pmItems = PmItem::with('descriptions')->get();
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
    }

    public function getItemsById(Request $request){
        return 'true' ;
        try {

            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
