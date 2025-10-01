<?php

namespace App\Interfaces;

interface CommonInterface
{

    public function uploadFileEcrRequirement($txtDocuReference,$path);
    public function uploadFile($txtDocuReference,$id,$path);
    public function uploadFileImg($machineRefBefore,$machineRefAfter,$id,$path);
    public function viewPdfFile($pdfPath);
    public function viewImageFile($filePath);
    public function getPmiApprovalStatus($approvalStatus);
    public function getEcrStatus($status);
    public function getEcrApprovalStatus($approvalStatus);
    public function getRapidxUserDeptByDeptId($departmentId);
}
