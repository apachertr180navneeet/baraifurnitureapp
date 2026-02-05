<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cart';

    protected $fillable = ['user_id', 'item_id', 'quantity', 'status','amount'];

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
