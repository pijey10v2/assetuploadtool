<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'createdBy',
        'createdByName',
        'asset_table_name',
        'mappings',
    ];

    protected $casts = [
        'mappings' => 'array',
    ];
}
