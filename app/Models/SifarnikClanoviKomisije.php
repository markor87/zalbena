<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SifarnikClanoviKomisije extends Model
{
    use HasFactory;

    protected $table = 'sifarnik_clanovi_komisije';

    public $timestamps = false;

    protected $fillable = [
        'ime',
        'prezime',
    ];
}
