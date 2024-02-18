<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DVBUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'step',
        'admin',
        'lang',
        'music',
        'deleted_at',
    ];
}
