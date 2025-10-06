<?php

namespace App\Http\Controllers;

use App\Models\PmItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;

class ProductMaterialController extends Controller
{
    public function loadProductMaterial(Request $request){
        $pmItem = PmItem::with('descriptions')->get();
        return ItemResource::collection($pmItem);
        try {
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
