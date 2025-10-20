<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLayer extends Model
{
    protected $table = 'Project_Layers'; // Match exact table name
    protected $primaryKey = 'Layer_ID'; // Your primary key field
    public $timestamps = false; // No created_at / updated_at columns

    protected $fillable = [
        'Layer_ID',
        'Data_ID',
        'Layer_Name',
        'Attached_Date',
        'Project_ID',
        'Attached_By',
        'Modified_By',
        'Modified_Date',
    ];
}
