<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UKPostCode extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'uk_postcodes';
    protected $fillable = ['postcode', 'latitude', 'longitude'];
}
