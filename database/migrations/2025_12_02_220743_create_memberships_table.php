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
        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_company_id')->nullable()->constrained('admin_companies')->cascadeOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('condominium_id')->nullable()->constrained('condominiums')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role'); //super_admin, company_admin, syndic, resident, porter e landlord...
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
