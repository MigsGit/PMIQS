<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapidAutoMailer extends Model
{
    use HasFactory;
    protected $connection = "mysql_rapid_auto_mailer";
    protected $table = "tbl_auto_mailer";


}
