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
        Schema::table('tugas', function (Blueprint $table) {
            $table->boolean('perlu_persetujuan_atasan')->default(false)->after('status');
            $table->string('jabatan_atasan_penyetuju', 50)->nullable()->after('perlu_persetujuan_atasan');
            $table->string('status_persetujuan_atasan', 50)->nullable()->after('jabatan_atasan_penyetuju');
            $table->foreignId('disetujui_atasan_oleh')->nullable()->after('status_persetujuan_atasan')->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_persetujuan_atasan')->nullable()->after('disetujui_atasan_oleh');
            $table->text('catatan_persetujuan_atasan')->nullable()->after('tanggal_persetujuan_atasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropForeign(['disetujui_atasan_oleh']);
            $table->dropColumn([
                'perlu_persetujuan_atasan',
                'jabatan_atasan_penyetuju',
                'status_persetujuan_atasan',
                'disetujui_atasan_oleh',
                'tanggal_persetujuan_atasan',
                'catatan_persetujuan_atasan',
            ]);
        });
    }
};
