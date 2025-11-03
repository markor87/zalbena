<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SifarnikTipPresude extends Model
{
    protected $table = 'sifarnik_tip_presude';

    public $timestamps = false;

    protected $fillable = [
        'tip_presude',
    ];
}
