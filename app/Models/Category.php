<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    // Explicitly set the table name
    protected $table = 'categories';

    // Mass assignable fields
    protected $fillable = ['name', 'status'];
}
