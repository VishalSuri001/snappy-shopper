<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedRecord extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'failed_records';
    protected $fillable = ['postcode', 'latitude', 'longitude', 'file_name', 'error_message', 'line_number'];
}
