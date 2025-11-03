<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SifarnikOsnovZalbe extends Model
{
    protected $table = 'sifarnik_osnov_zalbe';

    public $timestamps = false;

    protected $fillable = [
        'osnov_zalbe',
    ];
}
