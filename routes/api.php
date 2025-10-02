<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EcrController;
use App\Http\Controllers\ManController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EdocsController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\MethodController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\EnvironmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('check_session', function (Request $request) {
    // return session('rapidx_name');
    session_start();
    if($_SESSION){
        return true;
    }else{
        return false;
    }
});
//session_start in Authenticate Middleware, then passed it to the queries to get session
Route::middleware('auth')->group(function(){


    Route::controller(SettingsController::class)->group(function () {
        Route::post('save_dropdown_master_details', 'saveDropdownMasterDetails')->name('save_dropdown_master_details');
        Route::post('save_user_approver', 'saveUserApprover')->name('save_user_approver');
        Route::post('save_rapidx_user','saveRapidxUser')->name('save_rapidx_user');
        Route::post('del_classification_requirements','delClassificationRequirements')->name('del_classification_requirements');
        Route::get('get_user_master', 'getUserMaster')->name('get_user_master');
        Route::get('load_dropdown_master_details', 'loadDropdownMasterDetails')->name('load_dropdown_master_details');
        Route::get('load_classification_requirements', 'loadClassificationRequirements')->name('load_classification_requirements');
        Route::get('get_dropdown_master', 'getDropdownMaster')->name('get_dropdown_master');
        Route::get('get_dropdown_master_details_id', 'getDropdownMasterDetailsId')->name('get_dropdown_master_details_id');
        Route::get('get_admin_access_opt', 'getAdminAccessOpt')->name('get_admin_access_opt');
        Route::get('get_dropdown_master_category', 'getDropdownMasterCategory')->name('get_dropdown_master_category');
        Route::get('get_no_module_rapidx_user_by_id_opt', 'getNoModuleRapidxUserByIdOpt')->name('get_no_module_rapidx_user_by_id_opt');
    });

});
