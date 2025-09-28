<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes; // <-- Added SoftDeletes

    protected $fillable = [
        'order_date',
        'product_id',
        'customer_id',
        'price',
        'status',
    ];

    // Relation to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relation to User (customer)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
