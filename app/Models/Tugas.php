<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TugasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
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
        'perlu_persetujuan_atasan',
        'jabatan_atasan_penyetuju',
        'status_persetujuan_atasan',
        'disetujui_atasan_oleh',
        'tanggal_persetujuan_atasan',
        'catatan_persetujuan_atasan',
        'hold_started_at',
        'total_hold_seconds',
        'catatan_petugas',
        'file_evidence',
        'file_evidence_name',
        'link_evidence',
        'tanggal_tindak_lanjut',
        'catatan_admin',
        'permohonan_hold',
        'permohonan_perpanjangan',
        'usulan_tanggal_selesai',
        'catatan_permohonan',
        'tanggal_permohonan',
        'status_permohonan',
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
            'perlu_persetujuan_atasan' => 'boolean',
            'tanggal_persetujuan_atasan' => 'datetime',
            'hold_started_at' => 'datetime',
            'total_hold_seconds' => 'integer',
            'tanggal_tindak_lanjut' => 'datetime',
            'permohonan_hold' => 'boolean',
            'permohonan_perpanjangan' => 'boolean',
            'usulan_tanggal_selesai' => 'date',
            'tanggal_permohonan' => 'datetime',
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
     * The supervisor who verified/approved a cross-unit delegation.
     */
    public function atasanPenyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_atasan_oleh');
    }

    /**
     * Determine whether cross-unit delegation requires approval from the target unit's supervisor.
     * Returns the required supervisor role ('Kepala Sub Bagian' or 'Panitera Muda'), or null if not cross-unit.
     */
    public static function requiredCrossUnitApproverRole(User $delegator, User $recipient): ?string
    {
        if ($delegator->jabatan === 'Admin' || $delegator->klasifikasi === 'Pimpinan') {
            return null;
        }

        $delegatorUnit = $delegator->getEffectiveKlasifikasi();
        $recipientUnit = $recipient->klasifikasi;

        if ($delegatorUnit === 'Kesekretariatan' && $recipientUnit === 'Kepaniteraan') {
            return 'Panitera Muda';
        }

        if ($delegatorUnit === 'Kepaniteraan' && $recipientUnit === 'Kesekretariatan') {
            return 'Kepala Sub Bagian';
        }

        return null;
    }

    /**
     * Determine whether this task is awaiting cross-unit supervisor approval.
     */
    public function isAwaitingSupervisorApproval(): bool
    {
        return (bool) $this->perlu_persetujuan_atasan
            && $this->status_persetujuan_atasan === 'Menunggu Persetujuan';
    }

    /**
     * Determine whether the given user can approve/reject this task's cross-unit delegation.
     */
    public function canApproveCrossUnit(User $user): bool
    {
        if (! $this->isAwaitingSupervisorApproval()) {
            return false;
        }

        if ($user->jabatan === 'Admin') {
            return true;
        }

        if ((int) $user->id === (int) $this->admin_id) {
            return false;
        }

        return $user->jabatan === $this->jabatan_atasan_penyetuju;
    }

    /**
     * Additional collaborating officers assigned to this task.
     */
    public function kolaborator(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tugas_petugas', 'tugas_id', 'user_id')
            ->withTimestamps()
            ->orderBy('users.name');
    }

    /**
     * Progress discussion chat messages for this task.
     */
    public function diskusi(): HasMany
    {
        return $this->hasMany(TugasDiskusi::class, 'tugas_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get all assigned officers (primary PIC + collaborating officers) uniquely.
     *
     * @return Collection<int, User>
     */
    public function allPetugas(): Collection
    {
        $collection = collect();

        if ($this->petugas) {
            $collection->push($this->petugas);
        }

        $kolaborators = $this->relationLoaded('kolaborator')
            ? $this->kolaborator
            : $this->kolaborator()->get();

        return $collection->merge($kolaborators)->unique('id')->values();
    }

    /**
     * Determine whether the given user is assigned to this task (either as primary PIC or collaborator).
     */
    public function isAssignedTo(User|int $user): bool
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;

        if ((int) $this->petugas_id === $userId) {
            return true;
        }

        if ($this->relationLoaded('kolaborator')) {
            return $this->kolaborator->contains('id', $userId);
        }

        return $this->kolaborator()->where('users.id', $userId)->exists();
    }

    /**
     * Determine whether the given user can participate in the task's progress chat.
     */
    public function canParticipateInChat(User $user): bool
    {
        return $user->jabatan === 'Admin'
            || (int) $this->admin_id === (int) $user->id
            || $this->isAssignedTo($user);
    }

    /**
     * Determine whether this task is a collaborative multi-petugas task (has at least 1 collaborator).
     */
    public function isCollaborative(): bool
    {
        if ($this->relationLoaded('kolaborator')) {
            return $this->kolaborator->isNotEmpty();
        }

        return $this->kolaborator()->exists();
    }

    /**
     * Determine whether the given officer has contributed at least 1 progress comment, attachment, or submission.
     */
    public function hasOfficerContribution(User|int $user): bool
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;

        $diskusiItems = $this->relationLoaded('diskusi')
            ? $this->diskusi
            : $this->diskusi()->get();

        $hasDiskusiContribution = $diskusiItems->contains(function (TugasDiskusi $chat) use ($userId): bool {
            if ((int) $chat->user_id !== $userId) {
                return false;
            }

            return trim((string) $chat->pesan) !== ''
                || ! empty($chat->file_path)
                || ! empty($chat->link_url);
        });

        if ($hasDiskusiContribution) {
            return true;
        }

        $logItems = $this->relationLoaded('logs')
            ? $this->logs
            : $this->logs()->get();

        return $logItems->contains(fn (TugasLog $log): bool => (int) $log->user_id === $userId && $log->action === 'kirim_balik');
    }

    /**
     * Determine whether this task should be credited toward the given officer's performance matrix & ranking.
     */
    public function countsTowardOfficerPerformance(User|int $user): bool
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;

        if (! $this->isAssignedTo($userId)) {
            return false;
        }

        if ($this->isCollaborative()) {
            return $this->hasOfficerContribution($userId);
        }

        return (int) $this->petugas_id === $userId;
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
    public function recordLog(
        string $action,
        string $status,
        string $keterangan,
        ?string $catatan = null,
        ?int $userId = null,
        ?string $fileUrl = null,
        ?string $fileName = null,
    ): TugasLog {
        return $this->logs()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'status' => $status,
            'keterangan' => $keterangan,
            'catatan' => $catatan,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    /**
     * Put the task into HOLD status and start tracking hold time.
     */
    public function startHold(?Carbon $at = null): void
    {
        $now = $at ?? Carbon::now();

        if ($this->status !== 'HOLD' || $this->hold_started_at === null) {
            $this->hold_started_at = $now;
        }

        $this->status = 'HOLD';
    }

    /**
     * Release the task from HOLD status and accumulate the elapsed hold seconds.
     */
    public function releaseHold(?Carbon $at = null): void
    {
        $now = $at ?? Carbon::now();

        if ($this->hold_started_at !== null) {
            $holdStart = Carbon::parse($this->hold_started_at);
            if ($now->gt($holdStart)) {
                $this->total_hold_seconds = (int) ($this->total_hold_seconds ?? 0) + (int) round($holdStart->floatDiffInSeconds($now));
            }
            $this->hold_started_at = null;
        }
    }

    /**
     * Calculate total seconds the task spent in HOLD status (excluding from working duration & lateness).
     */
    public function getHoldDurationSeconds(?Carbon $until = null): int
    {
        $referenceEnd = $until ?? Carbon::now();

        $dbSeconds = (int) ($this->total_hold_seconds ?? 0);
        if ($this->hold_started_at !== null) {
            $holdStart = Carbon::parse($this->hold_started_at);
            if ($referenceEnd->gt($holdStart)) {
                $dbSeconds += (int) round($holdStart->floatDiffInSeconds($referenceEnd));
            }
        }

        // Also check activity logs if available (supports backdated logs and full audit trail)
        $logSeconds = 0;
        if ($this->relationLoaded('logs') || $this->exists) {
            $logs = $this->relationLoaded('logs') ? $this->logs : $this->logs()->get();
            $currentHoldStart = null;

            foreach ($logs as $log) {
                $isHoldLog = $log->status === 'HOLD' || $log->action === 'hold';
                if ($isHoldLog) {
                    if ($currentHoldStart === null) {
                        $currentHoldStart = Carbon::parse($log->created_at);
                    }
                } elseif ($currentHoldStart !== null) {
                    $logTime = Carbon::parse($log->created_at);
                    if ($logTime->gt($currentHoldStart)) {
                        $logSeconds += (int) round($currentHoldStart->floatDiffInSeconds($logTime));
                    }
                    $currentHoldStart = null;
                }
            }

            if ($currentHoldStart !== null && $this->status === 'HOLD') {
                if ($referenceEnd->gt($currentHoldStart)) {
                    $logSeconds += (int) round($currentHoldStart->floatDiffInSeconds($referenceEnd));
                }
            }
        }

        return max($dbSeconds, $logSeconds);
    }

    /**
     * Get the total hold duration in days (rounded to 1 decimal place).
     */
    public function getHoldDurationDays(?Carbon $until = null): float
    {
        return round($this->getHoldDurationSeconds($until) / 86400, 1);
    }

    /**
     * Calculate effective working duration in days from active working start until completion/closed,
     * excluding any time spent in HOLD status.
     */
    public function getEffectiveWorkingDurationDays(): float
    {
        $start = Carbon::parse($this->tanggal_mulai ?? $this->created_at);
        $finish = Carbon::parse($this->tanggal_tindak_lanjut ?? $this->updated_at);

        $rawDays = max(0.0, $start->floatDiffInDays($finish));
        $holdSeconds = max($this->getHoldDurationSeconds($finish), (int) ($this->total_hold_seconds ?? 0));
        $holdDays = $holdSeconds / 86400;

        $effectiveDays = max(0.0, $rawDays - $holdDays);

        return max(1.0, round($effectiveDays, 1));
    }

    /**
     * Determine whether the task was completed on time, excluding any duration spent in HOLD status.
     */
    public function isCompletedOnTime(): bool
    {
        if (! $this->tanggal_selesai) {
            return true;
        }

        $finish = Carbon::parse($this->tanggal_tindak_lanjut ?? $this->updated_at);
        $deadline = Carbon::parse($this->tanggal_selesai)->endOfDay();

        $holdSeconds = max($this->getHoldDurationSeconds($finish), (int) ($this->total_hold_seconds ?? 0));
        $effectiveFinish = $finish->copy()->subSeconds($holdSeconds);

        return $effectiveFinish->lte($deadline);
    }

    /**
     * Scope query to active tasks whose deadline is between today and N days ahead (0-2 days by default).
     */
    public function scopeApproachingDeadline(Builder $query, int $daysAhead = 2): Builder
    {
        $today = Carbon::today()->format('Y-m-d');
        $limitDate = Carbon::today()->addDays($daysAhead)->format('Y-m-d');

        return $query->whereNotNull('tanggal_selesai')
            ->whereNotIn('status', ['Selesai', 'HOLD', 'Ditolak Atasan', 'Menunggu Persetujuan Atasan'])
            ->whereBetween('tanggal_selesai', [$today, $limitDate]);
    }

    /**
     * Calculate remaining days from today until the task deadline (negative if overdue, 0 if today).
     */
    public function getDaysUntilDeadline(): ?int
    {
        if (! $this->tanggal_selesai) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($this->tanggal_selesai->copy()->startOfDay(), false);
    }
}
