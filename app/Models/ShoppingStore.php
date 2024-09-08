<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingStore extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'shopping_stores';
    protected $fillable = ['name', 'postcode', 'latitude', 'longitude', 'is_open', 'store_type', 'delivery_distance'];
}
