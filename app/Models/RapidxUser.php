<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RapidxUser extends Model
{
    protected $connection = 'mysql_rapidx';
    protected $table = 'users';

    protected $hidden_fields = ['created_at'];

}
