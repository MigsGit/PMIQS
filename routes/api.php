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

    Route::controller(EcrController::class)->group(function () {
        Route::post('save_ecr', 'saveEcr')->name('save_ecr');
        Route::post('save_ecr_details', 'saveEcrDetails')->name('save_ecr_details');
        Route::post('save_ecr_approval', 'saveEcrApproval')->name('save_ecr_approval');
        Route::post('upload_ecr_requirement_ref', 'uploadEcrRequirementRef')->name('upload_ecr_requirement_ref');

        Route::get('generate_control_number', 'generateControlNumber')->name('generate_control_number');
        Route::get('get_dropdown_master_by_opt', 'getDropdownMasterByOpt')->name('get_dropdown_master_by_opt');
        Route::get('load_ecr', 'loadEcr')->name('load_ecr');
        Route::get('load_ecr_details_by_ecr_id', 'loadEcrDetailsByEcrId')->name('load_ecr_details_by_ecr_id');
        Route::get('load_ecr_requirements', 'loadEcrRequirements')->name('load_ecr_requirements');
        Route::get('get_ecr_by_id', 'getEcrById')->name('get_ecr_by_id');
        Route::get('get_ecr_details_id', 'getEcrDetailsId')->name('get_ecr_details_id');
        Route::get('ecr_req_decision_change', 'ecrReqDecisionChange')->name('ecr_req_decision_change');
        Route::get('load_ecr_approval_summary', 'loadEcrApprovalSummary')->name('load_ecr_approval_summary');

        Route::get('get_ecr_requirement_ref_by_id', 'getEcrRequirementRefById')->name('get_ecr_requirement_ref_by_id');
        Route::get('view_ecr_requirement_ref', 'viewEcrRequirementRef')->name('view_ecr_requirement_ref');
        Route::get('download_ecr_excel_by_ecrs_id', 'downloadEcrExcelByEcrsId')->name('download_ecr_excel_by_ecrs_id');
        Route::get('get_ecr_ref_download', 'getEcrRefDownload')->name('get_ecr_ref_download');
        Route::get('view_ecr_ref', 'viewEcrRef')->name('view_ecr_ref');
    });

    Route::controller(CommonController::class)->group(function (): void {
        Route::get('save_pmi_internal_approval', 'savePmiInternalApproval')->name('save_pmi_internal_approval');
        Route::post('save_special_inspection', 'saveSpecialInspection')->name('save_special_inspection');
        Route::post('save_external_disposition', 'saveExternalDisposition')->name('save_external_disposition');

        Route::get('test_email', 'testEmail')->name('test_email'); //test function
        Route::get('get_special_inspection_by_id', 'getSpecialInspectionById')->name('get_special_inspection_by_id');
        Route::get('get_rapidx_user_by_id_opt', 'getRapidxUserByIdOpt')->name('get_rapidx_user_by_id_opt');
        Route::get('get_no_module_rapidx_user_by_id_opt', 'getNoModuleRapidxUserByIdOpt')->name('get_no_module_rapidx_user_by_id_opt');
        Route::get('get_current_approver_session', 'getCurrentApproverSession')->name('get_current_approver_session');
        Route::get('get_current_pmi_internal_approver', 'getCurrentPmiInternalApprover')->name('get_current_pmi_internal_approver');
        Route::get('load_pmi_internal_approval_summary', 'loadPmiInternalApprovalSummary')->name('load_pmi_internal_approval_summary');
        Route::get('load_special_inspection_by_ecr_id', 'loadSpecialInspectionByEcrId')->name('load_special_inspection_by_ecr_id');
        Route::get('download_excel_by_ecrs_id', 'downloadExcelByEcrsId')->name('download.common_excel');
        Route::get('view_external_disposition', 'viewExternalDisposition')->name('view_external_disposition');

        Route::get('get_approval_count_by_rapidx_user_id', 'getApprovalCountByRapidxUserId')->name('get_approval_count_by_rapidx_user_id');
    });

    Route::controller(SettingsController::class)->group(function () {
        Route::post('save_dropdown_master_details', 'saveDropdownMasterDetails')->name('save_dropdown_master_details');
        Route::post('save_ecr_requirement_details', 'saveEcrRequirementDetails')->name('save_ecr_requirement_details');
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
        Route::get('get_ecr_requirement_master_category', 'getEcrRequirementMasterCategory')->name('get_ecr_requirement_master_category');
        Route::get('get_ecr_requirement_details_by_id', 'getEcrRequirementDetailsById')->name('get_ecr_requirement_details_by_id');
    });

    Route::controller(ManController::class)->group(function () {
        Route::post('save_man', 'saveMan')->name('save_man');
        Route::get('save_man_approval', 'saveManApproval')->name('save_man_approval');
        Route::get('load_ecr_man_by_status', 'loadEcrManByStatus')->name('load_ecr_man_by_status');
        Route::get('load_man_approver_summary_ecrs_id', 'loadManApproverSummaryEcrsId')->name('load_man_approver_summary_ecrs_id');
        Route::get('load_man_by_ecr_id', 'loadManByEcrId')->name('load_man_by_ecr_id');
        Route::get('load_man_checklist', 'loadManChecklist')->name('load_man_checklist');
        Route::get('get_man_by_id', 'getManById')->name('get_man_by_id');
        Route::get('man_checklist_decision_change', 'manChecklistDecisionChange')->name('man_checklist_decision_change');
    });

    Route::controller(MaterialController::class)->group(function () {
        Route::post('save_material', 'saveMaterial')->name('save_material');
        Route::post('upload_material_ref', 'uploadMaterialRef')->name('upload_material_ref');

        Route::get('save_material_approval', 'saveMaterialApproval')->name('save_material_approval');
        Route::get('load_ecr_material_by_status', 'loadEcrMaterialByStatus')->name('load_ecr_material_by_status');
        Route::get('get_material_ecr_by_id', 'getMaterialEcrById')->name('get_material_ecr_by_id');
        Route::get('get_material_ref_by_ecrs_id', 'getMaterialRefByEcrsId')->name('get_material_ref_by_ecrs_id');
        Route::get('view_material_ref', 'viewMaterialRef')->name('view_material_ref');
        Route::get('load_material_approval_by_meterial_id', 'loadMaterialApprovalByMeterialId')->name('load_material_approval_by_meterial_id');
        Route::get('download_internal_material', 'downloadInternalMaterial')->name('download_internal_material');
    });

    Route::controller(MachineController::class)->group(function () {
        Route::post('save_machine', 'saveMachine')->name('save_machine');

        Route::get('save_machine_approval', 'saveMachineApproval')->name('save_machine_approval');
        Route::get('load_ecr_machine_by_status', 'loadEcrMachineByStatus')->name('load_ecr_machine_by_status');
        Route::get('load_machine_approver_summary', 'loadMachineApproverSummary')->name('load_machine_approver_summary');
        Route::get('load_machine_approver_summary_id', 'loadMachineApproverSummaryId')->name('load_machine_approver_summary_id');
        Route::get('get_machine_ref_by_id', 'getMachineRefById')->name('get_machine_ref_by_id');
        Route::get('view_machine_ref', 'viewMachineRef')->name('view_machine_ref');
        Route::get('download_internal_machine', 'downloadInternalMachine')->name('download_internal_machine');
    });

    Route::controller(MethodController::class)->group(function () {
        Route::post('save_method', 'saveMethod')->name('save_method');

        Route::get('load_method_ecr_by_status', 'loadMethodEcrByStatus')->name('load_method_ecr_by_status');
        Route::get('load_method_approver_summary_material_id', 'loadMethodApproverSummaryMaterialId')->name('load_method_approver_summary_material_id');
        Route::get('get_method_ref_by_id', 'getMethodRefById')->name('get_method_ref_by_id');
        Route::get('view_method_ref', 'viewMethodRef')->name('view_method_ref');
        Route::get('save_method_approval', 'saveMethodApproval')->name('save_method_approval');
    });

    Route::controller(EnvironmentController::class)->group(function () {
        Route::post('upload_environment_ref', 'uploadEnvironmentRef')->name('upload_environment_ref');

        Route::get('load_ecr_environment_by_status', 'loadEcrEnvironmentByStatus')->name('load_ecr_environment_by_status');
        Route::get('get_environment_ref_by_ecrs_id', 'getEnvironmentRefByEcrsId')->name('get_environment_ref_by_ecrs_id');
        Route::get('view_environment_ref', 'viewEnvironmentRef')->name('view_environment_ref');
    });
});
