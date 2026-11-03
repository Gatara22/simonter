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
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi');
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('file_tugas')->nullable();
            $table->string('file_tugas_name')->nullable();
            $table->string('status')->default('Didelegasikan');
            $table->text('catatan_petugas')->nullable();
            $table->string('file_evidence')->nullable();
            $table->string('file_evidence_name')->nullable();
            $table->text('link_evidence')->nullable();
            $table->timestamp('tanggal_tindak_lanjut')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
