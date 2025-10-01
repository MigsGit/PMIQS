<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Method extends Model
{
    protected $fillable = [
        'status',
        'approval_status',
        'ecrs_id',
        'original_filename',
        'filtered_document_name',
        'file_path',
    ];
    public function method_approvals()
    {
        return $this->hasMany(MethodApproval::class, 'methods_id', 'id')->whereNull('deleted_at');
    }
    public function method_approvals_pending()
    {
        return $this->hasMany(MethodApproval::class, 'methods_id', 'id')->where('status','PEN')->whereNull('deleted_at');
    }
}
