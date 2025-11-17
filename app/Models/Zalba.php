<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zalba extends Model
{
    protected $table = 'zalbe';

    protected $fillable = [
        'prijemni_broj',
        'datum_prijema_zalbe',
        'broj_resenja',
        'osnov_zalbe',
        'datum_vracanja_na_dopunu',
        'datum_prijema_dopune',
        'datum_predaje_komisiji',
        'datum_resavanja_na_zk',
        'datum_ekspedicije_ds_organu',
        'podnosioci_zalbe',
        'institucija',
        'tipovi_resenja',
        'tip_resenja_napomena',
        'napomena',
        'komisije_zkv',
        'datum_isticanja_donosenje',
        'status_zalbe',
        'rok_za_dopunu',
        'broj_odluke_us',
        'broj_resenja_zk_po_presudi_us',
        'datum_donosenja_odluke_us',
        'datum_prijema_tuzbe_od_us',
        'datum_ekspedicije_odgovora_zk',
        'datum_prijema_odluke_us',
        'datum_resenja_zk_po_presudi_us',
        'dostavnica',
        'clanovi_komisije1',
        'clanovi_komisije2',
        'naknada',
        'izvestilac_sa_zalbama',
        'tipovi_presude_us',
    ];

    protected $casts = [
        'datum_prijema_zalbe' => 'date',
        'datum_vracanja_na_dopunu' => 'date',
        'datum_prijema_dopune' => 'date',
        'datum_predaje_komisiji' => 'date',
        'datum_resavanja_na_zk' => 'date',
        'datum_ekspedicije_ds_organu' => 'date',
        'datum_isticanja_donosenje' => 'date',
        'rok_za_dopunu' => 'date',
        'datum_donosenja_odluke_us' => 'date',
        'datum_prijema_tuzbe_od_us' => 'date',
        'datum_ekspedicije_odgovora_zk' => 'date',
        'datum_prijema_odluke_us' => 'date',
        'datum_resenja_zk_po_presudi_us' => 'date',
        'dostavnica' => 'date',
        'naknada' => 'double',
    ];

    const CREATED_AT = 'datum_unosa';
    const UPDATED_AT = 'datum_azuriranja';

    public function podnosilac()
    {
        return $this->belongsTo(PodnosiocZalbe::class, 'podnosioci_zalbe');
    }

    public function tipResenja()
    {
        return $this->belongsTo(SifarnikTipResenja::class, 'tipovi_resenja');
    }

    public function tipPresude()
    {
        return $this->belongsTo(SifarnikTipPresude::class, 'tipovi_presude_us');
    }

    public function osnovZalbe()
    {
        return $this->belongsTo(SifarnikOsnovZalbe::class, 'osnov_zalbe', 'id');
    }

    public function izvestilac()
    {
        return $this->belongsTo(SifarnikClanoviKomisije::class, 'izvestilac_sa_zalbama');
    }

    public function komisijaZkv()
    {
        return $this->belongsTo(SifarnikClanoviKomisije::class, 'komisije_zkv');
    }

    public function clanKomisije1()
    {
        return $this->belongsTo(SifarnikClanoviKomisije::class, 'clanovi_komisije1');
    }

    public function clanKomisije2()
    {
        return $this->belongsTo(SifarnikClanoviKomisije::class, 'clanovi_komisije2');
    }
}
