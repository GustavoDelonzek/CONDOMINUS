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
        Schema::create('occurrence_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('occurrence_id')->constrained()->cascadeOnDelete();
            $table->text('media_url');
            $table->enum('media_type', ['image', 'video', 'document', 'audio'])->default('image');
            $table->timestamp('uploaded_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occurrence_media');
    }
};
