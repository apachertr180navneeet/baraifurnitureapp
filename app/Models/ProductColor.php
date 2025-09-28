<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProductColor extends Model
{
    use HasFactory, SoftDeletes; // <-- Add SoftDeletes

    protected $fillable = ['product_id', 'color_name', 'qty','status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
