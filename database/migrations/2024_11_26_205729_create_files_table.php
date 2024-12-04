<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\FileStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('file_id');
            $table->string('file_type');
            $table->string('file_path')->nullable();
            $table->integer('file_order');
            $table->string('status')->default(FileStatus::NEW->value);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'file_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
