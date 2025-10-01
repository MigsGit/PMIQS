<?php
namespace App\Services;
use Helpers;

use Throwable;
use Illuminate\Support\Facades\DB;
use App\Interfaces\ResourceInterface;


class ResourceService implements ResourceInterface
{
    public function readWithRelationsConditionsActive($model,$data=null,$relations=null,$conditions=null){
        try {
            $query = $model::query();
            if($data != null){
                foreach ($data as $key => $value) {
                    $query->select($value);
                    // $query->select('column1','column2');
                }
            }

            if($relations != null){
                $query->with($relations);
                // $query->with('approver_ordinates','approver_ordinates.user');
            }

            if($conditions != null){
                foreach ($conditions as $key => $value) {
                    $query->where($key, $value);
                    // $query->where('column1'=>'1');
                    // $query->where('column2'=>'2');
                }
            }

            // return $model;
            $query->whereNull('deleted_at');
            return $query->get();
        } catch (Throwable $e) {
            throw $e;
        }
    }
    public function readWithRelationsConditions($model,$data=null,$relations=null,$conditions=null){
        try {
            $query = $model::query();
            if($data != null){
                foreach ($data as $key => $value) {
                    $query->select($value);
                    // $query->select('column1','column2');
                }
            }

            if($relations != null){
                $query->with($relations);
                // $query->with('approver_ordinates','approver_ordinates.user');
            }

            if($conditions != null){
                foreach ($conditions as $key => $value) {
                    $query->where($key, $value);
                    // $query->where('column1'=>'1');
                    // $query->where('column2'=>'2');
                }
            }

            // return $model;
            return $query->get();
        } catch (Throwable $e) {
            throw $e;
        }
    }
    public function readCustomEloquent($model,$data=null,$relations=null,$conditions=null){
        try {
            $query = $model::query();
            if($data != null){
                foreach ($data as $key => $value) {
                    $query->select($data);
                    // $query->select('column1','column2');
                }
            }

            if($relations != null){
                $query->with($relations);
                // $query->with('approver_ordinates','approver_ordinates.user');
            }

            if($conditions != null){
                foreach ($conditions as $key => $value) {
                    $query->where($key, $value);
                    // $query->where('column1'=>'1');
                    // $query->where('column2'=>'2');
                }
            }
            // return $model;
            return $query;
        } catch (Throwable $e) {
            throw $e;
        }
    }
    public function create($model,array $data){
        date_default_timezone_set('Asia/Manila');

        DB::beginTransaction();
        try {
            $data_id = $model::insertGetId($data);
            DB::commit();
            return ['is_success' => 'true','data_id' => $data_id];
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function  updateConditions($model,array $conditions,array $data){
        date_default_timezone_set('Asia/Manila');
        DB::beginTransaction();
        try {
            $query = $model::query();
            if($conditions != null){
                foreach ($conditions as $key => $value) {
                    $query->where($key, $value);
                }
            }
            $query->update($data);
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    // public function  updateConditions($model,array $conditions,array $data){
    //     date_default_timezone_set('Asia/Manila');
    //     DB::beginTransaction();
    //     try {
    //         if( isset( $data_id ) ){ //edit
    //             return 'edit';
    //             $model::where('id',$id)->update($data);
    //             $data_id = $id;
    //         }else{ //add
    //             return 'add';
    //             $insert_by_id = $model::insertGetId($data);
    //             $data_id = $insert_by_id;
    //         }
    //         DB::commit();
    //         return response()->json(['is_success' => 'true','data_id'=>$data_id]);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         throw $e;
    //     }
    // }
}
