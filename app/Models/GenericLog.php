<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericLog extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'generic_logs';
    protected $fillable = ['log_type', 'message'];
}
