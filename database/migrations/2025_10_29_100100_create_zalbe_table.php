<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline migracija za centralnu tabelu zalbi. Opisuje shemu kakva je u bazi.
 * Napomena: produkciona tabela nema nijedan indeks osim primarnog kljuca, pa ga
 * ni ova migracija ne dodaje - da bi testna i produkciona shema bile iste.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('zalbe')) {
            return;
        }

        Schema::create('zalbe', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id')->autoIncrement();
            $table->string('prijemni_broj')->nullable();
            $table->date('datum_prijema_zalbe')->nullable();
            $table->string('broj_resenja')->nullable();
            $table->text('osnov_zalbe')->nullable();
            $table->date('datum_vracanja_na_dopunu')->nullable();
            $table->date('datum_prijema_dopune')->nullable();
            $table->date('datum_predaje_komisiji')->nullable();
            $table->date('datum_resavanja_na_zk')->nullable();
            $table->date('datum_ekspedicije_ds_organu')->nullable();
            $table->integer('podnosioci_zalbe')->nullable();
            $table->string('institucija')->nullable();
            $table->integer('tipovi_resenja')->nullable();
            $table->text('tip_resenja_napomena')->nullable();
            $table->mediumText('napomena')->nullable();
            $table->integer('komisije_zkv')->nullable();
            $table->date('datum_isticanja_donosenje')->nullable();
            $table->string('status_zalbe')->nullable();
            $table->date('rok_za_dopunu')->nullable();
            $table->string('broj_odluke_us')->nullable();
            $table->string('broj_resenja_zk_po_presudi_us')->nullable();
            $table->date('datum_donosenja_odluke_us')->nullable();
            $table->date('datum_prijema_tuzbe_od_us')->nullable();
            $table->date('datum_ekspedicije_odgovora_zk')->nullable();
            $table->date('datum_prijema_odluke_us')->nullable();
            $table->date('datum_resenja_zk_po_presudi_us')->nullable();
            $table->date('dostavnica')->nullable();
            $table->text('clanovi_komisije1')->nullable();
            $table->text('clanovi_komisije2')->nullable();
            $table->double('naknada')->nullable();
            $table->integer('izvestilac_sa_zalbama')->nullable();
            $table->integer('tipovi_presude_us')->nullable();
            $table->timestamp('datum_unosa')->nullable()->useCurrent();
            $table->timestamp('datum_azuriranja')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zalbe');
    }
};
