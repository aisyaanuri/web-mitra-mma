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
        Schema::table('order_returns', function (Blueprint $table) {
            $table->string('physical_status')->default('menunggu_barang')->after('refund_amount');
            $table->text('physical_note')->nullable()->after('physical_status');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('physical_note');
            $table->timestamp('received_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['physical_status', 'physical_note', 'verified_by', 'received_at']);
        });
    }
};
