<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SifarnikTipResenja extends Model
{
    protected $table = 'sifarnik_tipovi_resenja';

    public $timestamps = false;

    protected $fillable = [
        'tip_resenja',
    ];
}
