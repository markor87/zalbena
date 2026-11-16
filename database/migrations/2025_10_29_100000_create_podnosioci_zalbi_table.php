<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline migracija: tabela je nastala pre uvodjenja migracija u projekat,
 * pa je ovde opisana onako kako vec postoji u bazi (bez indeksa i stranih
 * kljuceva, sa utf8mb4_general_ci kolacijom). Provera hasTable() znaci da je
 * `php artisan migrate` bezopasan na postojecoj bazi, a na praznoj (testnoj)
 * pravi istu shemu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('podnosioci_zalbi')) {
            return;
        }

        Schema::create('podnosioci_zalbi', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id')->autoIncrement();
            $table->string('ime_podnosioca_zalbe')->nullable();
            $table->string('prezime_podnosioca_zalbe')->nullable();
            $table->text('vdbIdentity')->nullable();
            $table->string('jmbg_podnosioca_zalbe', 13)->nullable();
            $table->string('institucija_podnosioca_zalbe')->nullable();
            $table->integer('podnosioci_zalbe')->nullable();
            $table->text('napomena')->nullable();
            $table->timestamp('datum_unosa')->nullable()->useCurrent();
            $table->timestamp('datum_azuriranja')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podnosioci_zalbi');
    }
};
