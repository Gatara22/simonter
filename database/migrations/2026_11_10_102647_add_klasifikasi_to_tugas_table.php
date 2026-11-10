<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->string('klasifikasi', 50)->default('Kesekretariatan')->after('judul');
        });

        DB::table('tugas')
            ->whereIn('admin_id', function ($query) {
                $query->select('id')->from('users')->where('jabatan', 'Panitera Muda');
            })
            ->update(['klasifikasi' => 'Kepaniteraan']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn('klasifikasi');
        });
    }
};
