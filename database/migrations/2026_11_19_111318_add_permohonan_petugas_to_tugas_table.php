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
            $table->boolean('permohonan_hold')->default(false)->after('catatan_admin');
            $table->boolean('permohonan_perpanjangan')->default(false)->after('permohonan_hold');
            $table->date('usulan_tanggal_selesai')->nullable()->after('permohonan_perpanjangan');
            $table->text('catatan_permohonan')->nullable()->after('usulan_tanggal_selesai');
            $table->timestamp('tanggal_permohonan')->nullable()->after('catatan_permohonan');
            $table->string('status_permohonan', 50)->nullable()->after('tanggal_permohonan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn([
                'permohonan_hold',
                'permohonan_perpanjangan',
                'usulan_tanggal_selesai',
                'catatan_permohonan',
                'tanggal_permohonan',
                'status_permohonan',
            ]);
        });
    }
};
