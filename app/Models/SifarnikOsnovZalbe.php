<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SifarnikOsnovZalbe extends Model
{
    use HasFactory;

    protected $table = 'sifarnik_osnov_zalbe';

    public $timestamps = false;

    protected $fillable = [
        'osnov_zalbe',
    ];
}
