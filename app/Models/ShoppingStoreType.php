<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingStoreType extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'shopping_store_types';
    protected $fillable = ['type_name'];
}
