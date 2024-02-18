<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DVBGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'step',
        'lang',
        'music',
        'deleted_at',
    ];
}
