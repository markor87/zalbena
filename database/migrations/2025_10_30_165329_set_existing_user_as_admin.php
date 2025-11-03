<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set the first user as admin
        $user = User::first();
        if ($user) {
            $user->role = 'admin';
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the first user back to regular user
        $user = User::first();
        if ($user) {
            $user->role = 'user';
            $user->save();
        }
    }
};
