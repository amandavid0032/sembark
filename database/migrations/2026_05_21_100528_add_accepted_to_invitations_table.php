<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('invited_by');
            $table->foreignId('accepted_user_id')->nullable()->after('accepted_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('accepted_user_id');
            $table->dropColumn('accepted_at');
        });
    }
};
