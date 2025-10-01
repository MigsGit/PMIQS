<?php

namespace App\Models;

use App\Models\MachineApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Machine extends Model
{
    protected $fillable = [
        'status',
        'approval_status',
        'ecrs_id',
        'original_filename',
        'filtered_document_name',
        'file_path',
    ];
    public function machine_approvals()
    {
        return $this->hasMany(MachineApproval::class, 'machines_id', 'id')->whereNull('deleted_at');
    }
    public function machine_approvals_pending()
    {
        return $this->hasMany(MachineApproval::class, 'machines_id', 'id')->where('status','PEN')->whereNull('deleted_at');
    }

}
