<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SifarnikClanoviKomisije extends Model
{
    protected $table = 'sifarnik_clanovi_komisije';

    public $timestamps = false;

    protected $fillable = [
        'ime',
        'prezime',
    ];
}
