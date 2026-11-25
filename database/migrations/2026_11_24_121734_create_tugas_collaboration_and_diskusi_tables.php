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
        Schema::create('tugas_petugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tugas_id', 'user_id']);
        });

        Schema::create('tugas_diskusi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('pesan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::table('tugas_logs', function (Blueprint $table) {
            $table->string('file_url', 500)->nullable()->after('catatan');
            $table->string('file_name')->nullable()->after('file_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_logs', function (Blueprint $table) {
            $table->dropColumn(['file_url', 'file_name']);
        });

        Schema::dropIfExists('tugas_diskusi');
        Schema::dropIfExists('tugas_petugas');
    }
};
