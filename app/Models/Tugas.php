<?php

namespace App\Models;

use Database\Factories\TugasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Tugas extends Model
{
    /** @use HasFactory<TugasFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tugas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'klasifikasi',
        'deskripsi',
        'admin_id',
        'petugas_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'file_tugas',
        'file_tugas_name',
        'status',
        'catatan_petugas',
        'file_evidence',
        'file_evidence_name',
        'link_evidence',
        'tanggal_tindak_lanjut',
        'catatan_admin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_tindak_lanjut' => 'datetime',
        ];
    }

    /**
     * The admin who delegated this task.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * The officer assigned to this task.
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Get the log / history entries for this task.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TugasLog::class, 'tugas_id')->orderBy('created_at', 'asc');
    }

    /**
     * Record a new log entry for this task.
     */
    public function recordLog(string $action, string $status, string $keterangan, ?string $catatan = null, ?int $userId = null): TugasLog
    {
        return $this->logs()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'status' => $status,
            'keterangan' => $keterangan,
            'catatan' => $catatan,
        ]);
    }
}
