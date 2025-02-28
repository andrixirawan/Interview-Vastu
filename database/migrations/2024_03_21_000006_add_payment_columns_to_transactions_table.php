<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_status')->default('pending');
            $table->string('snap_token')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('transaction_status')->nullable();
            $table->string('fraud_status')->nullable();
            $table->json('payment_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'snap_token',
                'payment_url',
                'payment_type',
                'payment_time',
                'transaction_id',
                'transaction_status',
                'fraud_status',
                'payment_details'
            ]);
        });
    }
}; 