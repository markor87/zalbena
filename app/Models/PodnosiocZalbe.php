<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PodnosiocZalbe extends Model
{
    protected $table = 'podnosioci_zalbi';

    protected $fillable = [
        'ime_podnosioca_zalbe',
        'prezime_podnosioca_zalbe',
        'jmbg_podnosioca_zalbe',
        'institucija_podnosioca_zalbe',
        'podnosioci_zalbe',
        'napomena',
    ];

    const CREATED_AT = 'datum_unosa';
    const UPDATED_AT = 'datum_azuriranja';
}
