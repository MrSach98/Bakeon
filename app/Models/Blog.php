<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'image',
        'meta_title', 'meta_description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}