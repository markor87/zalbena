<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline migracija za sifarnik - tabela postoji u bazi od pre uvodjenja
 * migracija, pa je opisana onako kako jeste.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sifarnik_status_zalbe')) {
            return;
        }

        Schema::create('sifarnik_status_zalbe', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';

            $table->integer('id')->autoIncrement();
            $table->string('status_zalbe')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sifarnik_status_zalbe');
    }
};
