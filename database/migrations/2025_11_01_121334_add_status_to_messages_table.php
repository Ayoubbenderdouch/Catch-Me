<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // WhatsApp-style message status:
            // - sent: One checkmark (message sent to server)
            // - delivered: Two gray checkmarks (message delivered to recipient)
            // - read: Two green checkmarks (message opened/read by recipient)
            $table->enum('status', ['sent', 'delivered', 'read'])
                  ->default('sent')
                  ->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
