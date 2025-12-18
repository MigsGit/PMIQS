<?php
namespace App\Services;
use App\Models\Ecr;
use App\Models\Material;
use App\Models\RapidxUser;
use App\Models\RapidMailer;
use App\Models\RapidAutoMailer;
use App\Interfaces\FileInterface;
use App\Interfaces\EmailInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\CommonInterface;
use App\Interfaces\ResourceInterface;
use App\Models\ClassificationRequirement;


class EmailService implements EmailInterface
{

    protected $resourceInterface;
    protected $commonInterface;
    public function __construct(
        ResourceInterface $resourceInterface,
        CommonInterface $commonInterface
    ) {
        $this->resourceInterface = $resourceInterface;
        $this->commonInterface = $commonInterface;
    }
    public function getEmailByRapidxUserId($userId){
        try {
            $user = RapidxUser::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            if (!$user->email) {
                throw new \Exception('User Email not found');
            }
           return [
            'fullName' => $user->name,
            'email' => $user->email,
        ];
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function sendEmail($data){
        try {
            // return $data;
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            return RapidMailer::insert($data);
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function sendEmailWithAttachment($data){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            return RapidMailer::insert($data);
            DB::commit();
            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function sendEmailWithSchedule($data){
        try {
            date_default_timezone_set('Asia/Manila');
            DB::beginTransaction();
            return RapidAutoMailer::insert($data);
            DB::commit();

            return response()->json(['is_success' => 'true']);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function ecrEmailMsg($additionalMsg){
        return $msg = '<!DOCTYPE html>
            <html>
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
                    <style type="text/css">
                        body{
                            font-family: Arial;
                            font-size: 15px;
                        }
                        .text-green{
                            color: green;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row" style="margin: 1px 10px;">
                                <div class="col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label style="font-size: 18px;">'.$additionalMsg.'</label>
                                                <br>
                                            </div>
                                        </div>

                                        </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </body>
            </html>';
    }
      public function ecrEmailMsgEcrRequirement($ecrsId){
        $data = [];
        $relations = [
            'ecr_requirement',
        ];
        $conditions = [];
        $arrCategory = [1,2,3,4,5];
        $classificationRequirement = $this->resourceInterface->readCustomEloquent(ClassificationRequirement::class,$data,$relations,$conditions);

        //If ECR Approved, show the CHECK decision only per Category
        $classificationRequirements = $classificationRequirement->whereHas('ecr_requirement', function ($query) use ($ecrsId) {
            $query->where('decision', 'C');
            $query->where('ecrs_id', $ecrsId);

        })->with(['ecr_requirement' => function ($query) use ($ecrsId) {
            $query->where('decision', 'C');
            $query->where('ecrs_id', $ecrsId);

        }])->get();


        $classificationRequirements = collect($classificationRequirements)
        ->map(function ($row){
           return [
                'classificationsId' => $row->classifications_id,
                'requirement' => $row->requirement,
                'details' => $row->details,
                'evidence' => $row->evidence,
           ];
        });
        $ecrRequirementGroupByCategory = $classificationRequirements->groupBy('classificationsId');
        $ecrRequirementManCategory = $ecrRequirementGroupByCategory[1] ?? 'null';
        $ecrRequirementMaterialCategory = $ecrRequirementGroupByCategory[2] ?? 'null';
        $ecrRequirementMachineCategory = $ecrRequirementGroupByCategory[3] ?? 'null';
        $ecrRequirementMethodCategory = $ecrRequirementGroupByCategory[4] ?? 'null';
        $ecrRequirementEnvironmentCategory = $ecrRequirementGroupByCategory[5] ?? 'null';
        $ecrRequirementOthersCategory = $ecrRequirementGroupByCategory[6] ?? 'null';


        // $ecrRequirementGroupByCategory->groupBy('classifications_id');

        $result = '';
        $result .= '<!DOCTYPE html>
            <html>
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
                    <style type="text/css">
                        body{
                            font-family: Arial;
                            font-size: 15px;
                        }
                        .text-green{
                            color: green;
                            font-weight: bold;
                        }
                        table {
                            border-collapse: collapse;
                            table-layout: fixed;
                            width: 100%;
                        }
                        table td {
                            border: solid 1px #666;
                            width: 200px;
                            word-wrap: break-word;
                        }
                        table th {
                            backgroud: skyblue;
                            border: solid 1px #666;
                            width: 200px;
                            word-wrap: break-word;
                        }
                    </style>
                </head>
                <body>';
                    $result .= '<div class="row">
                                    <div class="col-sm-12">
                                        <label style="font-size: 18px;">Good day!</label><br>
                                        <label style="font-size: 18px;">Kindly check the ECR Requirement</label>
                                        <br>
                                        <hr>
                                </div>
                            </div>';
                    $result .= '<br>';
                    if( $ecrRequirementManCategory != 'null'){
                        $result.=  $this->tableCategory($ecrRequirementManCategory);
                    }
                    if( $ecrRequirementMaterialCategory != 'null'){
                        $result.=  $this->tableCategory($ecrRequirementMaterialCategory);
                    }
                    if( $ecrRequirementMachineCategory != 'null'){
                        $result.=  $this->tableCategory($ecrRequirementMachineCategory);
                    }
                    if( $ecrRequirementMethodCategory != 'null'){
                        $result.=  $this->tableCategory($ecrRequirementMethodCategory);
                    }
                    if( $ecrRequirementEnvironmentCategory != 'null'){
                        $result.=  $this->tableCategory($ecrRequirementEnvironmentCategory);
                    }
                    if( $ecrRequirementOthersCategory != 'null'){
                        $result.=  $this->tableCategory($ecrRequirementOthersCategory);
                    }

                '
                </body>
            </html>';
            return $result;
    }
    public function tableCategory($ecrRequirementCategory){
        try {
            switch ($ecrRequirementCategory[0]['classificationsId']) {
                case 1:
                    $title = 'Man';
                    break;
                case 2:
                    $title = 'Material';
                    break;
                case 3:
                    $title = 'Machine';
                    break;
                case 4:
                    $title = 'Method';
                    break;
                case 5:
                    $title = 'Environment';
                    break;
                case 6:
                    $title = 'Others';
                    break;
                default:
                    $title = '';
                    break;
            }
            $result = '';
            $result .= $title;
            $result .= '<table class="table table-bordered">
                        <thead>
                            <tr>
                            <th scope="col">Requirement</th>
                            <th scope="col">Details</th>
                            <th scope="col">Evidence</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">';
            // ecrRequirementManCategory
                foreach($ecrRequirementCategory as $index => $value){
                    # code...
                    $result .='
                            <tr>
                            <td>'.$value['requirement'].'</td>
                            <td>'.$value['details'].'</td>
                            <td>'.$value['evidence'].'</td>
                            </tr>
                    ';
                }
            $result .='</tbody>
        </table>';
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function materialEmailMsg($selectedId){
        $material = Material::with(
            'ecr',
            'dropdown_detail_material_sample',
            'dropdown_detail_material_supplier',
            'ecr.rapidx_user_created_by',
        )->find($selectedId);

        // $approvalStatus = $this->commonInterface->getEcrApprovalStatus($ecr->approval_status);
        $ecr = $material->ecr;
        $createdBy = $ecr->rapidx_user_created_by->name;
        $getMaterialStatus = $material->status;
        if($getMaterialStatus == 'DIS'){
            $header = "Your request has been disapproved";
        }else if($getMaterialStatus == 'OK'){
            $header = "Your request has been approved";
        }else{
            $header = "Please see the request for your approval.";
        }

        $materialSample = filled($material->dropdown_detail_material_sample) ?$material->dropdown_detail_material_sample->dropdown_masters_details : "N/A";
        $materialSupplier =filled($material->dropdown_detail_material_supplier) ? $material->dropdown_detail_material_supplier->dropdown_masters_details : "N/A";
        return $msg = '<!DOCTYPE html>
            <html>
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
                    <style type="text/css">
                        body{
                            font-family: Arial;
                            font-size: 15px;
                        }
                        .text-green{
                            color: green;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row" style="margin: 1px 10px;">
                                <div class="col-sm-12">
                                    <form id="frmSaveRecord">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label style="font-size: 18px;">Good day!</label><br>
                                                <label style="font-size: 18px;">'.$header.'</label>
                                                <br>
                                                <hr>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Ecr Control No. : </b><span class="text-black"> '. $ecr->ecr_no.' </span></label>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Category : </b><span class="text-black"> '.$ecr->category.'</span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Customer Name: </b><span class="text-black"> '.$ecr->customer_name.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>ICP: </b><span class="text-black"> '.$material->icp.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>GP: </b><span class="text-black"> '.$material->gp.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>PD Material: </b><span class="text-black"> '.$material->icp.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>MSDS: </b><span class="text-black"> '.$material->msds.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Qoutation: </b><span class="text-black"> '.$material->qoutation.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Material Sample: </b><span class="text-black"> '.$materialSample.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Material Supplier: </b><span class="text-black"> '.$materialSupplier.' </span></label>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>ROHS: </b><span class="text-black"> '.$material->rohs.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Requested By: </b><span class="text-black"> '.$createdBy.'</span></label>
                                                </div>
                                            </div>
                                            <br>
                                            <br>
                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label">For more info, please log-in to your Rapidx account. Go to http://rapidx/ and Click http://rapidx/4M/dashboard </label>
                                                </div>
                                            </div>

                                            <br>
                                            <br>

                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Notice of Disclaimer: </b></label>
                                                    <br>
                                                    <label class="col-sm-12 col-form-label"></label>   This message contains confidential information intended for a specific individual and purpose. If you are not the intended recipient, you should delete this message. Any disclosure,copying, or distribution of this message, or the taking of any action based on it, is strictly prohibited.</label>
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <br><br>
                                                <label style="font-size: 18px;"><b>For concerns on using the form, please contact ISS at local numbers 205, 206, or 208. You may send us e-mail at <a href="mailto: servicerequest@pricon.ph">servicerequest@pricon.ph</a></b></label>
                                            </div>
                                        </div>

                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </body>
            </html>';
    }
    public function ecrEmailMsgWithStatus($ecrsId){
        $ecr = Ecr::with('rapidx_user_created_by')->find($ecrsId);
        $approvalStatus = $this->commonInterface->getEcrApprovalStatus($ecr->approval_status);
        $createdBy = $ecr->rapidx_user_created_by->name;
        return $msg = '<!DOCTYPE html>
            <html>
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
                    <style type="text/css">
                        body{
                            font-family: Arial;
                            font-size: 15px;
                        }
                        .text-green{
                            color: green;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row" style="margin: 1px 10px;">
                                <div class="col-sm-12">
                                    <form id="frmSaveRecord">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label style="font-size: 18px;">Good day!</label><br>
                                                <label style="font-size: 18px;">Please see the ECR for your approval.</label>
                                                <br>
                                                <hr>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Ecr Control No. : </b><span class="text-black"> '. $ecr->ecr_no.' </span></label>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="col-sm-12">
                                                <div   div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Approval Status: </b> '. $approvalStatus['approvalStatus'].' </label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Category : </b><span class="text-black"> '.$ecr->category.'</span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Customer Name: </b><span class="text-black"> '.$ecr->customer_name.' </span></label>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Part Number : </b><span class="text-black"> '.$ecr->part_no.' </span></label>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b>Part Name : </b><span class="text-black"> '.$ecr->part_name.' </span></label>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Device Name : </b><span class="text-black"> '.$ecr->device_name.' </span></label>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Product Line : </b><span class="text-black"> '.$ecr->product_line.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Section : </b><span class="text-black"> '.$ecr->section.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Customer Ec #: </b><span class="text-black"> '.$ecr->customer_ec_no.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Date Of Request: </b><span class="text-black"> '.$ecr->date_of_request.' </span></label>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Requested By: </b><span class="text-black"> '.$createdBy.'</span></label>
                                                </div>
                                            </div>
                                            <br>
                                            <br>
                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label">For more info, please log-in to your Rapidx account. Go to http://rapidx/ and Click http://rapidx/4M/dashboard </label>
                                                </div>
                                            </div>

                                            <br>
                                            <br>

                                            <div class="col-sm-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-form-label"><b> Notice of Disclaimer: </b></label>
                                                    <br>
                                                    <label class="col-sm-12 col-form-label"></label>   This message contains confidential information intended for a specific individual and purpose. If you are not the intended recipient, you should delete this message. Any disclosure,copying, or distribution of this message, or the taking of any action based on it, is strictly prohibited.</label>
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <br><br>
                                                <label style="font-size: 18px;"><b>For concerns on using the form, please contact ISS at local numbers 205, 206, or 208. You may send us e-mail at <a href="mailto: servicerequest@pricon.ph">servicerequest@pricon.ph</a></b></label>
                                            </div>
                                        </div>

                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </body>
            </html>';
    }
}
