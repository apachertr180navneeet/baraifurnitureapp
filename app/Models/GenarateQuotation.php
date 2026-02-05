<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class GenarateQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'genarate_quotation';

    protected $fillable = ['user_id', 'item_id', 'quantity', 'status','amount','pdf_url'];

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
