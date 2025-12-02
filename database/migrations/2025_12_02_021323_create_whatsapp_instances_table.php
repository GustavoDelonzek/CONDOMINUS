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
        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_company_id')->constrained('admin_companies')->cascadeOnDelete();
            $table->string('instance_id');
            $table->string('instance_token'); // TODO: adicionar meu client token na env
            $table->string('status'); //TODO: enum com 'connected', 'disconnected', 'qrcode'
            $table->string('phone_number_connected')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instances');
    }
};
