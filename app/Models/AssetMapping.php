<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMapping extends Model
{
    use HasFactory;

    protected $table = 'asset_mappings';

    protected $fillable = [
        'asset_table_name',
        'mappings',
        'createdBy',
    ];

    protected $casts = [
        'mappings' => 'array',
    ];
}
