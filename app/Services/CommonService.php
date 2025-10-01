<?php
namespace App\Services;
use setasign\Fpdi\Fpdi;
use App\Models\RapidxUser;
use App\Models\RapidMailer;
use Illuminate\Support\Str;
use App\Models\RapidAutoMailer;
use App\Interfaces\FileInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use Illuminate\Support\Facades\Storage;


class CommonService implements CommonInterface
{
    protected $fileInterface;
    protected $fpdi;
    public function __construct(FileInterface $fileInterface,Fpdi $fpdi) {
        $this->fileInterface = $fileInterface;
        $this->fpdi = $fpdi;
    }
    public function uploadFileEcrRequirement($txtDocuReference,$path)
    {
        try {
            $currentPath= 'public/'.$path;
            $newFolderPath= 'public/'.$path.time();
            if (Storage::exists($currentPath)) {
                Storage::deleteDirectory($currentPath);
                // Storage::move($currentPath, $newFolderPath); //change file name if exist
            }
            $arr_filtered_filename = [];
            $arr_original_filename = [];
            foreach ($txtDocuReference as $key => $file) { //$request->file('txt_docu_reference')
                $original_filename = $file->getClientOriginalName(); //'/etc#hosts/@Álix Ãxel likes - beer?!.pdf';
                $filtered_filename = $key.'_'.$this->fileInterface->Slug($original_filename, '_', '.');	 // _etc_hosts_alix_axel_likes_beer.pdf //Interface

                // $file->storeAs($folderPath, $filtered_filename, 'public'); // 'storage' disk is used for storing files // not active
                Storage::putFileAs($currentPath, $file, $filtered_filename);//change file to storage //active
                $arr_original_filename[] =$original_filename;
                $arr_filtered_filename[] =$filtered_filename;
            }
            return [
                'arr_filtered_document_name' => $arr_filtered_filename,
                'arr_original_filename' => $arr_original_filename,
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function uploadFile($txtDocuReference,$id,$path) // $request->txt_docu_reference
    {
        try {
            $currentPath= 'public/'.$path.'/'.$id;
            $newFolderPath= 'public/'.$path.'/'.$id.'_'.time();
            if (Storage::exists($currentPath)) {
                Storage::deleteDirectory($currentPath);
                // Storage::move($currentPath, $newFolderPath); //change file name if exist
            }
            $arr_filtered_filename = [];
            $arr_original_filename = [];
            foreach ($txtDocuReference as $key => $file) { //$request->file('txt_docu_reference')
                $original_filename = $file->getClientOriginalName(); //'/etc#hosts/@Álix Ãxel likes - beer?!.pdf';
                $filtered_filename = $key.'_'.$this->fileInterface->Slug($original_filename, '_', '.');	 // _etc_hosts_alix_axel_likes_beer.pdf //Interface

                // $file->storeAs($folderPath, $filtered_filename, 'public'); // 'storage' disk is used for storing files // not active
                Storage::putFileAs($currentPath, $file, $filtered_filename);//change file to storage //active
                $arr_original_filename[] =$original_filename;
                $arr_filtered_filename[] =$filtered_filename;
            }
            return [
                'arr_filtered_document_name' => $arr_filtered_filename,
                'arr_original_filename' => $arr_original_filename,
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function uploadFileImg($machineRefBefore,$machineRefAfter,$id,$path)
    {
        try {
            $currentPathBefore= 'public/'.$path.'/'.$id.'/before';
            if (Storage::exists($currentPathBefore)) {
                Storage::deleteDirectory($currentPathBefore);
            }
            $arr_original_filename_before = [];
            $arr_filtered_filename_before = [];
            foreach ($machineRefBefore as $key => $file) { //$request->file('txt_docu_reference')
                $original_filename = $file->getClientOriginalName(); //'/etc#hosts/@Álix Ãxel likes - beer?!.pdf';
                $filtered_filename = $key.'_'.$this->fileInterface->Slug($original_filename, '_', '.');	 // _etc_hosts_alix_axel_likes_beer.pdf //Interface

                Storage::putFileAs($currentPathBefore, $file, $filtered_filename);//change file to storage //active
                $arr_original_filename_before[] =$original_filename;
                $arr_filtered_filename_before[] =$filtered_filename;
            }
            $currentPathAfter= 'public/'.$path.'/'.$id.'/after';
            if (Storage::exists($currentPathAfter)) {
                Storage::deleteDirectory($currentPathAfter);
            }
            $arr_filtered_filename_after = [];
            $arr_original_filename_after = [];
            foreach ($machineRefAfter as $key => $file) { //$request->file('txt_docu_reference')
                $original_filename = $file->getClientOriginalName(); //'/etc#hosts/@Álix Ãxel likes - beer?!.pdf';
                $filtered_filename = $key.'_'.$this->fileInterface->Slug($original_filename, '_', '.');	 // _etc_hosts_alix_axel_likes_beer.pdf //Interface

                // return $file;
                Storage::putFileAs($currentPathAfter, $file, $filtered_filename);//change file to storage //active
                $arr_filtered_filename_after[] =$filtered_filename;
                $arr_original_filename_after[] =$original_filename;
            }
            return [
                'arr_filtered_document_name_before' => $arr_filtered_filename_before,
                'arr_original_filename_before' => $arr_original_filename_before,
                'arr_filtered_document_name_after' => $arr_filtered_filename_after,
                'arr_original_filename_after' => $arr_original_filename_after,
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function viewPdfFile($pdfPath){

        try {
            $pageCount = $this->fpdi->setSourceFile($pdfPath);
            //Read all page using page count
            for ($i=1; $i <= $pageCount; $i++) {
                $templateId = $this->fpdi->importPage($i);
                // Insert the image at specified coordinates
                $pdfDimensions = $this->fpdi->getTemplateSize($templateId);
                $w = $pdfDimensions['width'];
                $h = $pdfDimensions['height'];
                //Calculate the position of the Signature Image

                $orientation 	= 'P';
                $page_size 	= 'A4';
                if($w > $h){
                    $orientation 	= 'L';
                    /* A4 size is width 210 x height 297 mm */
                    /* A3 size is width 297 x height 420 mm */
                    if($w > 297){
                        $page_size 			= 'A3';
                    }
                }

                // Add a page to the PDF
                $this->fpdi->AddPage($orientation,$page_size);
                $this->fpdi->useTemplate($templateId);

                // Add a image to the PDF
                $this->fpdi->SetFont('Arial', '', 5);

                // Generate a file path for the output PDF
                // $outputPath = storage_path('app/public/modified_pdf.pdf');
            }
            $this->fpdi->Output();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function viewImageFile($filePath){
        try {
            $path = storage_path($filePath);
            if (!file_exists($path)) {
                abort(404, 'Image not found');
            }
            return response()->file($path);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getPmiApprovalStatus($approvalStatus){
        try {
             switch ($approvalStatus) {
                 case 'PB':
                     $approvalStatus = 'Prepared by:';
                     break;
                 case 'CB':
                     $approvalStatus = 'Checked by:';
                     break;
                 case 'AB':
                     $approvalStatus = 'Approved by:';
                     break;
                 case 'EXQC':
                     $approvalStatus = 'QC Head:';
                     break;
                 case 'EXOH':
                     $approvalStatus = 'Operation Head:';
                     break;
                 case 'EXQA':
                     $approvalStatus = 'QA Head:';
                     break;
                 default:
                     $approvalStatus = '---';
                     break;
             }
             return [
                 'approvalStatus' => $approvalStatus,
             ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getEcrStatus($current_status){

        try {
             switch ($current_status) {
                 case 'IA':
                     $status = 'Internal Approval';
                     $bgStatus = 'badge rounded-pill bg-primary';
                     break;
                 case 'QA':
                     $status = 'QA Approval';
                     $bgStatus = 'badge rounded-pill bg-warning';
                     break;
                 case 'DIS':
                     $status = 'DISAPPROVED';
                     $bgStatus = 'badge rounded-pill bg-danger';
                     break;
                case 'OK':
                    $status = 'APPROVED';
                    $bgStatus = 'badge rounded-pill bg-success';
                    break;
                 default:
                     $status = '';
                     $bgStatus = '';
                     break;
             }
             return [
                 'status' => $status,
                 'bgStatus' => $bgStatus,
                 'current_status' => $current_status,
             ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getEcrApprovalStatus($approvalStatus){
        try {
             switch ($approvalStatus) {
                 case 'OTRB':
                     $approvalStatus = 'Requested by:';
                     break;
                 case 'OTTE':
                     $approvalStatus = 'Technical Engg:';
                     break;
                 case 'OTRVB':
                     $approvalStatus = 'Reviewed By:';
                     break;
                 case 'QACB':
                     $approvalStatus = 'QA Engineer';
                     break;
                 case 'QAIN':
                     $approvalStatus = 'QA Manager';
                     break;
                 case 'QAEX':
                     $approvalStatus = 'QMS Head';
                     break;
                 default:
                     $approvalStatus = '';
                     break;
             }
             return [
                 'approvalStatus' => $approvalStatus,
             ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getRapidxUserDeptByDeptId($departmentId){
        try {
           $departmentId;
            $rapidx_user = DB::connection('mysql_rapidx')
            ->select(" SELECT department_group
                FROM departments
                WHERE department_id = '".$departmentId."'
            ");
            $hris_data = DB::connection('mysql_systemone_hris')
            ->select("SELECT Department,Division,Section FROM vw_employeeinfo WHERE EmpNo = '".session('rapidx_employee_number')."'");
            $subcon_data = DB::connection('mysql_systemone_subcon')
            ->select("SELECT Department,Division,Section FROM vw_employeeinfo WHERE EmpNo = '".session('rapidx_employee_number')."'");
            if(count($hris_data) > 0 && count($rapidx_user)> 0){
                $vwEmployeeinfo =  $hris_data;
                $filteredSection = str_replace("'", "", $this->getFilteredSection($vwEmployeeinfo[0]->Department));
                $division = ($rapidx_user[0]->department_group == "PPS" || $rapidx_user[0]->department_group == "PPD") ? "PPD" : (($rapidx_user[0]->department_group == "LOG" || $rapidx_user[0]->department_group == "ISS" || $rapidx_user[0]->department_group == "FIN") ? "ADMIN" :
                $rapidx_user[0]->department_group);
            }
            if(count($subcon_data) > 0 && count($rapidx_user) > 0){
                $vwEmployeeinfo =  $subcon_data;
                $filteredSection = str_replace("'", "", $this->getFilteredSection($vwEmployeeinfo[0]->Department));
                $division = ($rapidx_user[0]->department_group == "PPS" || $rapidx_user[0]->department_group == "PPD") ? "PPD" : (($rapidx_user[0]->department_group == "LOG" || $rapidx_user[0]->department_group == "ISS" || $rapidx_user[0]->department_group == "FIN")  ? "ADMIN" :
                $rapidx_user[0]->department_group);
            }
            return [
                'division' => $division,
                'filteredSection' => $filteredSection,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getFilteredSection($department){
        try {
            if ( Str::contains($department, "LQC")) {
                $filteredSection = "LQC";
            } elseif (Str::contains($department, "Engineering")) {
                $filteredSection = "ENG'G";
            } elseif (Str::contains($department, "Production")) {
                $filteredSection = "PROD";
            }elseif (Str::contains($department, "-")) {
                $filteredSection = "LOG-PCH";
            }
            else {
                $filteredSection = "???";
            }
            return $filteredSection;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
