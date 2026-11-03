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
        Schema::create('tugas_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('status');
            $table->string('keterangan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // Backfill logs for existing tasks
        $existingTugas = DB::table('tugas')->get();
        foreach ($existingTugas as $t) {
            // 1. Initial creation log
            DB::table('tugas_logs')->insert([
                'tugas_id' => $t->id,
                'user_id' => $t->admin_id,
                'action' => 'delegasi',
                'status' => 'Didelegasikan',
                'keterangan' => 'Tugas dibuat dan didelegasikan kepada pelaksana tugas.',
                'catatan' => null,
                'created_at' => $t->created_at,
                'updated_at' => $t->created_at,
            ]);

            // 2. Submission log if submitted
            if ($t->tanggal_tindak_lanjut) {
                DB::table('tugas_logs')->insert([
                    'tugas_id' => $t->id,
                    'user_id' => $t->petugas_id,
                    'action' => 'kirim_balik',
                    'status' => 'Menunggu Konfirmasi',
                    'keterangan' => 'Pelaksana tugas telah menindaklanjuti tugas dan mengirimkan bukti laporan.',
                    'catatan' => $t->catatan_petugas,
                    'created_at' => $t->tanggal_tindak_lanjut,
                    'updated_at' => $t->tanggal_tindak_lanjut,
                ]);
            }

            // 3. Completed log if completed
            if ($t->status === 'Selesai') {
                $closedAt = $t->updated_at ?? $t->tanggal_tindak_lanjut ?? $t->created_at;
                DB::table('tugas_logs')->insert([
                    'tugas_id' => $t->id,
                    'user_id' => $t->admin_id,
                    'action' => 'selesai',
                    'status' => 'Selesai',
                    'keterangan' => 'Tugas diverifikasi dan dinyatakan Selesai (Closed) oleh pendelegasi tugas.',
                    'catatan' => $t->catatan_admin,
                    'created_at' => $closedAt,
                    'updated_at' => $closedAt,
                ]);
            } elseif ($t->status === 'Perlu Revisi') {
                DB::table('tugas_logs')->insert([
                    'tugas_id' => $t->id,
                    'user_id' => $t->admin_id,
                    'action' => 'revisi',
                    'status' => 'Perlu Revisi',
                    'keterangan' => 'Pendelegasi tugas meminta perbaikan / revisi tugas.',
                    'catatan' => $t->catatan_admin,
                    'created_at' => $t->updated_at,
                    'updated_at' => $t->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_logs');
    }
};
