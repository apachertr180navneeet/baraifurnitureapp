<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomizeOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customize_orders';

    protected $fillable = [
        'date',
        'orderId',
        'customerId',
        'remark',
        'image',
        'status',
        'coustomername'
    ];

    protected $dates = ['deleted_at', 'date'];

    /**
     * Get the customer associated with the customize order.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customerId', 'id');
    }
}
