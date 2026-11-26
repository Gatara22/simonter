<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view with performance infographics.
     */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        // 1. Filter Periode & Klasifikasi
        $periode = $request->query('periode', 'semua');
        $periodeLabel = 'Semua Waktu';
        $startDate = null;
        $endDate = null;

        if ($periode === 'bulan_ini') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $periodeLabel = 'Bulan Ini ('.Carbon::now()->translatedFormat('F Y').')';
        } elseif ($periode === '3_bulan') {
            $startDate = Carbon::now()->subMonths(2)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $periodeLabel = '3 Bulan Terakhir';
        } elseif ($periode === 'tahun_ini') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
            $periodeLabel = 'Tahun Ini ('.Carbon::now()->format('Y').')';
        }

        $klasifikasi = $request->query('klasifikasi');
        if (! in_array($klasifikasi, ['Kesekretariatan', 'Kepaniteraan'], true)) {
            $klasifikasi = null;
        }
        $klasifikasiLabel = $klasifikasi ?? 'Semua Klasifikasi';

        // Base query closure with period & classification filtering
        $applyPeriod = function ($query) use ($startDate, $endDate, $klasifikasi): void {
            if ($startDate && $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
            if ($klasifikasi !== null) {
                $query->where('klasifikasi', $klasifikasi);
            }
        };

        if ($isAdmin) {
            // Admin queries: Performance of all officers & delegators
            $tugasQuery = Tugas::query();
            $applyPeriod($tugasQuery);
            $allTugasInPeriod = $tugasQuery->with(['petugas', 'admin', 'kolaborator', 'diskusi', 'logs'])->get();

            $totalTugas = $allTugasInPeriod->count();
            $tugasMenunggu = $allTugasInPeriod->where('status', 'Menunggu Konfirmasi')->count();
            $tugasSelesai = $allTugasInPeriod->where('status', 'Selesai')->count();
            $tugasBerjalan = $allTugasInPeriod->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])->count();
            $tugasPerluRevisi = $allTugasInPeriod->where('status', 'Perlu Revisi')->count();
            $tugasHold = $allTugasInPeriod->where('status', 'HOLD')->count();

            // Calculate duration and on-time statistics for all completed tasks (excluding HOLD duration)
            $completedTasks = $allTugasInPeriod->where('status', 'Selesai');
            $durasiTotalHari = 0;
            $tugasTepatWaktu = 0;
            $tugasTerlambat = 0;

            foreach ($completedTasks as $t) {
                $durasi = $t->getEffectiveWorkingDurationDays();
                $durasiTotalHari += $durasi;

                if ($t->isCompletedOnTime()) {
                    $tugasTepatWaktu++;
                } else {
                    $tugasTerlambat++;
                }
            }

            $avgDurasiHari = $tugasSelesai > 0 ? round($durasiTotalHari / $tugasSelesai, 1) : 0;
            $onTimeRate = $tugasSelesai > 0 ? round(($tugasTepatWaktu / $tugasSelesai) * 100, 1) : 0;

            // Performance Leaderboard per Petugas / Kepala Sub Bagian / Panitera Muda
            $petugasList = User::whereIn('jabatan', ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'])->orderBy('name')->get();
            $petugasStats = [];

            foreach ($petugasList as $p) {
                $tasksOfPetugas = $allTugasInPeriod->filter(fn (Tugas $t): bool => $t->countsTowardOfficerPerformance($p->id));
                $pKolaborasiCount = $tasksOfPetugas->filter(fn (Tugas $t): bool => $t->isCollaborative())->count();
                $pTotal = $tasksOfPetugas->count();
                $pSelesai = $tasksOfPetugas->where('status', 'Selesai');
                $pSelesaiCount = $pSelesai->count();

                $pDurasiTotal = 0.0;
                $pDurasiKerjaTotal = 0.0;
                $pTargetTotal = 0.0;
                $pTargetKerjaTotal = 0.0;
                $pSlaTotal = 0.0;
                $pSlaKerjaTotal = 0.0;
                $pDurasiKalenderList = [];
                $pDurasiKerjaList = [];
                $pTepatWaktu = 0;
                $pTerlambat = 0;
                $pTotalRevisi = 0;
                $pFirstPassCount = 0;
                $pKolaborasiSelesaiCount = 0;
                $pAuditTasks = [];

                foreach ($pSelesai as $t) {
                    $durasiKal = $t->getEffectiveWorkingDurationDays();
                    $durasiKerja = $t->getEffectiveBusinessWorkingDurationDays();
                    $targetKal = $t->getTargetDurationDays(false);
                    $targetKerja = $t->getTargetDurationDays(true);
                    $slaKal = $t->getSlaEfficiencyPercent(false);
                    $slaKerja = $t->getSlaEfficiencyPercent(true);

                    $pDurasiTotal += $durasiKal;
                    $pDurasiKerjaTotal += $durasiKerja;
                    $pTargetTotal += $targetKal;
                    $pTargetKerjaTotal += $targetKerja;
                    $pSlaTotal += $slaKal;
                    $pSlaKerjaTotal += $slaKerja;
                    $pDurasiKalenderList[] = $durasiKal;
                    $pDurasiKerjaList[] = $durasiKerja;

                    if ($t->isCompletedOnTime()) {
                        $pTepatWaktu++;
                    } else {
                        $pTerlambat++;
                    }

                    $pTotalRevisi += $t->getRevisionCount();
                    if ($t->isFirstPassCompleted()) {
                        $pFirstPassCount++;
                    }
                    if ($t->isCollaborative()) {
                        $pKolaborasiSelesaiCount++;
                    }

                    $pAuditTasks[] = $this->formatAuditTaskRow($t);
                }

                $pAvgDurasi = $pSelesaiCount > 0 ? round($pDurasiTotal / $pSelesaiCount, 1) : 0;
                $pAvgDurasiKerja = $pSelesaiCount > 0 ? round($pDurasiKerjaTotal / $pSelesaiCount, 1) : 0;
                $pMedianDurasi = $this->calculateMedian($pDurasiKalenderList);
                $pMedianDurasiKerja = $this->calculateMedian($pDurasiKerjaList);
                $pAvgTargetKalender = $pSelesaiCount > 0 ? round($pTargetTotal / $pSelesaiCount, 1) : null;
                $pAvgTargetKerja = $pSelesaiCount > 0 ? round($pTargetKerjaTotal / $pSelesaiCount, 1) : null;
                $pAvgSlaKalender = $pSelesaiCount > 0 ? round($pSlaTotal / $pSelesaiCount, 1) : null;
                $pAvgSlaKerja = $pSelesaiCount > 0 ? round($pSlaKerjaTotal / $pSelesaiCount, 1) : null;
                $pOnTimeRate = $pSelesaiCount > 0 ? round(($pTepatWaktu / $pSelesaiCount) * 100, 1) : 0;
                $pFirstPassRate = $pSelesaiCount > 0 ? round(($pFirstPassCount / $pSelesaiCount) * 100, 1) : 0.0;

                // Balanced audit-ready score: rewards on-time completion (10), partial credit for late completion (4),
                // timeliness reliability (0.4 * On-Time %), and first-pass quality (0.2 * First-Pass %)
                $score = $pSelesaiCount > 0
                    ? round(($pTepatWaktu * 10) + ($pTerlambat * 4) + ($pOnTimeRate * 0.4) + ($pFirstPassRate * 0.2), 1)
                    : 0.0;

                $predikat = 'Belum Ada Tugas Selesai';
                if ($pSelesaiCount > 0) {
                    if ($pOnTimeRate >= 85 && $pAvgDurasi <= 3) {
                        $predikat = 'Sangat Cepat & Tepat';
                    } elseif ($pOnTimeRate >= 75) {
                        $predikat = 'Tepat Waktu';
                    } elseif ($pOnTimeRate >= 50) {
                        $predikat = 'Cukup Baik';
                    } else {
                        $predikat = 'Perlu Peningkatan';
                    }
                }

                $petugasStats[] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'nip' => $p->nip,
                    'jabatan' => $p->jabatan,
                    'foto_url' => $p->foto_url,
                    'total_tugas' => $pTotal,
                    'kolaborasi_count' => $pKolaborasiCount,
                    'mandiri_selesai' => $pSelesaiCount - $pKolaborasiSelesaiCount,
                    'kolaborasi_selesai' => $pKolaborasiSelesaiCount,
                    'selesai' => $pSelesaiCount,
                    'tepat_waktu' => $pTepatWaktu,
                    'terlambat' => $pTerlambat,
                    'on_time_rate' => $pOnTimeRate,
                    'avg_durasi' => $pAvgDurasi,
                    'avg_durasi_kerja' => $pAvgDurasiKerja,
                    'chart_avg_durasi' => $pSelesaiCount > 0 ? $pAvgDurasi : null,
                    'chart_avg_durasi_kerja' => $pSelesaiCount > 0 ? $pAvgDurasiKerja : null,
                    'median_durasi' => $pMedianDurasi,
                    'median_durasi_kerja' => $pMedianDurasiKerja,
                    'avg_target_kalender' => $pAvgTargetKalender,
                    'avg_target_kerja' => $pAvgTargetKerja,
                    'avg_sla_pct_kalender' => $pAvgSlaKalender,
                    'avg_sla_pct_kerja' => $pAvgSlaKerja,
                    'revisi_total' => $pTotalRevisi,
                    'first_pass_count' => $pFirstPassCount,
                    'first_pass_rate' => $pFirstPassRate,
                    'predikat' => $predikat,
                    'score' => $score,
                    'audit_tasks' => $pAuditTasks,
                ];
            }

            // Sort leaderboard by score descending, then on-time rate, then completed count
            usort($petugasStats, function (array $a, array $b): int {
                return ($b['score'] <=> $a['score'])
                    ?: ($b['on_time_rate'] <=> $a['on_time_rate'])
                    ?: ($b['selesai'] <=> $a['selesai']);
            });

            $topPetugas = ! empty($petugasStats) && $petugasStats[0]['selesai'] > 0 ? $petugasStats[0] : null;

            // Performance Leaderboard per Pemberi Tugas / Pekerjaan (Admin, Kepala Sub Bagian & Panitera Muda)
            $pemberiList = User::whereIn('jabatan', ['Admin', 'Kepala Sub Bagian', 'Panitera Muda'])->orderBy('name')->get();
            $pemberiStats = [];

            foreach ($pemberiList as $pemberi) {
                $delegatedTasks = $allTugasInPeriod->where('admin_id', $pemberi->id);
                $dTotal = $delegatedTasks->count();
                $dMenunggu = $delegatedTasks->where('status', 'Menunggu Konfirmasi')->count();

                $dDiresponCount = 0;
                $dLangsungClosed = 0;
                $dPerluRevisi = 0;
                $dRespondHoursTotal = 0.0;

                foreach ($delegatedTasks as $dt) {
                    $logs = $dt->logs ?? collect();
                    $hasRevisiLog = $logs->contains(fn ($l) => in_array($l->action, ['revisi', 'buka_kembali'], true) || $l->status === 'Perlu Revisi');
                    $hasSelesaiLog = $logs->contains(fn ($l) => $l->action === 'selesai' || $l->status === 'Selesai');

                    if ($dt->status === 'HOLD' && ! $hasRevisiLog && ! $hasSelesaiLog) {
                        continue;
                    }

                    $isResponded = in_array($dt->status, ['Selesai', 'Perlu Revisi'], true) || $hasRevisiLog || $hasSelesaiLog;
                    if (! $isResponded) {
                        continue;
                    }

                    $dDiresponCount++;

                    if ($dt->status === 'Perlu Revisi' || $hasRevisiLog) {
                        $dPerluRevisi++;
                    } elseif ($dt->status === 'Selesai') {
                        $dLangsungClosed++;
                    }

                    // Calculate respond time from task submission (kirim_balik / tanggal_tindak_lanjut) to verification decision (selesai / revisi)
                    $kirimLog = $logs->where('action', 'kirim_balik')->last();
                    $verifLog = $logs->filter(fn ($l) => in_array($l->action, ['selesai', 'revisi'], true))->last();

                    $submitTime = $kirimLog
                        ? Carbon::parse($kirimLog->created_at)
                        : ($dt->tanggal_tindak_lanjut ? Carbon::parse($dt->tanggal_tindak_lanjut) : Carbon::parse($dt->created_at));

                    $decisionTime = $verifLog
                        ? Carbon::parse($verifLog->created_at)
                        : Carbon::parse($dt->updated_at);

                    // Exclude any HOLD hours that occurred between submission and verification decision
                    $holdHoursBetween = 0.0;
                    $holdIntervalStart = null;
                    foreach ($logs as $logEntry) {
                        $isHoldLog = $logEntry->status === 'HOLD' || $logEntry->action === 'hold';
                        if ($isHoldLog) {
                            if ($holdIntervalStart === null) {
                                $holdIntervalStart = Carbon::parse($logEntry->created_at);
                            }
                        } elseif ($holdIntervalStart !== null) {
                            $holdIntervalEnd = Carbon::parse($logEntry->created_at);
                            $overlapStart = $holdIntervalStart->greaterThan($submitTime) ? $holdIntervalStart : $submitTime;
                            $overlapEnd = $holdIntervalEnd->lessThan($decisionTime) ? $holdIntervalEnd : $decisionTime;
                            if ($overlapEnd->gt($overlapStart)) {
                                $holdHoursBetween += $overlapStart->floatDiffInHours($overlapEnd);
                            }
                            $holdIntervalStart = null;
                        }
                    }

                    $rawRespondHours = abs($submitTime->floatDiffInHours($decisionTime));
                    $effectiveRespondHours = max(0.0, $rawRespondHours - $holdHoursBetween);
                    $diffHours = max(0.1, round($effectiveRespondHours, 1));
                    $dRespondHoursTotal += $diffHours;
                }

                $avgRespondJam = $dDiresponCount > 0 ? round($dRespondHoursTotal / $dDiresponCount, 1) : 0.0;
                $avgRespondHari = $dDiresponCount > 0 ? round($avgRespondJam / 24, 1) : 0.0;
                $directCloseRate = $dDiresponCount > 0 ? round(($dLangsungClosed / $dDiresponCount) * 100, 1) : 0.0;
                $revisiRate = $dDiresponCount > 0 ? round(($dPerluRevisi / $dDiresponCount) * 100, 1) : 0.0;

                // Speed bonus: faster verification response time gives higher score
                $speedScore = 0.0;
                if ($dDiresponCount > 0) {
                    if ($avgRespondJam <= 6) {
                        $speedScore = 30.0;
                    } elseif ($avgRespondJam <= 24) {
                        $speedScore = 22.0;
                    } elseif ($avgRespondJam <= 48) {
                        $speedScore = 14.0;
                    } elseif ($avgRespondJam <= 72) {
                        $speedScore = 8.0;
                    } else {
                        $speedScore = 3.0;
                    }
                }

                $pemberiScore = ($dDiresponCount * 10) + ($directCloseRate * 0.3) + $speedScore;

                $predikatPemberi = 'Belum Ada Verifikasi';
                if ($dDiresponCount > 0) {
                    if ($avgRespondJam <= 24) {
                        $predikatPemberi = 'Sangat Responsif';
                    } elseif ($avgRespondJam <= 48) {
                        $predikatPemberi = 'Responsif';
                    } elseif ($avgRespondJam <= 72) {
                        $predikatPemberi = 'Cukup Responsif';
                    } else {
                        $predikatPemberi = 'Perlu Percepatan Verifikasi';
                    }
                }

                if ($avgRespondJam > 0 && $avgRespondJam < 24) {
                    $respondTimeLabel = $avgRespondJam.' Jam';
                } elseif ($avgRespondJam >= 24) {
                    $respondTimeLabel = $avgRespondHari.' Hari ('.$avgRespondJam.' Jam)';
                } else {
                    $respondTimeLabel = '-';
                }

                $pemberiStats[] = [
                    'id' => $pemberi->id,
                    'name' => $pemberi->name,
                    'nip' => $pemberi->nip,
                    'jabatan' => $pemberi->jabatan,
                    'foto_url' => $pemberi->foto_url,
                    'total_delegasi' => $dTotal,
                    'direspon' => $dDiresponCount,
                    'menunggu_verifikasi' => $dMenunggu,
                    'langsung_closed' => $dLangsungClosed,
                    'perlu_revisi' => $dPerluRevisi,
                    'direct_close_rate' => $directCloseRate,
                    'revisi_rate' => $revisiRate,
                    'avg_respond_jam' => $avgRespondJam,
                    'avg_respond_hari' => $avgRespondHari,
                    'respond_label' => $respondTimeLabel,
                    'predikat' => $predikatPemberi,
                    'score' => $pemberiScore,
                ];
            }

            // Sort delegator leaderboard by score descending
            usort($pemberiStats, fn ($a, $b) => $b['score'] <=> $a['score']);

            $topPemberi = ! empty($pemberiStats) && $pemberiStats[0]['direspon'] > 0 ? $pemberiStats[0] : null;

            // Chart 1: Komparasi Kinerja Petugas (Top 7 Aktif & Semua Petugas)
            $activePetugas = array_values(array_filter($petugasStats, fn (array $ps): bool => $ps['selesai'] > 0));
            $topActivePetugas = ! empty($activePetugas)
                ? array_slice($activePetugas, 0, 7)
                : array_slice($petugasStats, 0, 7);

            $buildPetugasChartPayload = function (array $rows): array {
                return [
                    'ids' => array_column($rows, 'id'),
                    'labels' => array_column($rows, 'name'),
                    'selesai' => array_column($rows, 'selesai'),
                    'tepat_waktu' => array_column($rows, 'tepat_waktu'),
                    'terlambat' => array_column($rows, 'terlambat'),
                    'on_time_rate' => array_column($rows, 'on_time_rate'),
                    'first_pass_rate' => array_column($rows, 'first_pass_rate'),
                    'revisi_total' => array_column($rows, 'revisi_total'),
                    'mandiri_selesai' => array_column($rows, 'mandiri_selesai'),
                    'kolaborasi_selesai' => array_column($rows, 'kolaborasi_selesai'),
                    'score' => array_column($rows, 'score'),
                    'durasi_kalender' => array_column($rows, 'chart_avg_durasi'),
                    'durasi_kerja' => array_column($rows, 'chart_avg_durasi_kerja'),
                    'median_kalender' => array_column($rows, 'median_durasi'),
                    'median_kerja' => array_column($rows, 'median_durasi_kerja'),
                    'target_kalender' => array_column($rows, 'avg_target_kalender'),
                    'target_kerja' => array_column($rows, 'avg_target_kerja'),
                    'sla_kalender' => array_column($rows, 'avg_sla_pct_kalender'),
                    'sla_kerja' => array_column($rows, 'avg_sla_pct_kerja'),
                    'tasks' => array_column($rows, 'audit_tasks'),
                ];
            };

            $petugasChartDatasets = [
                'top_aktif' => $buildPetugasChartPayload($topActivePetugas),
                'semua' => $buildPetugasChartPayload($petugasStats),
            ];

            $chartPetugasLabels = $petugasChartDatasets['top_aktif']['labels'];
            $chartPetugasSelesai = $petugasChartDatasets['top_aktif']['selesai'];
            $chartPetugasAvgDurasi = array_column($topActivePetugas, 'avg_durasi');

            // Chart 2: Doughnut Chart Status Distribusi Ketepatan Waktu
            $chartDoughnutLabels = ['Selesai Tepat Waktu', 'Selesai Terlambat', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Perlu Revisi', 'HOLD'];
            $chartDoughnutData = [
                $tugasTepatWaktu,
                $tugasTerlambat,
                $tugasBerjalan,
                $tugasMenunggu,
                $tugasPerluRevisi,
                $tugasHold,
            ];

            // Chart 3: Tren Penyelesaian Tugas & Rata-rata Waktu (berdasarkan tanggal realisasi selesai)
            $oneYearAgo = Carbon::now()->subMonths(11)->startOfMonth();

            $allCompletedYear = Tugas::with(['petugas', 'admin', 'kolaborator', 'logs'])
                ->where('status', 'Selesai')
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->where(function ($q) use ($oneYearAgo): void {
                    $q->where('tanggal_tindak_lanjut', '>=', $oneYearAgo->copy()->subDays(30))
                        ->orWhere('updated_at', '>=', $oneYearAgo->copy()->subDays(30))
                        ->orWhereHas('logs', fn ($lq) => $lq->where('action', 'selesai')->where('created_at', '>=', $oneYearAgo));
                })
                ->get();

            $calcPeriodStats = function ($tasks): array {
                $count = $tasks->count();
                if ($count === 0) {
                    return [
                        'selesai' => 0,
                        'tepat_waktu' => 0,
                        'terlambat' => 0,
                        'on_time_rate' => null,
                        'durasi' => null,
                        'durasi_kerja' => null,
                        'median_durasi' => null,
                        'median_durasi_kerja' => null,
                        'target_durasi' => null,
                        'target_durasi_kerja' => null,
                        'sla_efisiensi' => null,
                        'sla_efisiensi_kerja' => null,
                        'tasks' => [],
                    ];
                }

                $durasiKalList = [];
                $durasiKerjaList = [];
                $targetKalList = [];
                $targetKerjaList = [];
                $slaKalList = [];
                $slaKerjaList = [];
                $tepatWaktu = 0;
                $terlambat = 0;
                $auditRows = [];

                foreach ($tasks as $mt) {
                    $dKal = $mt->getEffectiveWorkingDurationDays();
                    $dKerja = $mt->getEffectiveBusinessWorkingDurationDays();
                    $tKal = $mt->getTargetDurationDays(false);
                    $tKerja = $mt->getTargetDurationDays(true);

                    $durasiKalList[] = $dKal;
                    $durasiKerjaList[] = $dKerja;
                    $targetKalList[] = $tKal;
                    $targetKerjaList[] = $tKerja;
                    $slaKalList[] = $mt->getSlaEfficiencyPercent(false);
                    $slaKerjaList[] = $mt->getSlaEfficiencyPercent(true);

                    if ($mt->isCompletedOnTime()) {
                        $tepatWaktu++;
                    } else {
                        $terlambat++;
                    }

                    $auditRows[] = $this->formatAuditTaskRow($mt);
                }

                return [
                    'selesai' => $count,
                    'tepat_waktu' => $tepatWaktu,
                    'terlambat' => $terlambat,
                    'on_time_rate' => round(($tepatWaktu / $count) * 100, 1),
                    'durasi' => round(array_sum($durasiKalList) / $count, 1),
                    'durasi_kerja' => round(array_sum($durasiKerjaList) / $count, 1),
                    'median_durasi' => $this->calculateMedian($durasiKalList),
                    'median_durasi_kerja' => $this->calculateMedian($durasiKerjaList),
                    'target_durasi' => round(array_sum($targetKalList) / $count, 1),
                    'target_durasi_kerja' => round(array_sum($targetKerjaList) / $count, 1),
                    'sla_efisiensi' => round(array_sum($slaKalList) / $count, 1),
                    'sla_efisiensi_kerja' => round(array_sum($slaKerjaList) / $count, 1),
                    'tasks' => $auditRows,
                ];
            };

            $trendKeys = [
                'selesai',
                'tepat_waktu',
                'terlambat',
                'on_time_rate',
                'durasi',
                'durasi_kerja',
                'median_durasi',
                'median_durasi_kerja',
                'target_durasi',
                'target_durasi_kerja',
                'sla_efisiensi',
                'sla_efisiensi_kerja',
                'tasks',
            ];

            // 1. Data 1 Tahun (12 bulan) berdasarkan tanggal realisasi selesai
            $yearBucket = ['labels' => []];
            foreach ($trendKeys as $tk) {
                $yearBucket[$tk] = [];
            }

            for ($i = 11; $i >= 0; $i--) {
                $monthDate = Carbon::now()->subMonths($i);
                $mStart = $monthDate->copy()->startOfMonth();
                $mEnd = $monthDate->copy()->endOfMonth();

                $mTasks = $allCompletedYear->filter(function (Tugas $t) use ($mStart, $mEnd): bool {
                    return $t->getActualCompletionTime()->between($mStart, $mEnd);
                });

                $stats = $calcPeriodStats($mTasks);
                $yearBucket['labels'][] = $monthDate->translatedFormat('M Y');
                foreach ($trendKeys as $tk) {
                    $yearBucket[$tk][] = $stats[$tk];
                }
            }

            $sliceTrendBucket = function (array $source, int $length) use ($trendKeys): array {
                $sliced = ['labels' => array_slice($source['labels'], -$length)];
                foreach ($trendKeys as $tk) {
                    $sliced[$tk] = array_slice($source[$tk], -$length);
                }

                return $sliced;
            };

            $sixBucket = $sliceTrendBucket($yearBucket, 6);
            $threeBucket = $sliceTrendBucket($yearBucket, 3);

            // 4. Data 1 Bulan (4 pekan kontinyu penuh 30 hari terakhir hingga hari ini)
            $weekRanges = [
                [29, 23],
                [22, 16],
                [15, 8],
                [7, 0],
            ];
            $oneMonthBucket = ['labels' => []];
            foreach ($trendKeys as $tk) {
                $oneMonthBucket[$tk] = [];
            }

            foreach ($weekRanges as [$startOffset, $endOffset]) {
                $wStart = Carbon::now()->subDays($startOffset)->startOfDay();
                $wEnd = Carbon::now()->subDays($endOffset)->endOfDay();

                $wTasks = $allCompletedYear->filter(function (Tugas $t) use ($wStart, $wEnd): bool {
                    return $t->getActualCompletionTime()->between($wStart, $wEnd);
                });

                $stats = $calcPeriodStats($wTasks);
                $oneMonthBucket['labels'][] = $wStart->translatedFormat('d M').' - '.$wEnd->translatedFormat('d M');
                foreach ($trendKeys as $tk) {
                    $oneMonthBucket[$tk][] = $stats[$tk];
                }
            }

            $trendDatasets = [
                '1_tahun' => array_merge(['label' => '1 Tahun', 'subtitle' => '12 Bulan Terakhir (Berdasarkan Realisasi Selesai)'], $yearBucket),
                '6_bulan' => array_merge(['label' => '6 Bulan', 'subtitle' => '6 Bulan Terakhir (Berdasarkan Realisasi Selesai)'], $sixBucket),
                '3_bulan' => array_merge(['label' => '3 Bulan', 'subtitle' => '3 Bulan Terakhir (Berdasarkan Realisasi Selesai)'], $threeBucket),
                '1_bulan' => array_merge(['label' => '1 Bulan', 'subtitle' => '30 Hari Terakhir (4 Pekan Kontinyu)'], $oneMonthBucket),
            ];

            // For default display and backwards compatibility
            $trendLabels = $sixBucket['labels'];
            $trendSelesaiData = $sixBucket['selesai'];
            $trendAvgDurasiData = $sixBucket['durasi'];

            $recentTugas = Tugas::with(['petugas', 'admin', 'logs'])
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->latest()
                ->take(5)
                ->get();

            $pendingPermohonanCount = Tugas::where('status_permohonan', 'Menunggu Persetujuan')
                ->where('status', '!=', 'Selesai')
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->count();

            $tugasMendekatiDeadline = Tugas::with(['petugas', 'admin', 'kolaborator'])
                ->approachingDeadline(2)
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->orderBy('tanggal_selesai', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
            $tugasMendekatiDeadlineCount = $tugasMendekatiDeadline->count();

            $data = [
                'title' => 'Dashboard Pimpinan & Administrator',
                'menuDashboard' => 'active',
                'isAdmin' => true,
                'isKasubbag' => false,
                'periode' => $periode,
                'periodeLabel' => $periodeLabel,
                'klasifikasi' => $klasifikasi,
                'klasifikasiLabel' => $klasifikasiLabel,
                'pendingPermohonanCount' => $pendingPermohonanCount,
                'tugasMendekatiDeadline' => $tugasMendekatiDeadline,
                'tugasMendekatiDeadlineCount' => $tugasMendekatiDeadlineCount,
                'totalUser' => User::count(),
                'totalPetugas' => $petugasList->count(),
                'totalTugas' => $totalTugas,
                'tugasMenunggu' => $tugasMenunggu,
                'tugasSelesai' => $tugasSelesai,
                'tugasBerjalan' => $tugasBerjalan,
                'tugasPerluRevisi' => $tugasPerluRevisi,
                'tugasHold' => $tugasHold,
                'tugasTepatWaktu' => $tugasTepatWaktu,
                'tugasTerlambat' => $tugasTerlambat,
                'onTimeRate' => $onTimeRate,
                'avgDurasiHari' => $avgDurasiHari,
                'topPetugas' => $topPetugas,
                'petugasStats' => $petugasStats,
                'topPemberi' => $topPemberi,
                'pemberiStats' => $pemberiStats,
                'chartPetugasLabels' => $chartPetugasLabels,
                'chartPetugasSelesai' => $chartPetugasSelesai,
                'chartPetugasAvgDurasi' => $chartPetugasAvgDurasi,
                'petugasChartDatasets' => $petugasChartDatasets,
                'chartDoughnutLabels' => $chartDoughnutLabels,
                'chartDoughnutData' => $chartDoughnutData,
                'trendLabels' => $trendLabels,
                'trendSelesaiData' => $trendSelesaiData,
                'trendAvgDurasiData' => $trendAvgDurasiData,
                'trendDatasets' => $trendDatasets,
                'recentTugas' => $recentTugas,
            ];
        } else {
            // Kepala Sub Bagian or Petugas queries
            $scopeCallback = function ($q) use ($currentUser, $isKasubbag): void {
                if ($isKasubbag) {
                    $q->where('petugas_id', $currentUser->id)
                        ->orWhere('admin_id', $currentUser->id)
                        ->orWhereHas('kolaborator', fn ($k) => $k->where('users.id', $currentUser->id));
                } else {
                    $q->where('petugas_id', $currentUser->id)
                        ->orWhereHas('kolaborator', fn ($k) => $k->where('users.id', $currentUser->id));
                }
            };

            $myTugasQuery = Tugas::where($scopeCallback);
            $applyPeriod($myTugasQuery);
            $myTugasInPeriod = $myTugasQuery->with(['admin', 'petugas', 'kolaborator', 'diskusi', 'logs'])->get()
                ->filter(function (Tugas $t) use ($currentUser, $isKasubbag): bool {
                    if ($isKasubbag && (int) $t->admin_id === (int) $currentUser->id) {
                        return true;
                    }

                    return $t->countsTowardOfficerPerformance($currentUser->id);
                });

            $totalTugas = $myTugasInPeriod->count();
            $tugasMenunggu = $myTugasInPeriod->where('status', 'Menunggu Konfirmasi')->count();
            $tugasSelesai = $myTugasInPeriod->where('status', 'Selesai')->count();
            $tugasBerjalan = $myTugasInPeriod->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])->count();
            $tugasPerluRevisi = $myTugasInPeriod->where('status', 'Perlu Revisi')->count();
            $tugasHold = $myTugasInPeriod->where('status', 'HOLD')->count();

            // Personal turnaround time & on-time stats (excluding HOLD duration)
            $completedTasks = $myTugasInPeriod->where('status', 'Selesai');
            $durasiTotalHari = 0;
            $tugasTepatWaktu = 0;
            $tugasTerlambat = 0;

            foreach ($completedTasks as $t) {
                $durasi = $t->getEffectiveWorkingDurationDays();
                $durasiTotalHari += $durasi;

                if ($t->isCompletedOnTime()) {
                    $tugasTepatWaktu++;
                } else {
                    $tugasTerlambat++;
                }
            }

            $avgDurasiHari = $tugasSelesai > 0 ? round($durasiTotalHari / $tugasSelesai, 1) : 0;
            $onTimeRate = $tugasSelesai > 0 ? round(($tugasTepatWaktu / $tugasSelesai) * 100, 1) : 0;

            // Predikat Kinerja Pribadi
            $predikatKinerja = 'Belum Ada Tugas Selesai';
            if ($tugasSelesai > 0) {
                if ($onTimeRate >= 90 && $avgDurasiHari <= 3) {
                    $predikatKinerja = 'Sangat Memuaskan';
                } elseif ($onTimeRate >= 75) {
                    $predikatKinerja = 'Baik & Disiplin';
                } elseif ($onTimeRate >= 50) {
                    $predikatKinerja = 'Cukup Baik';
                } else {
                    $predikatKinerja = 'Perlu Peningkatan Disiplin Waktu';
                }
            }

            // Chart 1: Doughnut Chart Status Tugas
            $chartDoughnutLabels = ['Selesai Tepat Waktu', 'Selesai Terlambat', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Perlu Revisi', 'HOLD'];
            $chartDoughnutData = [
                $tugasTepatWaktu,
                $tugasTerlambat,
                $tugasBerjalan,
                $tugasMenunggu,
                $tugasPerluRevisi,
                $tugasHold,
            ];

            // Chart 2: Recent Completed Tasks Durasi vs Target (Hari)
            $recentCompleted = Tugas::with(['kolaborator', 'diskusi', 'logs'])
                ->where($scopeCallback)
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->where('status', 'Selesai')
                ->latest('tanggal_tindak_lanjut')
                ->take(12)
                ->get()
                ->filter(function (Tugas $t) use ($currentUser, $isKasubbag): bool {
                    if ($isKasubbag && (int) $t->admin_id === (int) $currentUser->id) {
                        return true;
                    }

                    return $t->countsTowardOfficerPerformance($currentUser->id);
                })
                ->take(6)
                ->reverse();

            $myTaskLabels = [];
            $myTaskActualDurasi = [];
            $myTaskTargetDurasi = [];

            foreach ($recentCompleted as $rt) {
                $start = Carbon::parse($rt->tanggal_mulai ?? $rt->created_at);
                $target = Carbon::parse($rt->tanggal_selesai);

                $myTaskLabels[] = strlen($rt->judul) > 18 ? substr($rt->judul, 0, 18).'...' : $rt->judul;
                $myTaskActualDurasi[] = $rt->getEffectiveWorkingDurationDays();
                $myTaskTargetDurasi[] = max(1, round($start->floatDiffInDays($target), 1));
            }

            $recentTugas = Tugas::with(['admin', 'petugas', 'logs'])
                ->where($scopeCallback)
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->latest()
                ->take(5)
                ->get();

            $pendingPermohonanCount = $isKasubbag
                ? Tugas::where('admin_id', $currentUser->id)
                    ->where('status_permohonan', 'Menunggu Persetujuan')
                    ->where('status', '!=', 'Selesai')
                    ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                    ->count()
                : 0;

            $tugasMendekatiDeadline = Tugas::with(['admin', 'petugas', 'kolaborator'])
                ->where($scopeCallback)
                ->approachingDeadline(2)
                ->when($klasifikasi !== null, fn ($q) => $q->where('klasifikasi', $klasifikasi))
                ->orderBy('tanggal_selesai', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
            $tugasMendekatiDeadlineCount = $tugasMendekatiDeadline->count();

            $data = [
                'title' => $isKasubbag ? 'Dashboard '.$currentUser->jabatan : 'Dashboard Kinerja Petugas',
                'menuDashboard' => 'active',
                'isAdmin' => false,
                'isKasubbag' => $isKasubbag,
                'periode' => $periode,
                'periodeLabel' => $periodeLabel,
                'klasifikasi' => $klasifikasi,
                'klasifikasiLabel' => $klasifikasiLabel,
                'pendingPermohonanCount' => $pendingPermohonanCount,
                'tugasMendekatiDeadline' => $tugasMendekatiDeadline,
                'tugasMendekatiDeadlineCount' => $tugasMendekatiDeadlineCount,
                'totalTugas' => $totalTugas,
                'tugasMenunggu' => $tugasMenunggu,
                'tugasSelesai' => $tugasSelesai,
                'tugasBerjalan' => $tugasBerjalan,
                'tugasPerluRevisi' => $tugasPerluRevisi,
                'tugasHold' => $tugasHold,
                'tugasTepatWaktu' => $tugasTepatWaktu,
                'tugasTerlambat' => $tugasTerlambat,
                'onTimeRate' => $onTimeRate,
                'avgDurasiHari' => $avgDurasiHari,
                'predikatKinerja' => $predikatKinerja,
                'chartDoughnutLabels' => $chartDoughnutLabels,
                'chartDoughnutData' => $chartDoughnutData,
                'myTaskLabels' => $myTaskLabels,
                'myTaskActualDurasi' => $myTaskActualDurasi,
                'myTaskTargetDurasi' => $myTaskTargetDurasi,
                'recentTugas' => $recentTugas,
            ];
        }

        return view('dashboard', $data);
    }

    /**
     * Calculate exact median from an array of numeric values (returns null when empty).
     *
     * @param  array<int, float|int>  $values
     */
    private function calculateMedian(array $values): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }

        sort($values, SORT_NUMERIC);
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return round((float) $values[$middle], 1);
        }

        return round(((float) $values[$middle - 1] + (float) $values[$middle]) / 2, 1);
    }

    /**
     * Format a completed task into a structured audit payload for drill-down chart modals.
     *
     * @return array{
     *     id: int,
     *     judul: string,
     *     klasifikasi: string,
     *     petugas_name: string,
     *     admin_name: string,
     *     is_kolaborasi: bool,
     *     tanggal_mulai: string,
     *     tanggal_deadline: string,
     *     tanggal_selesai_aktual: string,
     *     hold_days: float,
     *     durasi_kalender: float,
     *     durasi_kerja: float,
     *     target_kalender: float,
     *     target_kerja: float,
     *     sla_pct_kalender: float,
     *     sla_pct_kerja: float,
     *     revisi_count: int,
     *     tepat_waktu: bool,
     *     url: string
     * }
     */
    private function formatAuditTaskRow(Tugas $task): array
    {
        $completionTime = $task->getActualCompletionTime();
        $finishForHold = Carbon::parse($task->tanggal_tindak_lanjut ?? $task->updated_at ?? $completionTime);

        return [
            'id' => $task->id,
            'judul' => $task->judul,
            'klasifikasi' => $task->klasifikasi ?? 'Kesekretariatan',
            'petugas_name' => $task->petugas->name ?? '-',
            'admin_name' => $task->admin->name ?? 'Admin',
            'is_kolaborasi' => $task->isCollaborative(),
            'tanggal_mulai' => $task->tanggal_mulai ? $task->tanggal_mulai->format('d/m/Y') : ($task->created_at ? $task->created_at->format('d/m/Y') : '-'),
            'tanggal_deadline' => $task->tanggal_selesai ? $task->tanggal_selesai->format('d/m/Y') : '-',
            'tanggal_selesai_aktual' => $completionTime->format('d/m/Y H:i'),
            'hold_days' => $task->getHoldDurationDays($finishForHold),
            'durasi_kalender' => $task->getEffectiveWorkingDurationDays(),
            'durasi_kerja' => $task->getEffectiveBusinessWorkingDurationDays(),
            'target_kalender' => $task->getTargetDurationDays(false),
            'target_kerja' => $task->getTargetDurationDays(true),
            'sla_pct_kalender' => $task->getSlaEfficiencyPercent(false),
            'sla_pct_kerja' => $task->getSlaEfficiencyPercent(true),
            'revisi_count' => $task->getRevisionCount(),
            'tepat_waktu' => $task->isCompletedOnTime(),
            'url' => route('tugas.show', $task->id),
        ];
    }
}
