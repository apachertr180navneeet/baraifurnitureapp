<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'title',
        'description',
        'status',
    ];

    // Optional: cast date to Carbon instance
    protected $dates = ['date'];
}
