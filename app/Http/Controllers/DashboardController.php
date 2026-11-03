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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        // 1. Filter Periode
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

        // Base query closure with period filtering
        $applyPeriod = function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
        };

        if ($isAdmin) {
            // Admin queries: Performance of all officers
            $tugasQuery = Tugas::query();
            $applyPeriod($tugasQuery);
            $allTugasInPeriod = $tugasQuery->with(['petugas', 'admin'])->get();

            $totalTugas = $allTugasInPeriod->count();
            $tugasMenunggu = $allTugasInPeriod->where('status', 'Menunggu Konfirmasi')->count();
            $tugasSelesai = $allTugasInPeriod->where('status', 'Selesai')->count();
            $tugasBerjalan = $allTugasInPeriod->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])->count();
            $tugasPerluRevisi = $allTugasInPeriod->where('status', 'Perlu Revisi')->count();

            // Calculate duration and on-time statistics for all completed tasks
            $completedTasks = $allTugasInPeriod->where('status', 'Selesai');
            $durasiTotalHari = 0;
            $tugasTepatWaktu = 0;
            $tugasTerlambat = 0;

            foreach ($completedTasks as $t) {
                $start = Carbon::parse($t->tanggal_mulai ?? $t->created_at);
                $finish = Carbon::parse($t->tanggal_tindak_lanjut ?? $t->updated_at);
                $durasi = max(1, round($start->floatDiffInDays($finish), 1));
                $durasiTotalHari += $durasi;

                $deadline = Carbon::parse($t->tanggal_selesai)->endOfDay();
                if ($finish->lte($deadline)) {
                    $tugasTepatWaktu++;
                } else {
                    $tugasTerlambat++;
                }
            }

            $avgDurasiHari = $tugasSelesai > 0 ? round($durasiTotalHari / $tugasSelesai, 1) : 0;
            $onTimeRate = $tugasSelesai > 0 ? round(($tugasTepatWaktu / $tugasSelesai) * 100, 1) : 0;

            // Performance Leaderboard per Petugas / Kepala Sub Bagian
            $petugasList = User::whereIn('jabatan', ['Kepala Sub Bagian', 'Petugas'])->orderBy('name')->get();
            $petugasStats = [];

            foreach ($petugasList as $p) {
                $tasksOfPetugas = $allTugasInPeriod->where('petugas_id', $p->id);
                $pTotal = $tasksOfPetugas->count();
                $pSelesai = $tasksOfPetugas->where('status', 'Selesai');
                $pSelesaiCount = $pSelesai->count();

                $pDurasiTotal = 0;
                $pTepatWaktu = 0;
                $pTerlambat = 0;

                foreach ($pSelesai as $t) {
                    $start = Carbon::parse($t->tanggal_mulai ?? $t->created_at);
                    $finish = Carbon::parse($t->tanggal_tindak_lanjut ?? $t->updated_at);
                    $durasi = max(1, round($start->floatDiffInDays($finish), 1));
                    $pDurasiTotal += $durasi;

                    $deadline = Carbon::parse($t->tanggal_selesai)->endOfDay();
                    if ($finish->lte($deadline)) {
                        $pTepatWaktu++;
                    } else {
                        $pTerlambat++;
                    }
                }

                $pAvgDurasi = $pSelesaiCount > 0 ? round($pDurasiTotal / $pSelesaiCount, 1) : 0;
                $pOnTimeRate = $pSelesaiCount > 0 ? round(($pTepatWaktu / $pSelesaiCount) * 100, 1) : 0;

                // Score for ranking: completed count weighted (10 pts) + on time rate (0.5 pts)
                $score = ($pSelesaiCount * 10) + ($pOnTimeRate * 0.5);

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
                    'selesai' => $pSelesaiCount,
                    'tepat_waktu' => $pTepatWaktu,
                    'terlambat' => $pTerlambat,
                    'on_time_rate' => $pOnTimeRate,
                    'avg_durasi' => $pAvgDurasi,
                    'predikat' => $predikat,
                    'score' => $score,
                ];
            }

            // Sort leaderboard by score descending
            usort($petugasStats, fn ($a, $b) => $b['score'] <=> $a['score']);

            $topPetugas = ! empty($petugasStats) && $petugasStats[0]['selesai'] > 0 ? $petugasStats[0] : null;

            // Chart 1: Bar Chart per Petugas (Top 7)
            $top7Petugas = array_slice($petugasStats, 0, 7);
            $chartPetugasLabels = array_column($top7Petugas, 'name');
            $chartPetugasSelesai = array_column($top7Petugas, 'selesai');
            $chartPetugasAvgDurasi = array_column($top7Petugas, 'avg_durasi');

            // Chart 2: Doughnut Chart Status Distribusi Ketepatan Waktu
            $chartDoughnutLabels = ['Selesai Tepat Waktu', 'Selesai Terlambat', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Perlu Revisi'];
            $chartDoughnutData = [
                $tugasTepatWaktu,
                $tugasTerlambat,
                $tugasBerjalan,
                $tugasMenunggu,
                $tugasPerluRevisi,
            ];

            // Chart 3: Tren Penyelesaian Tugas & Rata-rata Waktu (1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan)
            $oneYearAgo = Carbon::now()->subMonths(11)->startOfMonth();
            $nowEnd = Carbon::now()->endOfMonth();

            $allCompletedYear = Tugas::where('status', 'Selesai')
                ->where(function ($q) use ($oneYearAgo, $nowEnd) {
                    $q->whereBetween('tanggal_selesai', [$oneYearAgo->format('Y-m-d'), $nowEnd->format('Y-m-d')])
                        ->orWhere(function ($q2) use ($oneYearAgo, $nowEnd) {
                            $q2->whereNull('tanggal_selesai')
                                ->whereBetween('created_at', [$oneYearAgo, $nowEnd]);
                        });
                })
                ->get();

            $calcPeriodStats = function ($tasks) {
                $count = $tasks->count();
                $durasiTotal = 0;
                foreach ($tasks as $mt) {
                    $st = Carbon::parse($mt->tanggal_mulai ?? $mt->created_at);
                    $fn = Carbon::parse($mt->tanggal_tindak_lanjut ?? $mt->updated_at);
                    $durasiTotal += max(1, round($st->floatDiffInDays($fn), 1));
                }
                $avg = $count > 0 ? round($durasiTotal / $count, 1) : 0;

                return [$count, $avg];
            };

            // 1. Data 1 Tahun (12 bulan)
            $yearLabels = [];
            $yearSelesai = [];
            $yearDurasi = [];
            for ($i = 11; $i >= 0; $i--) {
                $monthDate = Carbon::now()->subMonths($i);
                $mStart = $monthDate->copy()->startOfMonth()->format('Y-m-d');
                $mEnd = $monthDate->copy()->endOfMonth()->format('Y-m-d');

                $mTasks = $allCompletedYear->filter(function ($t) use ($mStart, $mEnd) {
                    $date = $t->tanggal_selesai ? Carbon::parse($t->tanggal_selesai)->format('Y-m-d') : Carbon::parse($t->created_at)->format('Y-m-d');

                    return $date >= $mStart && $date <= $mEnd;
                });

                [$mCount, $mAvg] = $calcPeriodStats($mTasks);
                $yearLabels[] = $monthDate->translatedFormat('M Y');
                $yearSelesai[] = $mCount;
                $yearDurasi[] = $mAvg;
            }

            // 2. Data 6 Bulan (6 bulan terakhir)
            $sixLabels = array_slice($yearLabels, -6);
            $sixSelesai = array_slice($yearSelesai, -6);
            $sixDurasi = array_slice($yearDurasi, -6);

            // 3. Data 3 Bulan (3 bulan terakhir)
            $threeLabels = array_slice($yearLabels, -3);
            $threeSelesai = array_slice($yearSelesai, -3);
            $threeDurasi = array_slice($yearDurasi, -3);

            // 4. Data 1 Bulan (4 pekan terakhir dalam 28 hari)
            $oneMonthLabels = [];
            $oneMonthSelesai = [];
            $oneMonthDurasi = [];
            for ($w = 3; $w >= 0; $w--) {
                $wStart = Carbon::now()->subDays(($w * 7) + 6)->startOfDay();
                $wEnd = Carbon::now()->subDays($w * 7)->endOfDay();

                $wTasks = $allCompletedYear->filter(function ($t) use ($wStart, $wEnd) {
                    $d = Carbon::parse($t->tanggal_selesai ?? $t->created_at);

                    return $d->between($wStart, $wEnd);
                });

                [$wCount, $wAvg] = $calcPeriodStats($wTasks);
                $oneMonthLabels[] = $wStart->translatedFormat('d M').' - '.$wEnd->translatedFormat('d M');
                $oneMonthSelesai[] = $wCount;
                $oneMonthDurasi[] = $wAvg;
            }

            $trendDatasets = [
                '1_tahun' => [
                    'label' => '1 Tahun',
                    'subtitle' => '12 Bulan Terakhir',
                    'labels' => $yearLabels,
                    'selesai' => $yearSelesai,
                    'durasi' => $yearDurasi,
                ],
                '6_bulan' => [
                    'label' => '6 Bulan',
                    'subtitle' => '6 Bulan Terakhir',
                    'labels' => $sixLabels,
                    'selesai' => $sixSelesai,
                    'durasi' => $sixDurasi,
                ],
                '3_bulan' => [
                    'label' => '3 Bulan',
                    'subtitle' => '3 Bulan Terakhir',
                    'labels' => $threeLabels,
                    'selesai' => $threeSelesai,
                    'durasi' => $threeDurasi,
                ],
                '1_bulan' => [
                    'label' => '1 Bulan',
                    'subtitle' => '4 Pekan Terakhir (28 Hari)',
                    'labels' => $oneMonthLabels,
                    'selesai' => $oneMonthSelesai,
                    'durasi' => $oneMonthDurasi,
                ],
            ];

            // For default display and backwards compatibility
            $trendLabels = $sixLabels;
            $trendSelesaiData = $sixSelesai;
            $trendAvgDurasiData = $sixDurasi;

            $recentTugas = Tugas::with(['petugas'])->latest()->take(5)->get();

            $data = [
                'title' => 'Dashboard Pimpinan & Administrator',
                'menuDashboard' => 'active',
                'isAdmin' => true,
                'isKasubbag' => false,
                'periode' => $periode,
                'periodeLabel' => $periodeLabel,
                'totalUser' => User::count(),
                'totalPetugas' => $petugasList->count(),
                'totalTugas' => $totalTugas,
                'tugasMenunggu' => $tugasMenunggu,
                'tugasSelesai' => $tugasSelesai,
                'tugasBerjalan' => $tugasBerjalan,
                'tugasPerluRevisi' => $tugasPerluRevisi,
                'tugasTepatWaktu' => $tugasTepatWaktu,
                'tugasTerlambat' => $tugasTerlambat,
                'onTimeRate' => $onTimeRate,
                'avgDurasiHari' => $avgDurasiHari,
                'topPetugas' => $topPetugas,
                'petugasStats' => $petugasStats,
                'chartPetugasLabels' => $chartPetugasLabels,
                'chartPetugasSelesai' => $chartPetugasSelesai,
                'chartPetugasAvgDurasi' => $chartPetugasAvgDurasi,
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
            $scopeCallback = function ($q) use ($currentUser, $isKasubbag) {
                if ($isKasubbag) {
                    $q->where('petugas_id', $currentUser->id)
                        ->orWhere('admin_id', $currentUser->id);
                } else {
                    $q->where('petugas_id', $currentUser->id);
                }
            };

            $myTugasQuery = Tugas::where($scopeCallback);
            $applyPeriod($myTugasQuery);
            $myTugasInPeriod = $myTugasQuery->with(['admin', 'petugas'])->get();

            $totalTugas = $myTugasInPeriod->count();
            $tugasMenunggu = $myTugasInPeriod->where('status', 'Menunggu Konfirmasi')->count();
            $tugasSelesai = $myTugasInPeriod->where('status', 'Selesai')->count();
            $tugasBerjalan = $myTugasInPeriod->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])->count();
            $tugasPerluRevisi = $myTugasInPeriod->where('status', 'Perlu Revisi')->count();

            // Personal turnaround time & on-time stats
            $completedTasks = $myTugasInPeriod->where('status', 'Selesai');
            $durasiTotalHari = 0;
            $tugasTepatWaktu = 0;
            $tugasTerlambat = 0;

            foreach ($completedTasks as $t) {
                $start = Carbon::parse($t->tanggal_mulai ?? $t->created_at);
                $finish = Carbon::parse($t->tanggal_tindak_lanjut ?? $t->updated_at);
                $durasi = max(1, round($start->floatDiffInDays($finish), 1));
                $durasiTotalHari += $durasi;

                $deadline = Carbon::parse($t->tanggal_selesai)->endOfDay();
                if ($finish->lte($deadline)) {
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
            $chartDoughnutLabels = ['Selesai Tepat Waktu', 'Selesai Terlambat', 'Sedang Dikerjakan', 'Menunggu Konfirmasi', 'Perlu Revisi'];
            $chartDoughnutData = [
                $tugasTepatWaktu,
                $tugasTerlambat,
                $tugasBerjalan,
                $tugasMenunggu,
                $tugasPerluRevisi,
            ];

            // Chart 2: Recent Completed Tasks Durasi vs Target (Hari)
            $recentCompleted = Tugas::where($scopeCallback)
                ->where('status', 'Selesai')
                ->latest('tanggal_tindak_lanjut')
                ->take(6)
                ->get()
                ->reverse();

            $myTaskLabels = [];
            $myTaskActualDurasi = [];
            $myTaskTargetDurasi = [];

            foreach ($recentCompleted as $rt) {
                $start = Carbon::parse($rt->tanggal_mulai ?? $rt->created_at);
                $finish = Carbon::parse($rt->tanggal_tindak_lanjut ?? $rt->updated_at);
                $target = Carbon::parse($rt->tanggal_selesai);

                $myTaskLabels[] = strlen($rt->judul) > 18 ? substr($rt->judul, 0, 18).'...' : $rt->judul;
                $myTaskActualDurasi[] = max(1, round($start->floatDiffInDays($finish), 1));
                $myTaskTargetDurasi[] = max(1, round($start->floatDiffInDays($target), 1));
            }

            $recentTugas = Tugas::with(['admin', 'petugas'])->where($scopeCallback)->latest()->take(5)->get();

            $data = [
                'title' => $isKasubbag ? 'Dashboard Kepala Sub Bagian' : 'Dashboard Kinerja Petugas',
                'menuDashboard' => 'active',
                'isAdmin' => false,
                'isKasubbag' => $isKasubbag,
                'periode' => $periode,
                'periodeLabel' => $periodeLabel,
                'totalTugas' => $totalTugas,
                'tugasMenunggu' => $tugasMenunggu,
                'tugasSelesai' => $tugasSelesai,
                'tugasBerjalan' => $tugasBerjalan,
                'tugasPerluRevisi' => $tugasPerluRevisi,
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
}
