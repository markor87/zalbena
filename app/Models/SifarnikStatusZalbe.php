<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SifarnikStatusZalbe extends Model
{
    protected $table = 'sifarnik_status_zalbe';

    public $timestamps = false;

    protected $fillable = [
        'status_zalbe',
    ];
}
