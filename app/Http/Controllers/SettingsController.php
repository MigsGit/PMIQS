<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Classification;
use App\Models\DropdownMaster;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Models\DropdownMasterDetail;
use App\Interfaces\ResourceInterface;
use App\Models\DropdownCustomerGroup;
use App\Models\ClassificationRequirement;
use App\Http\Requests\DropdownMasterDetailRequest;
use App\Http\Resources\DropdownCustomerGroupResource;
use App\Http\Requests\ClassificationRequirementRequest;

class SettingsController extends Controller
{
    protected $resourceInterface;
    protected $commonInterface;
    public function __construct(ResourceInterface $resourceInterface,CommonInterface $commonInterface) {
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
    }
    public function saveDropdownMasterDetails (Request $request,DropdownMasterDetailRequest $dropdownMasterDetailRequest){
        date_default_timezone_set('Asia/Manila');
        try {
            $dropdownMasterDetailRequest = $dropdownMasterDetailRequest->validated();
            $dropdownMasterDetailRequest ['dropdown_masters_id'] = $request->dropdown_masters_id;
            $dropdownMasterDetailRequest ['remarks'] = $request->remarks;
            if ( isset($request->dropdown_master_details_id) ){
                $conditions =[
                    'id' => $request->dropdown_master_details_id
                ];
                $this->resourceInterface->updateConditions(DropdownMasterDetail::class,$conditions,$dropdownMasterDetailRequest);
            }else{
                $dropdownMasterDetailRequest ['created_at'] = now();
                $this->resourceInterface->create(DropdownMasterDetail::class,$dropdownMasterDetailRequest);
            }

            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function saveUserApprover(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();

            $getUser = User::where( 'rapidx_user_id' , $request->userId)->first();
            if( filled( $getUser) ){
                if($getUser->roles === "APP"){
                    User::where( 'rapidx_user_id' , $request->userId)->update([
                        'roles' => "USER",
                    ]);
                }
                if($getUser->roles != "APP"){
                    User::where( 'rapidx_user_id' , $request->userId)->update([
                        'roles' => "APP",
                    ]);
                }
                DB::commit();
                return response()->json(['isSuccess' => 'true']);
            }

            $user = User::insert([
                'rapidx_user_id' => $request->userId,
                'roles' => 'APP',
            ]);
            DB::commit();
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function saveRapidxUser(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $isModuleAccess = DB::connection('mysql_rapidx')
            ->table('user_accesses')
            ->select('*')
            ->from('user_accesses')
            ->where('user_id',$request->rapidxUser)
            ->where('module_id',50)
            ->count();
            if($isModuleAccess > 0){
                return response()->json(['is_success' => 'false', 'msg' => 'User already has access to this module.'],409);
            }
            $requestValidated = [
                'user_level_id' => 5,
                'module_id' => 50,
                'user_id' => $request->rapidxUser,
                'user_access_stat' => 1,
                'update_version' => 1,
                'created_by' => session('rapidx_user_id'),
                'last_updated_by' => session('rapidx_user_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $validUserAccess = DB::connection('mysql_rapidx')
            ->table('user_accesses')
            ->insert($requestValidated);

            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delClassificationRequirements(Request $request){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            $this->resourceInterface->updateConditions(ClassificationRequirement::class,['id' => (int)$request->classificationRequirementsId], ['deleted_at' => now()]);
            DB::commit();
            return response()->json(['isSuccess' => 'true']);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    public function getAdminAccessOpt(Request $request){ //get_admin_access_opt
        try {
            $validUserAccess = DB::connection('mysql_rapidx')->select(
                'SELECT users.*,user_accesses.module_id,departments.department_name,departments.department_group
                FROM  users
                LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                LEFT JOIN departments departments ON departments.department_id = users.department_id
                WHERE 1=1
                AND users.id = '.session('rapidx_user_id').'
                AND user_accesses.user_access_stat = 1
                AND users.user_stat = 1
                AND user_accesses.module_id = 50
                '
            );
            return response()->json([
                'departmentGroup'  => $validUserAccess[0]->department_group,
                'activeUserFullName'  => session('rapidx_name'),
                'validUserAccessCount'  => count($validUserAccess),
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getUserMaster(Request $request){
        try {
            // DB::table('user')-
            $user = DB::connection('mysql_rapidx')->select(
                'SELECT users.*,user_accesses.module_id,departments.department_name
                FROM  users
                LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                LEFT JOIN departments departments ON departments.department_id = users.department_id
                WHERE 1=1
                AND user_accesses.user_access_stat = 1
                AND users.user_stat = 1
                AND user_accesses.module_id = 50
                '
            );
            return DataTables::of($user)
            ->addColumn('get_action',function($row){
                // return $row->id;
                return $btn = '<button data-id = "'.$row->id.'"  class="btn btn-outline-info btn-sm" data-toggle="modal" id="btnUserMasterDetails" type="button" title="Edit"><i class="fas fa-edit"></i></button>';
                // return $btn = '<button data-id = "'.$row->id.'" id="editResProcedure" type="button" class="btn btn-info btn-sm" title="Edit"></i>Edit</button>';
            })
            ->addColumn('get_roles',function($row){
                $user = User::where('rapidx_user_id',$row->id)->first();
                $isRoles = $user->roles ?? "";
                switch ($isRoles) {
                    case 'APP':
                        $roles = 'Approver';
                        $badgeBg = 'badge rounded-pill bg-warning';
                        break;
                    case 'USER':
                        $roles = 'User';
                        $badgeBg = 'badge rounded-pill bg-primary';
                        break;
                    default:
                        $roles = 'User';
                        $badgeBg = 'badge rounded-pill bg-primary';
                        break;
                }

                $result = '';
                $result .= '<center>';
                $result .= '<span class="'.$badgeBg.'"> '.$roles.' </span>';
                $result .= '</center>';
                return $result;
            })
            ->addColumn('get_departments',function($row){
                $result = '';
                $result .= '<span class="badge rounded-pill bg-primary"> '.$row->department_name.' </span>';
                return $result;
            })
            ->rawColumns([
                'get_action',
                'get_roles',
                'get_departments',
            ])
            ->make(true);
            // return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getDropdownMaster(Request $request){
        try {
            $conditions = [
              'category' => $request->category
            ];
            $dropdownMaster =  $this->resourceInterface->readWithRelationsConditions(DropdownMaster::class,[],[],$conditions);
            return response()->json([
                'is_success' => 'true',
                'dropdownMaster' => $dropdownMaster
            ]);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getDropdownMasterCategory(Request $request){
        try {
            // return DropdownMaster::get();
            $dropdownMaster =  $this->resourceInterface->readCustomEloquent(DropdownMaster::class,['category'],[],[]);
           $dropdownMaster->whereNull('deleted_at');
           $dropdownMaster= $dropdownMaster->groupBy('category')->get();
            return response()->json([
                'is_success' => 'true',
                'dropdownMaster' => $dropdownMaster
            ]);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function loadDropdownMasterDetails(Request $request){
        try {
            $relations = [
                'dropdown_master'
            ];
            $conditions = [
                'dropdown_masters_id' => $request->dropDownMastersId ?? ""
            ];

            $dropdownMaster =  $this->resourceInterface->readCustomEloquent(DropdownMasterDetail::class,[],$relations,$conditions);

            $dropdownMaster->whereNull('deleted_at');
            $dropdownMaster->orderBy('dropdown_masters_details');
            return DataTables::of($dropdownMaster)
            ->addColumn('get_action',function($row){
                return $btn = '<button dropdown-master-details-id = "'.$row->id.'"  class="btn btn-outline-info btn-sm" data-toggle="modal" id="btnDropdownMasterDetails" type="button" title="Edit"><i class="fas fa-edit"></i></button>';
            })
            ->addColumn('get_status',function($row){
                $result = '';
                $result .= '<span class="badge rounded-pill bg-success"> Active </span>';
                return $result;
            })
            ->rawColumns([
                'get_action',
                'get_status'
            ])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function loadClassificationRequirements(Request $request){
        try {
            $relations = [
                'classification'
            ];
            $conditions = [
                'classifications_id' => $request->dropDownMastersId ?? ""
            ];

            $dropdownMaster =  $this->resourceInterface->readCustomEloquent(ClassificationRequirement::class,[],$relations,$conditions);

           $dropdownMaster->whereNull('deleted_at');
           $dropdownMaster->orderBy('id');
            // ->get();
            return DataTables::of($dropdownMaster)
            ->addColumn('get_action',function($row){
                $btn = '';
                $btn .= '<button dropdown-master-details-id = "'.$row->id.'"  class="btn btn-outline-info btn-sm" data-toggle="modal" id="btnDropdownMasterDetails" type="button" title="Edit"><i class="fas fa-edit"></i></button>';
                $btn .= '<br>';
                $btn .= '<button dropdown-master-details-id = "'.$row->id.'"  class="btn btn-outline-danger btn-sm" data-toggle="modal" id="btnDelClassificationRequirements" type="button" title="Delete"><i class="fas fa-trash"></i></button>';
                return $btn;
            })
            ->addColumn('get_status',function($row){
                $result = '';
                $result .= '<span class="badge rounded-pill bg-success"> Active </span>';
                return $result;
            })
            ->rawColumns([
                'get_action',
                'get_status'
            ])
            ->make(true);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getDropdownMasterDetailsId(Request $request){ //
        try {
            $conditions = [
                'id' => $request->dropdownMasterDetailsId
            ];
           $dropdownMasterDetail = $this->resourceInterface->readWithRelationsConditionsActive(DropdownMasterDetail::class,[],[],$conditions);
            return response()->json([
                'isSuccess' => 'true',
                'dropdownMasterDetail' => $dropdownMasterDetail
            ]);
        } catch (Exception $e) {
            return response()->json(['is_success' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getNoModuleRapidxUserByIdOpt(Request $request){ //get all users in rapidx
        try {
            $rapidxUserById = DB::connection('mysql_rapidx')->select('SELECT users.id,users.name
                FROM  users users
                LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                WHERE 1=1
                AND user_stat = 1
                -- AND user_accesses.user != users.id
                GROUP BY users.id,users.name
                '
            );
            if(count ($rapidxUserById) > 0){
                return response()->json(['isSuccess' => 'true','rapidxUserById'=>$rapidxUserById]);
            }
            return response()->json(['isSuccess' => 'false','rapidxUserById'=>[],'msg' => 'User Not Found !',],500);
        } catch (Exception $e) {
            return response()->json(['isSuccess' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getRapidxUserByIdOpt(Request $request){  //get users with module access in rapidx
        try {
            $rapidxUserDeptGroup = $request->rapidxUserDeptGroup ?? "N/A";
            $isApprover = $request->isApprover ?? "N/A";
            $rapidxUserDeptGroupQuery = $rapidxUserDeptGroup === "N/A" ? '': 'AND departments.department_group = "'.$rapidxUserDeptGroup.'"';

            $userApprover = User::where('roles','APP')->get();
            $userApproverCollection = collect($userApprover);
            $userApproverCollectionImplode = $userApproverCollection->pluck('rapidx_user_id')->implode(', ');
            $rapidxUserIdGroupQuery = $isApprover === "N/A" ? '': 'AND users.id IN('.$userApproverCollectionImplode.')';
            if($isApprover === "true"){
                $rapidxUserById = DB::connection('mysql_rapidx')->select('SELECT users.*,user_accesses.
                    module_id,departments.department_name,departments.department_group
                    FROM  users
                    LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                    LEFT JOIN departments departments ON departments.department_id = users.department_id
                    WHERE 1=1
                    -- AND departments.department_group = "'.$request->rapidxUserDeptGroup.'"
                    '.$rapidxUserDeptGroupQuery.'
                    '.$rapidxUserIdGroupQuery.'
                    AND users.user_stat = 1
                    AND user_accesses.module_id = 46'
                );
                if(count ($rapidxUserById) > 0){
                    return response()->json(['isSuccess' => 'true','rapidxUserById'=>$rapidxUserById]);
                }
                return response()->json(['isSuccess' => 'false','rapidxUserById'=>[],'msg' => 'User Not Found !',],500);
            }

            $rapidxUserById = DB::connection('mysql_rapidx')->select('SELECT users.*,user_accesses.
                module_id,departments.department_name,departments.department_group
                FROM  users
                LEFT JOIN user_accesses user_accesses ON user_accesses.user_id = users.id
                LEFT JOIN departments departments ON departments.department_id = users.department_id
                WHERE 1=1
                -- AND departments.department_group = "'.$request->rapidxUserDeptGroup.'"
                '.$rapidxUserDeptGroupQuery.'
                AND users.user_stat = 1
                AND user_accesses.module_id = 46'
            );
            if(count ($rapidxUserById) > 0){
                return response()->json(['isSuccess' => 'true','rapidxUserById'=>$rapidxUserById]);
            }
            return response()->json(['isSuccess' => 'false','rapidxUserById'=>[],'msg' => 'User Not Found !',],500);
        } catch (Exception $e) {
            return response()->json(['isSuccess' => 'false', 'exceptionError' => $e->getMessage()]);
        }
    }
    public function getPdfToGroup(Request $request){
        try {
            $dropdownCustomerGroup= $this->resourceInterface->readCustomEloquent(DropdownCustomerGroup::class,[],[],[]);
            $dropdownCustomerGroupData = $dropdownCustomerGroup->get();
            $dropdownCustomerGroupDataResource = DropdownCustomerGroupResource::collection($dropdownCustomerGroupData)->resolve();


            $customer = $request->customer;
            if(filled($dropdownCustomerGroupDataResource) && filled($customer)){
                $selectedCustomer = collect($dropdownCustomerGroupDataResource)->groupBy('customer');
                return response()->json([
                    'isSuccess' => 'true',
                    'customer' => $selectedCustomer[$customer][0]['customer'],
                    'recipientsCc' => explode(',',$selectedCustomer[$customer][0]['recipientsCc']),
                    'recipientsTo' => explode(',',$selectedCustomer[$customer][0]['recipientsTo']),
                ]);
            }
            if(filled($dropdownCustomerGroupDataResource) && blank($customer)){
                $selectedCustomer = collect($dropdownCustomerGroupDataResource)->map(function($row){
                    return  $row['customer'];
                });
                return response()->json([
                    'isSuccess' => 'true',
                    'customer' => $selectedCustomer,

                ]);
            }
            return response()->json([
                'isSuccess' => 'false',
            ],500);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
