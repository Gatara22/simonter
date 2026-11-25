<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\TugasDiskusi;
use App\Models\User;
use App\Notifications\TugasNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TugasController extends Controller
{
    /**
     * Display a listing of tasks (for admin, kepala sub bagian, or petugas).
     */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);
        $canDelegate = $isAdmin || $isKasubbag;

        $activeKlasifikasi = $request->query('klasifikasi');
        if (! in_array($activeKlasifikasi, ['Kesekretariatan', 'Kepaniteraan'], true)) {
            $activeKlasifikasi = null;
        }

        if ($isAdmin || $isKasubbag) {
            $tugasQuery = Tugas::with(['admin', 'petugas', 'kolaborator', 'atasanPenyetuju', 'logs'])->latest();

            $totalTugas = Tugas::count();
            $totalKesekretariatan = Tugas::where('klasifikasi', 'Kesekretariatan')->count();
            $totalKepaniteraan = Tugas::where('klasifikasi', 'Kepaniteraan')->count();
            $totalDidelegasikan = Tugas::whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])->count();
            $totalMenungguKonfirmasi = Tugas::where('status', 'Menunggu Konfirmasi')->count();
            $totalSelesai = Tugas::where('status', 'Selesai')->count();
            $totalHold = Tugas::where('status', 'HOLD')->count();
            $totalRevisi = Tugas::where('status', 'Perlu Revisi')->count();
            $pendingPermohonan = Tugas::where('status_permohonan', 'Menunggu Persetujuan')
                ->where('status', '!=', 'Selesai')
                ->when(! $isAdmin, fn ($q) => $q->where('admin_id', $currentUser->id))
                ->count();
            $pendingPersetujuanAtasan = Tugas::where('perlu_persetujuan_atasan', true)
                ->where('status_persetujuan_atasan', 'Menunggu Persetujuan')
                ->when(! $isAdmin, function ($q) use ($currentUser): void {
                    $q->where('jabatan_atasan_penyetuju', $currentUser->jabatan)
                        ->where('admin_id', '!=', $currentUser->id);
                })
                ->count();
            $totalMendekatiDeadline = Tugas::approachingDeadline(2)->count();
        } else {
            $assignedScope = function ($query) use ($currentUser): void {
                $query->where('petugas_id', $currentUser->id)
                    ->orWhereHas('kolaborator', fn ($k) => $k->where('users.id', $currentUser->id));
            };

            $tugasQuery = Tugas::with(['admin', 'petugas', 'kolaborator', 'atasanPenyetuju', 'logs'])
                ->where($assignedScope)
                ->whereNotIn('status', ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'])
                ->latest();

            $totalTugas = Tugas::where($assignedScope)
                ->whereNotIn('status', ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'])
                ->count();
            $totalKesekretariatan = Tugas::where($assignedScope)
                ->whereNotIn('status', ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'])
                ->where('klasifikasi', 'Kesekretariatan')
                ->count();
            $totalKepaniteraan = Tugas::where($assignedScope)
                ->whereNotIn('status', ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'])
                ->where('klasifikasi', 'Kepaniteraan')
                ->count();
            $totalDidelegasikan = Tugas::where($assignedScope)
                ->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])
                ->count();
            $totalMenungguKonfirmasi = Tugas::where($assignedScope)
                ->where('status', 'Menunggu Konfirmasi')
                ->count();
            $totalSelesai = Tugas::where($assignedScope)
                ->where('status', 'Selesai')
                ->count();
            $totalHold = Tugas::where($assignedScope)
                ->where('status', 'HOLD')
                ->count();
            $totalRevisi = Tugas::where($assignedScope)
                ->where('status', 'Perlu Revisi')
                ->count();
            $pendingPermohonan = Tugas::where($assignedScope)
                ->where('status_permohonan', 'Menunggu Persetujuan')
                ->where('status', '!=', 'Selesai')
                ->count();
            $pendingPersetujuanAtasan = 0;
            $totalMendekatiDeadline = Tugas::where($assignedScope)->approachingDeadline(2)->count();
        }

        // Optional klasifikasi filter
        if ($activeKlasifikasi !== null) {
            $tugasQuery->where('klasifikasi', $activeKlasifikasi);
        }

        // Optional status filter (supports 'berjalan' shorthand as well as exact statuses)
        $activeStatus = $request->query('status');
        if ($activeStatus === 'berjalan') {
            $tugasQuery->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan']);
        } elseif ($request->filled('status')) {
            $tugasQuery->where('status', $activeStatus);
        }

        // Optional pending permohonan filter
        $activePermohonan = $request->query('permohonan');
        if ($activePermohonan === 'menunggu') {
            $tugasQuery->where('status_permohonan', 'Menunggu Persetujuan')
                ->where('status', '!=', 'Selesai');
            if ($isKasubbag && ! $isAdmin) {
                $tugasQuery->where('admin_id', $currentUser->id);
            }
        }

        // Optional pending cross-unit supervisor approval filter
        $activePersetujuanAtasan = $request->query('persetujuan_atasan');
        if ($activePersetujuanAtasan === 'menunggu') {
            $tugasQuery->where('perlu_persetujuan_atasan', true)
                ->where('status_persetujuan_atasan', 'Menunggu Persetujuan');
            if ($isKasubbag && ! $isAdmin) {
                $tugasQuery->where('jabatan_atasan_penyetuju', $currentUser->jabatan)
                    ->where('admin_id', '!=', $currentUser->id);
            }
        }

        // Optional approaching-deadline filter (0-2 days ahead)
        $activeDeadline = $request->query('deadline');
        if ($activeDeadline === 'mendekati') {
            $tugasQuery->approachingDeadline(2)
                ->reorder('tanggal_selesai', 'asc')
                ->orderBy('created_at', 'desc');
        }

        $title = 'Data Tugas Saya';
        if ($isAdmin) {
            $title = 'Data Tugas (Delegasi Admin)';
        } elseif ($isKasubbag) {
            $title = 'Data Tugas & Delegasi ('.$currentUser->jabatan.')';
        }

        $petugasList = collect();
        if ($isAdmin) {
            $petugasList = User::whereIn('jabatan', ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'])->orderBy('name')->get();
        } elseif ($isKasubbag) {
            $petugasList = User::where('jabatan', 'Petugas')->orderBy('name')->get();
        }

        $data = [
            'title' => $title,
            'menuAdminTugas' => $isAdmin ? 'active' : '',
            'menuKasubbagTugas' => $isKasubbag ? 'active' : '',
            'menuPetugasTugas' => (! $isAdmin && ! $isKasubbag) ? 'active' : '',
            'tugas' => $tugasQuery->get(),
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
            'canDelegate' => $canDelegate,
            'petugasList' => $petugasList,
            'activeKlasifikasi' => $activeKlasifikasi,
            'activeStatus' => $activeStatus,
            'activePermohonan' => $activePermohonan,
            'activePersetujuanAtasan' => $activePersetujuanAtasan,
            'activeDeadline' => $activeDeadline,
            'stats' => [
                'total' => $totalTugas,
                'kesekretariatan' => $totalKesekretariatan,
                'kepaniteraan' => $totalKepaniteraan,
                'didelegasikan' => $totalDidelegasikan,
                'menunggu' => $totalMenungguKonfirmasi,
                'selesai' => $totalSelesai,
                'hold' => $totalHold,
                'revisi' => $totalRevisi,
                'pending_permohonan' => $pendingPermohonan,
                'pending_persetujuan_atasan' => $pendingPersetujuanAtasan,
                'mendekati_deadline' => $totalMendekatiDeadline,
            ],
        ];

        return view('admin.tugas.index', $data);
    }

    /**
     * Show the form for creating and delegating a new task.
     */
    public function create(Request $request): View|RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        if (! $isAdmin && ! $isKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin, Kepala Sub Bagian, dan Panitera Muda yang dapat mendelegasikan tugas.');
        }

        $petugasList = $isAdmin
            ? User::whereIn('jabatan', ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'])->orderBy('name')->get()
            : User::where('jabatan', 'Petugas')->orderBy('name')->get();

        $sumberTugas = null;
        if ($request->filled('teruskan_id')) {
            $sumberTugas = Tugas::find($request->query('teruskan_id'));
            if ($sumberTugas && ! $isAdmin && $sumberTugas->petugas_id !== $currentUser->id) {
                $sumberTugas = null;
            }
        }

        $data = [
            'title' => 'Delegasikan Tugas Baru',
            'menuDelegasiBaru' => 'active',
            'menuAdminTugas' => '',
            'menuKasubbagTugas' => '',
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
            'petugasList' => $petugasList,
            'sumberTugas' => $sumberTugas,
        ];

        return view('admin.tugas.create', $data);
    }

    /**
     * Store a newly delegated task (supports single or multiple recipients).
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        if (! $isAdmin && ! $isKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin, Kepala Sub Bagian, dan Panitera Muda yang dapat mendelegasikan tugas.');
        }

        $allowedRecipientRoles = $isAdmin ? ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'] : ['Petugas'];
        $allowedRecipientIds = User::whereIn('jabatan', $allowedRecipientRoles)->pluck('id')->map(fn ($id) => (int) $id)->all();

        // Normalize single petugas_id into array for backwards compatibility while supporting multi-select array
        $rawPetugasInput = $request->input('petugas_id');
        if ($rawPetugasInput !== null && ! is_array($rawPetugasInput)) {
            $request->merge([
                'petugas_id' => $rawPetugasInput !== '' ? [$rawPetugasInput] : [],
            ]);
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'klasifikasi' => ['nullable', 'in:Kesekretariatan,Kepaniteraan'],
            'mode_delegasi' => ['nullable', 'in:terpisah,kolaborasi'],
            'petugas_id' => [
                'required',
                'array',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) use ($allowedRecipientIds): void {
                    if (! is_array($value)) {
                        return;
                    }
                    foreach ($value as $recipientId) {
                        if (! in_array((int) $recipientId, $allowedRecipientIds, true)) {
                            $fail('Petugas yang dipilih tidak valid atau tidak sesuai wewenang.');

                            return;
                        }
                    }
                },
            ],
            'deskripsi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'file_tugas' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png'],
            'sumber_tugas_id' => ['nullable', 'exists:tugas,id'],
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'klasifikasi.in' => 'Klasifikasi tugas tidak valid (pilih Kesekretariatan atau Kepaniteraan).',
            'petugas_id.required' => 'Pilih minimal 1 penerima / petugas pelaksana tugas.',
            'petugas_id.array' => 'Format pilihan penerima tugas tidak valid.',
            'petugas_id.min' => 'Pilih minimal 1 penerima / petugas pelaksana tugas.',
            'deskripsi.required' => 'Deskripsi tugas dan instruksi wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai tugas wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai / deadline wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'file_tugas.file' => 'Berkas lampiran tugas harus berupa file yang valid.',
            'file_tugas.max' => 'Ukuran file lampiran tugas maksimal 10 MB.',
        ]);

        $fileTugasPath = null;
        $fileTugasName = null;

        if ($request->hasFile('file_tugas')) {
            $file = $request->file('file_tugas');
            $fileTugasPath = $this->storeUploadedFile($file, 'tugas');
            $fileTugasName = $file->getClientOriginalName();
        } elseif ($request->filled('sumber_tugas_id')) {
            $sumberTugas = Tugas::find($request->input('sumber_tugas_id'));
            if ($sumberTugas && $sumberTugas->file_tugas) {
                $fileTugasPath = $sumberTugas->file_tugas;
                $fileTugasName = $sumberTugas->file_tugas_name;
            }
        }

        $recipientIds = array_values(array_unique(array_map('intval', $validated['petugas_id'])));
        $targetUsers = User::whereIn('id', $recipientIds)->get()->keyBy('id');
        $modeDelegasi = $validated['mode_delegasi'] ?? 'terpisah';

        if ($modeDelegasi === 'kolaborasi' && count($recipientIds) > 1) {
            $primaryId = $recipientIds[0];
            $primaryUser = $targetUsers->get($primaryId);
            $kolaboratorIds = array_slice($recipientIds, 1);

            if ($currentUser->jabatan === 'Kepala Sub Bagian') {
                $taskKlasifikasi = 'Kesekretariatan';
            } elseif ($currentUser->jabatan === 'Panitera Muda') {
                $taskKlasifikasi = 'Kepaniteraan';
            } else {
                if (! empty($validated['klasifikasi'])) {
                    $taskKlasifikasi = $validated['klasifikasi'];
                } elseif ($primaryUser?->jabatan === 'Panitera Muda') {
                    $taskKlasifikasi = 'Kepaniteraan';
                } else {
                    $taskKlasifikasi = 'Kesekretariatan';
                }
            }

            $crossUnitApproverRole = collect($recipientIds)
                ->map(function (int $id) use ($targetUsers, $currentUser): ?string {
                    $u = $targetUsers->get($id);

                    return $u ? Tugas::requiredCrossUnitApproverRole($currentUser, $u) : null;
                })
                ->filter()
                ->first();

            $needsCrossUnitApproval = $crossUnitApproverRole !== null;

            $tugas = Tugas::create([
                'judul' => $validated['judul'],
                'klasifikasi' => $taskKlasifikasi,
                'petugas_id' => $primaryId,
                'admin_id' => $currentUser->id,
                'deskripsi' => $validated['deskripsi'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'status' => $needsCrossUnitApproval ? 'Menunggu Persetujuan Atasan' : 'Didelegasikan',
                'perlu_persetujuan_atasan' => $needsCrossUnitApproval,
                'jabatan_atasan_penyetuju' => $crossUnitApproverRole,
                'status_persetujuan_atasan' => $needsCrossUnitApproval ? 'Menunggu Persetujuan' : null,
                'file_tugas' => $fileTugasPath,
                'file_tugas_name' => $fileTugasName,
            ]);

            $tugas->kolaborator()->sync($kolaboratorIds);

            $kolaboratorNames = collect($kolaboratorIds)
                ->map(fn (int $id) => $targetUsers->get($id)?->name)
                ->filter()
                ->implode(', ');

            if ($needsCrossUnitApproval) {
                $tugas->recordLog(
                    action: 'pengajuan_lintas_bidang',
                    status: 'Menunggu Persetujuan Atasan',
                    keterangan: 'Tugas kolaborasi lintas bidang ('.$taskKlasifikasi.') diajukan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') untuk Tim Multi-Petugas (PIC: '.($primaryUser?->name ?? 'Pelaksana').' & Kolaborator: '.$kolaboratorNames.') dan menunggu verifikasi/persetujuan '.$crossUnitApproverRole.'.',
                    catatan: null,
                    userId: $currentUser->id,
                );

                $this->notifyCrossUnitApprovers($tugas, $currentUser, $crossUnitApproverRole);

                return redirect()->route('tugas.index')->with('success', "Tugas kolaborasi lintas bidang berhasil dibuat dan menunggu verifikasi serta persetujuan dari {$crossUnitApproverRole}.");
            }

            $tugas->recordLog(
                action: 'delegasi',
                status: 'Didelegasikan',
                keterangan: 'Tugas kolaborasi ('.$taskKlasifikasi.') dibuat dan didelegasikan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') kepada Tim Multi-Petugas — PIC Utama: '.($primaryUser?->name ?? 'Pelaksana').' & Kolaborator: '.$kolaboratorNames.'.',
                catatan: null,
                userId: $currentUser->id,
            );

            if ($primaryUser && $primaryUser->id !== $currentUser->id) {
                $primaryUser->notify(new TugasNotification(
                    tugas: $tugas,
                    sender: $currentUser,
                    type: 'tugas_baru',
                    catatan: $tugas->deskripsi,
                ));
            }

            foreach ($kolaboratorIds as $kolabId) {
                $kolabUser = $targetUsers->get($kolabId);
                if ($kolabUser && $kolabUser->id !== $currentUser->id) {
                    $kolabUser->notify(new TugasNotification(
                        tugas: $tugas,
                        sender: $currentUser,
                        type: 'kolaborator_baru',
                        catatan: $tugas->deskripsi,
                    ));
                }
            }

            User::whereIn('id', $recipientIds)->update(['status' => 'Sedang Bertugas']);

            $totalTim = count($recipientIds);

            return redirect()->route('tugas.index')->with('success', "Tugas kolaborasi berhasil didelegasikan kepada {$totalTim} petugas pelaksana dalam 1 ruang kerja bersama.");
        }

        $createdCount = 0;
        $pendingApprovalCount = 0;
        $directOfficerIds = [];

        foreach ($recipientIds as $recipientId) {
            $targetUser = $targetUsers->get($recipientId);

            if ($currentUser->jabatan === 'Kepala Sub Bagian') {
                $taskKlasifikasi = 'Kesekretariatan';
            } elseif ($currentUser->jabatan === 'Panitera Muda') {
                $taskKlasifikasi = 'Kepaniteraan';
            } else {
                if (! empty($validated['klasifikasi'])) {
                    $taskKlasifikasi = $validated['klasifikasi'];
                } elseif ($targetUser?->jabatan === 'Panitera Muda') {
                    $taskKlasifikasi = 'Kepaniteraan';
                } else {
                    $taskKlasifikasi = 'Kesekretariatan';
                }
            }

            $approverRole = $targetUser ? Tugas::requiredCrossUnitApproverRole($currentUser, $targetUser) : null;
            $needsApproval = $approverRole !== null;

            $tugasData = [
                'judul' => $validated['judul'],
                'klasifikasi' => $taskKlasifikasi,
                'petugas_id' => $recipientId,
                'admin_id' => $currentUser->id,
                'deskripsi' => $validated['deskripsi'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'status' => $needsApproval ? 'Menunggu Persetujuan Atasan' : 'Didelegasikan',
                'perlu_persetujuan_atasan' => $needsApproval,
                'jabatan_atasan_penyetuju' => $approverRole,
                'status_persetujuan_atasan' => $needsApproval ? 'Menunggu Persetujuan' : null,
                'file_tugas' => $fileTugasPath,
                'file_tugas_name' => $fileTugasName,
            ];

            $tugas = Tugas::create($tugasData);
            $createdCount++;

            if ($needsApproval) {
                $pendingApprovalCount++;
                $tugas->recordLog(
                    action: 'pengajuan_lintas_bidang',
                    status: 'Menunggu Persetujuan Atasan',
                    keterangan: 'Tugas lintas bidang ('.$taskKlasifikasi.') diajukan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') untuk '.($targetUser?->name ?? 'Pelaksana').' ('.$targetUser?->klasifikasi.') dan menunggu verifikasi serta persetujuan '.$approverRole.'.',
                    catatan: null,
                    userId: $currentUser->id,
                );

                $this->notifyCrossUnitApprovers($tugas, $currentUser, $approverRole);
            } else {
                $directOfficerIds[] = $recipientId;

                $tugas->recordLog(
                    action: 'delegasi',
                    status: 'Didelegasikan',
                    keterangan: 'Tugas ('.$taskKlasifikasi.') dibuat dan didelegasikan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') kepada '.($targetUser?->name ?? 'Pelaksana').' ('.($targetUser?->jabatan ?? 'Petugas').').',
                    catatan: null,
                    userId: $currentUser->id,
                );

                if ($targetUser && $targetUser->id !== $currentUser->id) {
                    $targetUser->notify(new TugasNotification(
                        tugas: $tugas,
                        sender: $currentUser,
                        type: 'tugas_baru',
                        catatan: $tugas->deskripsi,
                    ));
                }
            }
        }

        // Update only directly delegated officers' status to 'Sedang Bertugas'
        if (! empty($directOfficerIds)) {
            User::whereIn('id', $directOfficerIds)->update(['status' => 'Sedang Bertugas']);
        }

        if ($pendingApprovalCount > 0 && $pendingApprovalCount === $createdCount) {
            $successMessage = 'Delegasi tugas lintas bidang berhasil diajukan dan sedang menunggu verifikasi serta persetujuan atasan unit terkait.';
        } elseif ($pendingApprovalCount > 0) {
            $successMessage = "Tugas berhasil dibuat ({$pendingApprovalCount} tugas lintas bidang menunggu persetujuan atasan unit terkait).";
        } else {
            $successMessage = $createdCount > 1
                ? "Tugas berhasil didelegasikan kepada {$createdCount} petugas pelaksana."
                : 'Tugas berhasil didelegasikan kepada petugas.';
        }

        return redirect()->route('tugas.index')->with('success', $successMessage);
    }

    /**
     * Delegate a task received by Kepala Sub Bagian (or Admin) to one or multiple Petugas.
     */
    public function delegasikanKePetugas(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        if (! $isAdmin && (! $isKasubbag || $tugas->petugas_id !== $currentUser->id)) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk mendelegasikan tugas ini.');
        }

        // Normalize single petugas_id into array for backwards compatibility while supporting multi-select array
        $rawPetugasInput = $request->input('petugas_id');
        if ($rawPetugasInput !== null && ! is_array($rawPetugasInput)) {
            $request->merge([
                'petugas_id' => $rawPetugasInput !== '' ? [$rawPetugasInput] : [],
            ]);
        }

        $validated = $request->validate([
            'petugas_id' => ['required', 'array', 'min:1'],
            'petugas_id.*' => [
                'required',
                'distinct',
                Rule::exists('users', 'id')->where('jabatan', 'Petugas'),
            ],
            'catatan_delegasi' => ['nullable', 'string'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:'.$tugas->tanggal_mulai->format('Y-m-d')],
        ], [
            'petugas_id.required' => 'Pilih minimal 1 Petugas yang akan menerima delegasi tugas.',
            'petugas_id.array' => 'Format pilihan Petugas tidak valid.',
            'petugas_id.min' => 'Pilih minimal 1 Petugas yang akan menerima delegasi tugas.',
            'petugas_id.*.required' => 'Pilih Petugas yang akan menerima delegasi tugas.',
            'petugas_id.*.exists' => 'Petugas yang dipilih tidak valid (harus jabatan Petugas).',
            'tanggal_selesai.after_or_equal' => 'Batas selesai tidak boleh sebelum tanggal mulai tugas.',
        ]);

        $previousAssigneeId = $tugas->petugas_id;
        $recipientIds = array_values(array_unique(array_map('intval', $validated['petugas_id'])));
        $targetUsers = User::whereIn('id', $recipientIds)->get()->keyBy('id');

        $updatedDeskripsi = $tugas->deskripsi;
        if (! empty($validated['catatan_delegasi'])) {
            $updatedDeskripsi = $tugas->deskripsi."\n\n[Instruksi Tambahan ".$currentUser->name."]:\n".$validated['catatan_delegasi'];
        }

        $updatedTanggalSelesai = ! empty($validated['tanggal_selesai'])
            ? $validated['tanggal_selesai']
            : $tugas->tanggal_selesai;

        if ($currentUser->jabatan === 'Kepala Sub Bagian') {
            $delegatedKlasifikasi = 'Kesekretariatan';
        } elseif ($currentUser->jabatan === 'Panitera Muda') {
            $delegatedKlasifikasi = 'Kepaniteraan';
        } else {
            $delegatedKlasifikasi = $tugas->klasifikasi ?: 'Kesekretariatan';
        }

        $directOfficerIds = [];
        $pendingApprovalCount = 0;

        foreach ($recipientIds as $index => $recipientId) {
            $targetPetugas = $targetUsers->get($recipientId);
            $approverRole = $targetPetugas ? Tugas::requiredCrossUnitApproverRole($currentUser, $targetPetugas) : null;
            $needsApproval = $approverRole !== null;

            if ($index === 0) {
                $tugas->klasifikasi = $delegatedKlasifikasi;
                $tugas->deskripsi = $updatedDeskripsi;
                $tugas->tanggal_selesai = $updatedTanggalSelesai;
                $tugas->admin_id = $currentUser->id;
                $tugas->petugas_id = $recipientId;
                $tugas->status = $needsApproval ? 'Menunggu Persetujuan Atasan' : 'Didelegasikan';
                $tugas->perlu_persetujuan_atasan = $needsApproval;
                $tugas->jabatan_atasan_penyetuju = $approverRole;
                $tugas->status_persetujuan_atasan = $needsApproval ? 'Menunggu Persetujuan' : null;
                $tugas->disetujui_atasan_oleh = null;
                $tugas->tanggal_persetujuan_atasan = null;
                $tugas->catatan_persetujuan_atasan = null;
                $tugas->save();

                $delegatedTask = $tugas;
            } else {
                $delegatedTask = Tugas::create([
                    'judul' => $tugas->judul,
                    'klasifikasi' => $delegatedKlasifikasi,
                    'deskripsi' => $updatedDeskripsi,
                    'admin_id' => $currentUser->id,
                    'petugas_id' => $recipientId,
                    'tanggal_mulai' => $tugas->tanggal_mulai,
                    'tanggal_selesai' => $updatedTanggalSelesai,
                    'status' => $needsApproval ? 'Menunggu Persetujuan Atasan' : 'Didelegasikan',
                    'perlu_persetujuan_atasan' => $needsApproval,
                    'jabatan_atasan_penyetuju' => $approverRole,
                    'status_persetujuan_atasan' => $needsApproval ? 'Menunggu Persetujuan' : null,
                    'file_tugas' => $tugas->file_tugas,
                    'file_tugas_name' => $tugas->file_tugas_name,
                ]);
            }

            if ($needsApproval) {
                $pendingApprovalCount++;
                $delegatedTask->recordLog(
                    action: 'pengajuan_lintas_bidang',
                    status: 'Menunggu Persetujuan Atasan',
                    keterangan: 'Tugas lintas bidang ('.$delegatedKlasifikasi.') diteruskan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') untuk '.($targetPetugas?->name ?? 'Petugas').' ('.$targetPetugas?->klasifikasi.') dan menunggu persetujuan '.$approverRole.'.',
                    catatan: ! empty($validated['catatan_delegasi']) ? $validated['catatan_delegasi'] : null,
                    userId: $currentUser->id,
                );

                $this->notifyCrossUnitApprovers($delegatedTask, $currentUser, $approverRole);
            } else {
                $directOfficerIds[] = $recipientId;
                $delegatedTask->recordLog(
                    action: 'delegasi',
                    status: 'Didelegasikan',
                    keterangan: 'Tugas ('.$delegatedKlasifikasi.') didelegasikan lanjutan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') kepada '.($targetPetugas?->name ?? 'Petugas').' (Petugas).',
                    catatan: ! empty($validated['catatan_delegasi']) ? $validated['catatan_delegasi'] : null,
                    userId: $currentUser->id,
                );

                if ($targetPetugas && $targetPetugas->id !== $currentUser->id) {
                    $targetPetugas->notify(new TugasNotification(
                        tugas: $delegatedTask,
                        sender: $currentUser,
                        type: 'delegasi_lanjutan',
                        catatan: ! empty($validated['catatan_delegasi']) ? $validated['catatan_delegasi'] : $delegatedTask->deskripsi,
                    ));
                }
            }
        }

        // Update directly delegated Petugas status to 'Sedang Bertugas'
        if (! empty($directOfficerIds)) {
            User::whereIn('id', $directOfficerIds)->update(['status' => 'Sedang Bertugas']);
        }

        // Update previous assignee (e.g. Kepala Sub Bagian) status if they have no other active assigned tasks
        $this->refreshOfficerStatus((int) $previousAssigneeId);

        $count = count($recipientIds);
        if ($pendingApprovalCount > 0 && $pendingApprovalCount === $count) {
            $message = 'Delegasi tugas lintas bidang berhasil diajukan dan menunggu verifikasi serta persetujuan atasan unit terkait.';
        } elseif ($pendingApprovalCount > 0) {
            $message = "Tugas berhasil didelegasikan ({$pendingApprovalCount} tugas lintas bidang menunggu persetujuan atasan unit terkait).";
        } else {
            $message = $count > 1
                ? "Tugas berhasil didelegasikan kepada {$count} Petugas."
                : 'Tugas berhasil didelegasikan kepada Petugas.';
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', $message);
    }

    /**
     * Display the specified task details.
     */
    public function show(Tugas $tugas): View|RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);
        $canManageTask = $isAdmin || $tugas->admin_id === $currentUser->id;

        // Petugas can only view tasks assigned to them or delegated by them
        if (! $isAdmin && ! $isKasubbag && ! $tugas->isAssignedTo($currentUser) && $tugas->admin_id !== $currentUser->id) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki akses ke tugas ini.');
        }

        // Automatically mark unread notifications related to this task for the viewing user as read
        $currentUser->unreadNotifications
            ->filter(fn ($n): bool => ($n->data['tugas_id'] ?? null) === $tugas->id)
            ->markAsRead();

        // If the task was just delegated and an assigned officer opens it, mark it in progress
        if (! $isAdmin && $tugas->status === 'Didelegasikan' && $tugas->isAssignedTo($currentUser)) {
            $tugas->update(['status' => 'Sedang Dikerjakan']);
            $tugas->recordLog(
                action: 'mulai_dikerjakan',
                status: 'Sedang Dikerjakan',
                keterangan: 'Tugas dibuka dan mulai dikerjakan oleh pelaksana '.$currentUser->name.' ('.$currentUser->jabatan.').',
                catatan: null,
                userId: $currentUser->id,
            );
        }

        $tugas->load(['admin', 'petugas', 'kolaborator', 'atasanPenyetuju', 'diskusi.user', 'logs.user']);

        $petugasList = ($isAdmin || $isKasubbag)
            ? User::where('jabatan', 'Petugas')->orderBy('name')->get()
            : collect();

        $availableKolaborator = $canManageTask
            ? User::whereIn('jabatan', $isAdmin ? ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'] : ['Petugas'])
                ->where('id', '!=', $tugas->petugas_id)
                ->orderBy('name')
                ->get()
            : collect();

        $isAssignedOfficer = $tugas->isAssignedTo($currentUser);
        $canChat = $tugas->canParticipateInChat($currentUser);
        $canApproveCrossUnit = $tugas->canApproveCrossUnit($currentUser);

        $data = [
            'title' => 'Detail Tugas: '.$tugas->judul,
            'menuAdminTugas' => $isAdmin ? 'active' : '',
            'menuKasubbagTugas' => $isKasubbag ? 'active' : '',
            'menuPetugasTugas' => (! $isAdmin && ! $isKasubbag) ? 'active' : '',
            'tugas' => $tugas,
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
            'canManageTask' => $canManageTask,
            'isAssignedOfficer' => $isAssignedOfficer,
            'canChat' => $canChat,
            'canApproveCrossUnit' => $canApproveCrossUnit,
            'petugasList' => $petugasList,
            'availableKolaborator' => $availableKolaborator,
        ];

        return view('admin.tugas.show', $data);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Tugas $tugas): View|RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        if (! $isAdmin && (! $isKasubbag || ($tugas->admin_id !== $currentUser->id && $tugas->petugas_id !== $currentUser->id))) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin, Kepala Sub Bagian, dan Panitera Muda terkait yang dapat mengedit tugas.');
        }

        $petugasList = $isAdmin
            ? User::whereIn('jabatan', ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'])->orderBy('name')->get()
            : User::where('jabatan', 'Petugas')->orderBy('name')->get();

        $data = [
            'title' => 'Edit Delegasi Tugas',
            'menuAdminTugas' => $isAdmin ? 'active' : '',
            'menuKasubbagTugas' => $isKasubbag ? 'active' : '',
            'tugas' => $tugas,
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
            'petugasList' => $petugasList,
        ];

        return view('admin.tugas.edit', $data);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        if (! $isAdmin && (! $isKasubbag || ($tugas->admin_id !== $currentUser->id && $tugas->petugas_id !== $currentUser->id))) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin, Kepala Sub Bagian, dan Panitera Muda terkait yang dapat mengedit tugas.');
        }

        $allowedRecipientRoles = $isAdmin ? ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'] : ['Petugas'];

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'klasifikasi' => ['nullable', 'in:Kesekretariatan,Kepaniteraan'],
            'mode_delegasi' => ['nullable', 'in:terpisah,kolaborasi'],
            'petugas_id' => [
                'required',
                Rule::exists('users', 'id')->whereIn('jabatan', $allowedRecipientRoles),
            ],
            'deskripsi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'file_tugas' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png'],
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'klasifikasi.in' => 'Klasifikasi tugas tidak valid (pilih Kesekretariatan atau Kepaniteraan).',
            'petugas_id.required' => 'Pilih petugas pelaksana tugas.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak terdaftar atau tidak sesuai wewenang.',
            'deskripsi.required' => 'Deskripsi tugas dan instruksi wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai tugas wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai / deadline wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'file_tugas.file' => 'Berkas lampiran tugas harus berupa file yang valid.',
            'file_tugas.max' => 'Ukuran file lampiran tugas maksimal 10 MB.',
        ]);

        if ($currentUser->jabatan === 'Kepala Sub Bagian') {
            $validated['klasifikasi'] = 'Kesekretariatan';
        } elseif ($currentUser->jabatan === 'Panitera Muda') {
            $validated['klasifikasi'] = 'Kepaniteraan';
        } elseif (empty($validated['klasifikasi'])) {
            $validated['klasifikasi'] = $tugas->klasifikasi ?: 'Kesekretariatan';
        }

        if ($request->hasFile('file_tugas')) {
            if ($tugas->file_tugas) {
                Storage::disk('public')->delete($tugas->file_tugas);
            }
            $file = $request->file('file_tugas');
            $path = $this->storeUploadedFile($file, 'tugas');
            $validated['file_tugas'] = $path;
            $validated['file_tugas_name'] = $file->getClientOriginalName();
        }

        $previousAssigneeId = $tugas->petugas_id;

        if ($isKasubbag && $tugas->petugas_id === $currentUser->id && (int) $validated['petugas_id'] !== $currentUser->id) {
            $validated['admin_id'] = $currentUser->id;
            $validated['status'] = 'Didelegasikan';
        }

        if (
            $tugas->status_permohonan === 'Menunggu Persetujuan'
            && $tugas->permohonan_perpanjangan
            && $tugas->tanggal_selesai
            && $validated['tanggal_selesai'] !== $tugas->tanggal_selesai->format('Y-m-d')
        ) {
            $validated['status_permohonan'] = 'Disetujui';
        }

        $tugas->update($validated);

        $tugas->recordLog(
            action: 'update',
            status: $tugas->status,
            keterangan: 'Data dan ketentuan tugas diperbarui oleh '.$currentUser->name.' ('.$currentUser->jabatan.').',
            catatan: null,
            userId: $currentUser->id,
        );

        if ((int) $validated['petugas_id'] !== $previousAssigneeId) {
            $tugas->kolaborator()->detach((int) $validated['petugas_id']);
            User::where('id', $validated['petugas_id'])->update(['status' => 'Sedang Bertugas']);

            $newAssignee = User::find($validated['petugas_id']);
            if ($newAssignee && $newAssignee->id !== $currentUser->id) {
                $newAssignee->notify(new TugasNotification(
                    tugas: $tugas,
                    sender: $currentUser,
                    type: 'tugas_baru',
                    catatan: $tugas->deskripsi,
                ));
            }

            $this->refreshOfficerStatus((int) $previousAssigneeId);
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Data tugas berhasil diperbarui.');
    }

    /**
     * Show the form for petugas to submit tindak lanjut.
     */
    public function tindakLanjutForm(Tugas $tugas): View|RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        if ($currentUser->jabatan !== 'Admin' && ! $tugas->isAssignedTo($currentUser)) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk menindaklanjuti tugas ini.');
        }

        if (in_array($tugas->status, ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'], true)) {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas lintas bidang belum disetujui oleh atasan terkait.');
        }

        $data = [
            'title' => 'Tindak Lanjut Tugas: '.$tugas->judul,
            'menuKasubbagTugas' => $isKasubbag ? 'active' : '',
            'menuPetugasTugas' => ! $isKasubbag ? 'active' : '',
            'tugas' => $tugas,
        ];

        return view('admin.tugas.tindak-lanjut', $data);
    }

    /**
     * Submit tindak lanjut and send back to Admin with file evidence or link.
     */
    public function kirimBalik(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->jabatan !== 'Admin' && ! $tugas->isAssignedTo($currentUser)) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk menindaklanjuti tugas ini.');
        }

        if (in_array($tugas->status, ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'], true)) {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas lintas bidang belum disetujui oleh atasan terkait.');
        }

        $validated = $request->validate([
            'catatan_petugas' => ['required', 'string'],
            'file_evidence' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png',
                'required_without:link_evidence',
            ],
            'link_evidence' => [
                'nullable',
                'url',
                'max:500',
                'required_without:file_evidence',
            ],
        ], [
            'catatan_petugas.required' => 'Laporan / keterangan tindak lanjut wajib diisi.',
            'file_evidence.file' => 'Berkas bukti / evidence harus berupa file yang valid.',
            'file_evidence.max' => 'Ukuran file evidence maksimal 10 MB.',
            'file_evidence.required_without' => 'Harap lampirkan file evidence (maks 10 MB) atau masukkan link evidence.',
            'link_evidence.required_without' => 'Harap lampirkan file evidence (maks 10 MB) atau masukkan link evidence.',
            'link_evidence.url' => 'Format link evidence tidak valid (harus diawali http:// atau https://).',
        ]);

        if ($request->hasFile('file_evidence')) {
            if ($tugas->file_evidence) {
                Storage::disk('public')->delete($tugas->file_evidence);
            }
            $file = $request->file('file_evidence');
            $path = $this->storeUploadedFile($file, 'evidence');
            $tugas->file_evidence = $path;
            $tugas->file_evidence_name = $file->getClientOriginalName();
        }

        if ($request->filled('link_evidence')) {
            $tugas->link_evidence = $validated['link_evidence'];
        }

        if ($tugas->status === 'HOLD' || $tugas->hold_started_at !== null) {
            $tugas->releaseHold();
        }

        $tugas->catatan_petugas = $validated['catatan_petugas'];
        $tugas->tanggal_tindak_lanjut = now();
        $tugas->status = 'Menunggu Konfirmasi';
        $tugas->save();

        $tugas->recordLog(
            action: 'kirim_balik',
            status: 'Menunggu Konfirmasi',
            keterangan: 'Laporan hasil tindak lanjut dan bukti evidence dikirimkan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') kepada pendelegasi tugas.',
            catatan: $validated['catatan_petugas'],
            userId: $currentUser->id,
        );

        // Notify all Admin users and the delegating user (e.g. Kepala Sub Bagian) that follow-up is completed
        $recipients = User::where(function ($query) use ($tugas): void {
            $query->where('jabatan', 'Admin')
                ->orWhere('id', $tugas->admin_id);
        })
            ->where('id', '!=', $currentUser->id)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'tindak_lanjut',
                catatan: $validated['catatan_petugas'],
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Tugas berhasil ditindaklanjuti dan dikirim balik ke pemberi tugas.');
    }

    /**
     * Admin or delegating pemberi tugas review and verification of submitted task (Complete, Request Revision, or HOLD).
     */
    public function verifikasi(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isPemberiTugas = $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isPemberiTugas) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk memverifikasi tugas ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Selesai,Perlu Revisi,HOLD,Sedang Dikerjakan'],
            'catatan_admin' => ['nullable', 'string'],
        ], [
            'status.required' => 'Pilih keputusan verifikasi.',
            'status.in' => 'Status verifikasi tidak valid.',
        ]);

        if ($validated['status'] === 'HOLD') {
            return $this->processHoldStatus($tugas, $currentUser, $validated['catatan_admin'] ?? null, true);
        }

        if ($validated['status'] === 'Sedang Dikerjakan') {
            return $this->processHoldStatus($tugas, $currentUser, $validated['catatan_admin'] ?? null, false);
        }

        // If task was previously in HOLD status, release hold and accumulate hold duration before closing/revising
        if ($tugas->status === 'HOLD' || $tugas->hold_started_at !== null) {
            $tugas->releaseHold();
        }

        $tugas->status = $validated['status'];
        $tugas->catatan_admin = $validated['catatan_admin'] ?? null;
        if ($validated['status'] === 'Selesai' && $tugas->tanggal_tindak_lanjut === null) {
            $tugas->tanggal_tindak_lanjut = now();
        }
        $tugas->save();

        if ($validated['status'] === 'Selesai') {
            $tugas->recordLog(
                action: 'selesai',
                status: 'Selesai',
                keterangan: 'Tugas diverifikasi dan dinyatakan Selesai (Closed) oleh '.$currentUser->name.' ('.$currentUser->jabatan.').',
                catatan: $validated['catatan_admin'] ?? null,
                userId: $currentUser->id,
            );
        } else {
            $tugas->recordLog(
                action: 'revisi',
                status: 'Perlu Revisi',
                keterangan: 'Pendelegasi tugas ('.$currentUser->name.' - '.$currentUser->jabatan.') meminta perbaikan / revisi tugas kepada pelaksana.',
                catatan: $validated['catatan_admin'] ?? null,
                userId: $currentUser->id,
            );
        }

        // Notify all assigned officers (PIC + collaborators) of verification result (Selesai or Perlu Revisi)
        $assignedOfficers = $tugas->allPetugas();
        $notifyTargets = $assignedOfficers->reject(fn (User $user): bool => $user->id === $currentUser->id)->values();
        if ($notifyTargets->isNotEmpty()) {
            Notification::send($notifyTargets, new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: $validated['status'] === 'Selesai' ? 'selesai' : 'revisi',
                catatan: $validated['catatan_admin'] ?? null,
            ));
        }

        // If completed, check if each assigned officer has any other active tasks
        if ($validated['status'] === 'Selesai') {
            foreach ($assignedOfficers as $officer) {
                $this->refreshOfficerStatus((int) $officer->id);
            }
        }

        $message = $validated['status'] === 'Selesai'
            ? 'Tugas berhasil diverifikasi dan ditandai Selesai.'
            : 'Tugas telah dikembalikan ke petugas untuk revisi.';

        return redirect()->route('tugas.show', $tugas->id)->with('success', $message);
    }

    /**
     * Place a task on HOLD or resume it from HOLD (only allowed for Admin and Pemberi Tugas).
     */
    public function hold(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isPemberiTugas = $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isPemberiTugas) {
            return redirect()->route('tugas.index')->with('error', 'Status HOLD hanya dapat diberikan oleh Admin dan pemberi tugas.');
        }

        if ($tugas->status === 'Selesai') {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas yang sudah Selesai (Closed) tidak dapat diubah ke status HOLD.');
        }

        $validated = $request->validate([
            'action' => ['nullable', 'in:hold,lanjutkan'],
            'catatan_hold' => ['nullable', 'string'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        $catatan = $validated['catatan_hold'] ?? $validated['catatan_admin'] ?? null;
        $shouldHold = ($validated['action'] ?? null) === 'lanjutkan'
            ? false
            : ($tugas->status !== 'HOLD' || ($validated['action'] ?? null) === 'hold');

        return $this->processHoldStatus($tugas, $currentUser, $catatan, $shouldHold);
    }

    /**
     * Internal helper to transition a task into HOLD or resume it from HOLD.
     */
    protected function processHoldStatus(Tugas $tugas, User $currentUser, ?string $catatan, bool $shouldHold): RedirectResponse
    {
        if ($shouldHold) {
            $tugas->startHold();
            if ($tugas->status_permohonan === 'Menunggu Persetujuan' && $tugas->permohonan_hold) {
                $tugas->status_permohonan = 'Disetujui';
            }
            if ($catatan !== null && $catatan !== '') {
                $tugas->catatan_admin = $catatan;
            }
            $tugas->save();

            $tugas->recordLog(
                action: 'hold',
                status: 'HOLD',
                keterangan: 'Tugas diubah ke status HOLD (ditunda sementara) oleh '.$currentUser->name.' ('.$currentUser->jabatan.'). Durasi selama status HOLD tidak dihitung dalam keterlambatan performa.',
                catatan: $catatan,
                userId: $currentUser->id,
            );

            $notifyTargets = $tugas->allPetugas()->reject(fn (User $user): bool => $user->id === $currentUser->id)->values();
            if ($notifyTargets->isNotEmpty()) {
                Notification::send($notifyTargets, new TugasNotification(
                    tugas: $tugas,
                    sender: $currentUser,
                    type: 'hold',
                    catatan: $catatan,
                ));
            }

            return redirect()->route('tugas.show', $tugas->id)->with('success', 'Tugas berhasil diubah ke status HOLD. Durasi selama status HOLD tidak terhitung dalam keterlambatan penyelesaian tugas.');
        }

        // Resume task from HOLD
        $tugas->releaseHold();
        $nextStatus = ($tugas->file_evidence || $tugas->link_evidence || $tugas->tanggal_tindak_lanjut)
            ? 'Sedang Dikerjakan'
            : 'Sedang Dikerjakan';
        $tugas->status = $nextStatus;
        if ($catatan !== null && $catatan !== '') {
            $tugas->catatan_admin = $catatan;
        }
        $tugas->save();

        $tugas->recordLog(
            action: 'lanjutkan',
            status: 'Sedang Dikerjakan',
            keterangan: 'Status HOLD dicabut dan pengerjaan tugas dilanjutkan kembali oleh '.$currentUser->name.' ('.$currentUser->jabatan.').',
            catatan: $catatan,
            userId: $currentUser->id,
        );

        $notifyTargets = $tugas->allPetugas()->reject(fn (User $user): bool => $user->id === $currentUser->id)->values();
        if ($notifyTargets->isNotEmpty()) {
            Notification::send($notifyTargets, new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'lanjutkan',
                catatan: $catatan,
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Status HOLD berhasil dicabut dan tugas kembali dalam status Sedang Dikerjakan.');
    }

    /**
     * Officer (Petugas) submits a checklist request to the delegator to HOLD the task and/or extend the deadline.
     */
    public function ajukanPermohonan(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $tugas->isAssignedTo($currentUser)) {
            return redirect()->route('tugas.index')->with('error', 'Hanya petugas pelaksana tugas ini yang dapat mengajukan permohonan Hold atau perpanjangan waktu.');
        }

        if ($tugas->status === 'Selesai') {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas yang sudah Selesai (Closed) tidak dapat diajukan permohonan.');
        }

        $jenisArray = (array) $request->input('jenis_permohonan', []);
        $wantsHold = in_array('hold', $jenisArray, true) || $request->boolean('permohonan_hold');
        $wantsPerpanjangan = in_array('perpanjangan', $jenisArray, true) || $request->boolean('permohonan_perpanjangan');

        if (! $wantsHold && ! $wantsPerpanjangan) {
            return redirect()->back()
                ->withErrors(['jenis_permohonan' => 'Pilih minimal 1 ceklist permohonan (Permohonan Hold Tugas atau Perpanjangan Masa Pengerjaan).'])
                ->withInput();
        }

        $minDate = $tugas->tanggal_selesai
            ? $tugas->tanggal_selesai->format('Y-m-d')
            : ($tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('Y-m-d') : date('Y-m-d'));

        $validated = $request->validate([
            'usulan_tanggal_selesai' => [
                $wantsPerpanjangan ? 'required' : 'nullable',
                'date',
                'after_or_equal:'.$minDate,
            ],
            'catatan_permohonan' => ['required', 'string', 'max:1000'],
        ], [
            'usulan_tanggal_selesai.required' => 'Usulan tanggal batas selesai baru wajib diisi jika mencentang permohonan perpanjangan masa pengerjaan.',
            'usulan_tanggal_selesai.date' => 'Format usulan tanggal selesai baru tidak valid.',
            'usulan_tanggal_selesai.after_or_equal' => 'Usulan batas selesai baru tidak boleh sebelum batas waktu tugas saat ini.',
            'catatan_permohonan.required' => 'Alasan / keterangan permohonan wajib diisi.',
        ]);

        $tugas->permohonan_hold = $wantsHold;
        $tugas->permohonan_perpanjangan = $wantsPerpanjangan;
        $tugas->usulan_tanggal_selesai = $wantsPerpanjangan ? ($validated['usulan_tanggal_selesai'] ?? null) : null;
        $tugas->catatan_permohonan = $validated['catatan_permohonan'];
        $tugas->tanggal_permohonan = now();
        $tugas->status_permohonan = 'Menunggu Persetujuan';
        $tugas->save();

        $checkedLabels = [];
        if ($wantsHold) {
            $checkedLabels[] = 'Permohonan HOLD Tugas';
        }
        if ($wantsPerpanjangan) {
            $usulanStr = ! empty($validated['usulan_tanggal_selesai'])
                ? ' (Usulan Deadline Baru: '.date('d/m/Y', strtotime($validated['usulan_tanggal_selesai'])).')'
                : '';
            $checkedLabels[] = 'Permohonan Edit / Perpanjangan Masa Pengerjaan'.$usulanStr;
        }

        $ringkasanPermohonan = implode(' & ', $checkedLabels);

        $tugas->recordLog(
            action: 'permohonan_petugas',
            status: $tugas->status,
            keterangan: 'Petugas pelaksana '.$currentUser->name.' ('.$currentUser->jabatan.') mengajukan ceklist permohonan kepada pemberi tugas: ['.$ringkasanPermohonan.'].',
            catatan: $validated['catatan_permohonan'],
            userId: $currentUser->id,
        );

        $recipients = User::where(function ($query) use ($tugas): void {
            $query->where('jabatan', 'Admin')
                ->orWhere('id', $tugas->admin_id);
        })
            ->where('id', '!=', $currentUser->id)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'permohonan_petugas',
                catatan: '['.$ringkasanPermohonan.'] — '.$validated['catatan_permohonan'],
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Permohonan ('.$ringkasanPermohonan.') berhasil dikirimkan kepada pemberi tugas.');
    }

    /**
     * Admin or Pemberi Tugas responds to the officer's checklist request (Approve HOLD / Extend Deadline or Reject).
     */
    public function tanggapiPermohonan(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isPemberiTugas = $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isPemberiTugas) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin dan pemberi tugas yang dapat menanggapi permohonan petugas.');
        }

        $validated = $request->validate([
            'keputusan' => ['required', 'in:setujui,tolak'],
            'tanggal_selesai_baru' => [
                'nullable',
                'date',
                'after_or_equal:'.($tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('Y-m-d') : date('Y-m-d')),
            ],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ], [
            'keputusan.required' => 'Pilih keputusan tanggapan permohonan.',
            'tanggal_selesai_baru.after_or_equal' => 'Batas selesai baru tidak boleh sebelum tanggal mulai tugas.',
        ]);

        if ($validated['keputusan'] === 'setujui') {
            $tindakanList = [];

            if ($tugas->permohonan_perpanjangan) {
                $newDeadline = ! empty($validated['tanggal_selesai_baru'])
                    ? $validated['tanggal_selesai_baru']
                    : ($tugas->usulan_tanggal_selesai ? $tugas->usulan_tanggal_selesai->format('Y-m-d') : null);

                if ($newDeadline) {
                    $tugas->tanggal_selesai = $newDeadline;
                    $tindakanList[] = 'Masa pengerjaan diperpanjang hingga '.date('d/m/Y', strtotime($newDeadline));
                }
            }

            if ($tugas->permohonan_hold) {
                $tugas->startHold();
                $tindakanList[] = 'Status tugas diubah menjadi HOLD';
            }

            $tugas->status_permohonan = 'Disetujui';
            if (! empty($validated['catatan_admin'])) {
                $tugas->catatan_admin = $validated['catatan_admin'];
            }
            $tugas->save();

            $ketTindakan = ! empty($tindakanList) ? ' ('.implode(' & ', $tindakanList).')' : '';

            $tugas->recordLog(
                action: $tugas->status === 'HOLD' ? 'hold' : 'tanggapi_permohonan',
                status: $tugas->status,
                keterangan: 'Permohonan petugas disetujui oleh '.$currentUser->name.' ('.$currentUser->jabatan.')'.$ketTindakan.'.',
                catatan: $validated['catatan_admin'] ?? null,
                userId: $currentUser->id,
            );

            $notifyTargets = $tugas->allPetugas()->reject(fn (User $user): bool => $user->id === $currentUser->id)->values();
            if ($notifyTargets->isNotEmpty()) {
                Notification::send($notifyTargets, new TugasNotification(
                    tugas: $tugas,
                    sender: $currentUser,
                    type: $tugas->status === 'HOLD' ? 'hold' : 'tanggapi_permohonan',
                    catatan: $validated['catatan_admin'] ?? ('Permohonan Anda telah disetujui'.$ketTindakan.'.'),
                ));
            }

            return redirect()->route('tugas.show', $tugas->id)->with('success', 'Permohonan petugas berhasil disetujui'.$ketTindakan.'.');
        }

        // Reject request
        $tugas->status_permohonan = 'Ditolak';
        if (! empty($validated['catatan_admin'])) {
            $tugas->catatan_admin = $validated['catatan_admin'];
        }
        $tugas->save();

        $tugas->recordLog(
            action: 'tolak_permohonan',
            status: $tugas->status,
            keterangan: 'Permohonan petugas ditolak oleh '.$currentUser->name.' ('.$currentUser->jabatan.').',
            catatan: $validated['catatan_admin'] ?? null,
            userId: $currentUser->id,
        );

        $notifyTargets = $tugas->allPetugas()->reject(fn (User $user): bool => $user->id === $currentUser->id)->values();
        if ($notifyTargets->isNotEmpty()) {
            Notification::send($notifyTargets, new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'tanggapi_permohonan',
                catatan: $validated['catatan_admin'] ?? 'Permohonan Anda belum dapat disetujui.',
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Permohonan petugas telah ditolak.');
    }

    /**
     * Reopen a closed task and redelegate to the assigned officer due to work errors.
     */
    public function bukaKembali(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isDelegatorKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true) && $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isDelegatorKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin yang berwenang membuka kembali tugas ini.');
        }

        if ($tugas->status !== 'Selesai') {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Hanya tugas dengan status Selesai yang dapat dibuka kembali.');
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:'.$tugas->tanggal_mulai->format('Y-m-d')],
        ], [
            'catatan.required' => 'Keterangan kesalahan / instruksi perbaikan wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Batas selesai baru tidak boleh sebelum tanggal mulai tugas.',
        ]);

        if (! empty($validated['tanggal_selesai'])) {
            $tugas->tanggal_selesai = $validated['tanggal_selesai'];
        }

        $tugas->status = 'Didelegasikan';
        $tugas->catatan_admin = $validated['catatan'];
        $tugas->tanggal_tindak_lanjut = null;
        $tugas->save();

        // Update all assigned officers' status back to 'Sedang Bertugas'
        $assignedOfficers = $tugas->allPetugas();
        if ($assignedOfficers->isNotEmpty()) {
            User::whereIn('id', $assignedOfficers->pluck('id')->all())->update(['status' => 'Sedang Bertugas']);
        }

        $petugas = $tugas->petugas;
        $tugas->recordLog(
            action: 'buka_kembali',
            status: 'Didelegasikan',
            keterangan: 'Tugas yang telah ditutup (closed) dibuka kembali dan didelegasikan ulang kepada '.($petugas?->name ?? 'Petugas').' ('.($petugas?->jabatan ?? 'Petugas').') oleh '.$currentUser->name.' ('.$currentUser->jabatan.') karena terdapat kesalahan/perbaikan pengerjaan.',
            catatan: $validated['catatan'],
            userId: $currentUser->id,
        );

        $notifyTargets = $assignedOfficers->reject(fn (User $user): bool => $user->id === $currentUser->id)->values();
        if ($notifyTargets->isNotEmpty()) {
            Notification::send($notifyTargets, new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'buka_kembali',
                catatan: $validated['catatan'],
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Tugas berhasil dibuka kembali dan didelegasikan ulang kepada petugas yang bersangkutan.');
    }

    /**
     * Send a flexible progress chat message and/or file/link attachment without auto-changing task status to review.
     */
    public function kirimDiskusi(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $tugas->canParticipateInChat($currentUser)) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki akses untuk berkomunikasi pada tugas ini.');
        }

        if ($tugas->status === 'Selesai') {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas sudah berstatus Selesai (Closed). Buka kembali tugas terlebih dahulu jika ingin melanjutkan diskusi.');
        }

        if (in_array($tugas->status, ['Menunggu Persetujuan Atasan', 'Ditolak Atasan'], true)) {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas lintas bidang belum disetujui oleh atasan terkait.');
        }

        $validated = $request->validate([
            'pesan' => ['nullable', 'string', 'max:2000', 'required_without_all:file_lampiran,link_url'],
            'file_lampiran' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png,webp'],
            'link_url' => ['nullable', 'url', 'max:500'],
        ], [
            'pesan.required_without_all' => 'Tuliskan pesan progres, lampirkan berkas, atau sertakan tautan URL terlebih dahulu.',
            'file_lampiran.file' => 'Berkas lampiran diskusi harus berupa file yang valid.',
            'file_lampiran.max' => 'Ukuran file lampiran diskusi maksimal 10 MB.',
            'file_lampiran.mimes' => 'Format file yang didukung: PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR, JPG, PNG, WEBP.',
            'link_url.url' => 'Format tautan URL tidak valid (harus diawali http:// atau https://).',
        ]);

        $filePath = null;
        $fileName = null;
        $fileUrl = null;

        if ($request->hasFile('file_lampiran')) {
            $file = $request->file('file_lampiran');
            $filePath = $this->storeUploadedFile($file, 'diskusi');
            $fileName = $file->getClientOriginalName();
            $fileUrl = asset('storage/'.$filePath);
        }

        $linkUrl = ! empty($validated['link_url']) ? $validated['link_url'] : null;
        $pesan = isset($validated['pesan']) && trim($validated['pesan']) !== '' ? trim($validated['pesan']) : null;

        TugasDiskusi::create([
            'tugas_id' => $tugas->id,
            'user_id' => $currentUser->id,
            'pesan' => $pesan,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'link_url' => $linkUrl,
        ]);

        $logUrl = $fileUrl ?? $linkUrl;
        $logFileName = $fileName;

        $activityParts = [];
        if ($pesan !== null) {
            $activityParts[] = 'mengirim pesan progres';
        }
        if ($fileUrl !== null) {
            $activityParts[] = 'mengunggah berkas ('.$fileName.')';
        } elseif ($linkUrl !== null) {
            $activityParts[] = 'melampirkan tautan URL';
        }
        $activitySummary = ! empty($activityParts) ? implode(' & ', $activityParts) : 'mengirim pembaruan diskusi';

        $tugas->recordLog(
            action: 'diskusi',
            status: $tugas->status,
            keterangan: $currentUser->name.' ('.$currentUser->jabatan.') '.$activitySummary.' di ruang diskusi (status tugas tetap '.$tugas->status.', tidak otomatis masuk tinjauan).',
            catatan: $pesan,
            userId: $currentUser->id,
            fileUrl: $logUrl,
            fileName: $logFileName,
        );

        $participantIds = collect([$tugas->admin_id, $tugas->petugas_id])
            ->merge($tugas->kolaborator()->pluck('users.id'))
            ->filter()
            ->unique()
            ->reject(fn ($id): bool => (int) $id === (int) $currentUser->id)
            ->values();

        if ($participantIds->isNotEmpty()) {
            $recipients = User::whereIn('id', $participantIds->all())->get();
            if ($recipients->isNotEmpty()) {
                $notifCatatan = $pesan ?? ($fileName ? 'Mengunggah berkas: '.$fileName : $linkUrl);
                Notification::send($recipients, new TugasNotification(
                    tugas: $tugas,
                    sender: $currentUser,
                    type: 'diskusi_progres',
                    catatan: $notifCatatan,
                ));
            }
        }

        $successMessage = $logUrl !== null
            ? 'Pesan progres dan tautan lampiran berhasil dicatat ke Ruang Diskusi & Log Aktivitas tanpa mengubah status menjadi Menunggu Konfirmasi.'
            : 'Pesan progres berhasil dikirim ke Ruang Diskusi.';

        return redirect()->to(route('tugas.show', $tugas->id).'#ruang-diskusi')->with('success', $successMessage);
    }

    /**
     * Add or update collaborating officers (multi-petugas) on a task (only Admin or Author).
     */
    public function syncKolaborator(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isAuthor = (int) $tugas->admin_id === (int) $currentUser->id;

        if (! $isAdmin && ! $isAuthor) {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Hanya Admin atau Pemberi Tugas yang dapat mengelola tim multi-petugas.');
        }

        if ($tugas->status === 'Selesai') {
            return redirect()->route('tugas.show', $tugas->id)->with('error', 'Tugas yang sudah Selesai tidak dapat diubah tim petugasnya.');
        }

        $allowedRoles = $isAdmin ? ['Kepala Sub Bagian', 'Panitera Muda', 'Petugas'] : ['Petugas'];

        $validated = $request->validate([
            'kolaborator_ids' => ['nullable', 'array'],
            'kolaborator_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->whereIn('jabatan', $allowedRoles),
            ],
        ], [
            'kolaborator_ids.*.exists' => 'Salah satu petugas kolaborator yang dipilih tidak valid.',
        ]);

        $newIds = collect($validated['kolaborator_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === (int) $tugas->petugas_id)
            ->unique()
            ->values()
            ->all();

        $oldIds = $tugas->kolaborator()->pluck('users.id')->map(fn ($id): int => (int) $id)->all();

        $addedIds = array_values(array_diff($newIds, $oldIds));
        $removedIds = array_values(array_diff($oldIds, $newIds));

        $tugas->kolaborator()->sync($newIds);

        if (! empty($addedIds)) {
            $addedUsers = User::whereIn('id', $addedIds)->get();

            $crossUnitApproverRole = $addedUsers
                ->map(fn (User $u): ?string => Tugas::requiredCrossUnitApproverRole($currentUser, $u))
                ->filter()
                ->first();

            if ($crossUnitApproverRole !== null) {
                $tugas->status = 'Menunggu Persetujuan Atasan';
                $tugas->perlu_persetujuan_atasan = true;
                $tugas->jabatan_atasan_penyetuju = $crossUnitApproverRole;
                $tugas->status_persetujuan_atasan = 'Menunggu Persetujuan';
                $tugas->disetujui_atasan_oleh = null;
                $tugas->tanggal_persetujuan_atasan = null;
                $tugas->catatan_persetujuan_atasan = null;
                $tugas->save();

                $this->notifyCrossUnitApprovers($tugas, $currentUser, $crossUnitApproverRole);
            } else {
                User::whereIn('id', $addedIds)->update(['status' => 'Sedang Bertugas']);

                $notifyUsers = $addedUsers->where('id', '!=', $currentUser->id)->values();
                if ($notifyUsers->isNotEmpty()) {
                    Notification::send($notifyUsers, new TugasNotification(
                        tugas: $tugas,
                        sender: $currentUser,
                        type: 'kolaborator_baru',
                        catatan: $tugas->deskripsi,
                    ));
                }
            }
        }

        foreach ($removedIds as $removedId) {
            $this->refreshOfficerStatus($removedId);
        }

        $kolaboratorNames = User::whereIn('id', $newIds)->orderBy('name')->pluck('name')->implode(', ');
        $keterangan = $kolaboratorNames !== ''
            ? $currentUser->name.' ('.$currentUser->jabatan.') memperbarui Tim Multi-Petugas — Kolaborator aktif: '.$kolaboratorNames.'.'
            : $currentUser->name.' ('.$currentUser->jabatan.') memperbarui Tim Multi-Petugas (hanya PIC Utama yang tersisa).';

        $tugas->recordLog(
            action: $tugas->isAwaitingSupervisorApproval() ? 'pengajuan_lintas_bidang' : 'kolaborator',
            status: $tugas->status,
            keterangan: $keterangan,
            catatan: null,
            userId: $currentUser->id,
        );

        $msg = $tugas->isAwaitingSupervisorApproval()
            ? "Tim multi-petugas diperbarui dan menunggu persetujuan {$tugas->jabatan_atasan_penyetuju} karena melibatkan petugas lintas bidang."
            : 'Tim multi-petugas pelaksana berhasil diperbarui.';

        return redirect()->route('tugas.show', $tugas->id)->with('success', $msg);
    }

    /**
     * Supervisor (Kepala Sub Bagian, Panitera Muda, or Admin) approves or rejects a cross-unit delegation.
     */
    public function tanggapiPersetujuanAtasan(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $tugas->canApproveCrossUnit($currentUser)) {
            return redirect()->route('tugas.show', $tugas->id)->with(
                'error',
                'Anda tidak memiliki wewenang untuk memverifikasi persetujuan delegasi lintas bidang ini (khusus '.($tugas->jabatan_atasan_penyetuju ?? 'Atasan Unit Terkait').' atau Admin).'
            );
        }

        $validated = $request->validate([
            'keputusan' => ['required', 'in:setujui,tolak'],
            'catatan_persetujuan_atasan' => ['nullable', 'string', 'max:1000'],
        ], [
            'keputusan.required' => 'Pilih keputusan persetujuan delegasi.',
            'keputusan.in' => 'Keputusan persetujuan tidak valid.',
        ]);

        $catatan = ! empty($validated['catatan_persetujuan_atasan']) ? trim($validated['catatan_persetujuan_atasan']) : null;
        $delegator = $tugas->admin;

        if ($validated['keputusan'] === 'setujui') {
            $tugas->status = 'Didelegasikan';
            $tugas->status_persetujuan_atasan = 'Disetujui';
            $tugas->disetujui_atasan_oleh = $currentUser->id;
            $tugas->tanggal_persetujuan_atasan = now();
            $tugas->catatan_persetujuan_atasan = $catatan;
            $tugas->save();

            $assignedOfficers = $tugas->allPetugas();
            if ($assignedOfficers->isNotEmpty()) {
                User::whereIn('id', $assignedOfficers->pluck('id')->all())->update(['status' => 'Sedang Bertugas']);

                $officerTargets = $assignedOfficers->reject(fn (User $u): bool => $u->id === $currentUser->id)->values();
                if ($officerTargets->isNotEmpty()) {
                    Notification::send($officerTargets, new TugasNotification(
                        tugas: $tugas,
                        sender: $delegator ?? $currentUser,
                        type: 'tugas_baru',
                        catatan: $tugas->deskripsi,
                    ));
                }
            }

            $tugas->recordLog(
                action: 'persetujuan_atasan_disetujui',
                status: 'Didelegasikan',
                keterangan: 'Delegasi lintas bidang diverifikasi dan disetujui oleh '.$currentUser->name.' ('.$currentUser->jabatan.'). Tugas resmi diteruskan kepada pelaksana.',
                catatan: $catatan,
                userId: $currentUser->id,
            );

            if ($delegator && $delegator->id !== $currentUser->id) {
                $delegator->notify(new TugasNotification(
                    tugas: $tugas,
                    sender: $currentUser,
                    type: 'persetujuan_lintas_bidang_disetujui',
                    catatan: $catatan,
                ));
            }

            return redirect()->route('tugas.show', $tugas->id)->with('success', 'Delegasi lintas bidang disetujui dan resmi diteruskan kepada petugas pelaksana.');
        }

        // Reject cross-unit delegation
        $tugas->status = 'Ditolak Atasan';
        $tugas->status_persetujuan_atasan = 'Ditolak';
        $tugas->disetujui_atasan_oleh = $currentUser->id;
        $tugas->tanggal_persetujuan_atasan = now();
        $tugas->catatan_persetujuan_atasan = $catatan;
        $tugas->save();

        $tugas->recordLog(
            action: 'persetujuan_atasan_ditolak',
            status: 'Ditolak Atasan',
            keterangan: 'Delegasi lintas bidang ditolak oleh '.$currentUser->name.' ('.$currentUser->jabatan.').',
            catatan: $catatan,
            userId: $currentUser->id,
        );

        if ($delegator && $delegator->id !== $currentUser->id) {
            $delegator->notify(new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'persetujuan_lintas_bidang_ditolak',
                catatan: $catatan,
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Delegasi lintas bidang telah ditolak dan pemberi tugas telah diberi notifikasi.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isDelegatorKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true) && $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isDelegatorKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk menghapus tugas ini.');
        }

        $affectedOfficerIds = $tugas->allPetugas()->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if ($tugas->file_tugas) {
            Storage::disk('public')->delete($tugas->file_tugas);
        }
        if ($tugas->file_evidence) {
            Storage::disk('public')->delete($tugas->file_evidence);
        }

        $diskusiFiles = $tugas->diskusi()->whereNotNull('file_path')->pluck('file_path');
        foreach ($diskusiFiles as $diskusiFile) {
            Storage::disk('public')->delete($diskusiFile);
        }

        $tugas->delete();

        foreach ($affectedOfficerIds as $officerId) {
            $this->refreshOfficerStatus($officerId);
        }

        return redirect()->route('tugas.index')->with('success', 'Data tugas berhasil dihapus.');
    }

    /**
     * Export task report to Excel (.xls) with optional period and status filter.
     */
    public function exportExcel(Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        $query = Tugas::with(['admin', 'petugas']);

        if ($isAdmin) {
            if ($request->filled('petugas_id')) {
                $query->where('petugas_id', $request->query('petugas_id'));
            }
        } elseif ($isKasubbag) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('petugas_id', $currentUser->id)
                    ->orWhere('admin_id', $currentUser->id);
            });
            if ($request->filled('petugas_id')) {
                $query->where('petugas_id', $request->query('petugas_id'));
            }
        } else {
            $query->where(function ($q) use ($currentUser): void {
                $q->where('petugas_id', $currentUser->id)
                    ->orWhereHas('kolaborator', fn ($k) => $k->where('users.id', $currentUser->id));
            });
        }

        if ($request->filled('klasifikasi')) {
            $query->where('klasifikasi', $request->query('klasifikasi'));
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_mulai', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_selesai', '<=', $request->query('tanggal_akhir'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $tugas = $query->latest()->get();

        $periodeString = 'Semua Periode';
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $periodeString = date('d/m/Y', strtotime($request->tanggal_awal)).' s/d '.date('d/m/Y', strtotime($request->tanggal_akhir));
        } elseif ($request->filled('tanggal_awal')) {
            $periodeString = 'Mulai '.date('d/m/Y', strtotime($request->tanggal_awal));
        } elseif ($request->filled('tanggal_akhir')) {
            $periodeString = 'Sampai '.date('d/m/Y', strtotime($request->tanggal_akhir));
        }

        $filename = 'Laporan_Data_Tugas_'.date('Ymd_His').'.xls';

        $content = view('admin.tugas.excel', [
            'tugas' => $tugas,
            'periodeString' => $periodeString,
            'filterStatus' => $request->status ?? 'Semua Status',
            'filterKlasifikasi' => $request->klasifikasi ?: 'Semua Klasifikasi',
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export task report to PDF with optional period and status filter.
     */
    public function exportPdf(Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        $query = Tugas::with(['admin', 'petugas']);

        $filterPetugas = 'Semua Petugas';
        if ($isAdmin) {
            if ($request->filled('petugas_id')) {
                $query->where('petugas_id', $request->query('petugas_id'));
                $selectedPetugas = User::find($request->query('petugas_id'));
                $filterPetugas = $selectedPetugas ? $selectedPetugas->name : 'Semua Petugas';
            }
        } elseif ($isKasubbag) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('petugas_id', $currentUser->id)
                    ->orWhere('admin_id', $currentUser->id);
            });
            if ($request->filled('petugas_id')) {
                $query->where('petugas_id', $request->query('petugas_id'));
                $selectedPetugas = User::find($request->query('petugas_id'));
                $filterPetugas = $selectedPetugas ? $selectedPetugas->name : $currentUser->name;
            }
        } else {
            $query->where(function ($q) use ($currentUser): void {
                $q->where('petugas_id', $currentUser->id)
                    ->orWhereHas('kolaborator', fn ($k) => $k->where('users.id', $currentUser->id));
            });
            $filterPetugas = $currentUser->name;
        }

        if ($request->filled('klasifikasi')) {
            $query->where('klasifikasi', $request->query('klasifikasi'));
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_mulai', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_selesai', '<=', $request->query('tanggal_akhir'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $tugas = $query->latest()->get();

        $periodeString = 'Semua Periode';
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $periodeString = date('d/m/Y', strtotime($request->tanggal_awal)).' s/d '.date('d/m/Y', strtotime($request->tanggal_akhir));
        } elseif ($request->filled('tanggal_awal')) {
            $periodeString = 'Mulai '.date('d/m/Y', strtotime($request->tanggal_awal));
        } elseif ($request->filled('tanggal_akhir')) {
            $periodeString = 'Sampai '.date('d/m/Y', strtotime($request->tanggal_akhir));
        }

        $pdf = Pdf::loadView('admin.tugas.pdf', [
            'title' => 'Laporan Data Tugas',
            'tugas' => $tugas,
            'periodeString' => $periodeString,
            'filterStatus' => $request->status ?? 'Semua Status',
            'filterKlasifikasi' => $request->klasifikasi ?: 'Semua Klasifikasi',
            'filterPetugas' => $filterPetugas,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Data_Tugas_'.date('Ymd_His').'.pdf');
    }

    /**
     * Store an uploaded file safely to public storage.
     * Prevents empty path issues on Windows/FastCGI environments where getRealPath() may return false.
     */
    protected function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        $realPath = $file->getRealPath();
        $sourcePath = (! empty($realPath)) ? $realPath : $file->getPathname();

        if (empty($sourcePath) || ! file_exists($sourcePath)) {
            throw new \RuntimeException('Berkas yang diunggah tidak ditemukan pada direktori sementara server.');
        }

        $hashName = $file->hashName();
        $storedPath = Storage::disk('public')->putFileAs($directory, $sourcePath, $hashName);

        if (! $storedPath) {
            throw new \RuntimeException('Gagal menyimpan berkas ke penyimpanan.');
        }

        return $storedPath;
    }

    /**
     * Refresh an officer's availability status based on remaining active tasks (primary or collaborator).
     */
    protected function refreshOfficerStatus(int $userId): void
    {
        $activeCount = Tugas::where('status', '!=', 'Selesai')
            ->where(function ($query) use ($userId): void {
                $query->where('petugas_id', $userId)
                    ->orWhereHas('kolaborator', fn ($k) => $k->where('users.id', $userId));
            })
            ->count();

        if ($activeCount === 0) {
            User::where('id', $userId)->update(['status' => 'Belum Ditugaskan']);
        }
    }

    /**
     * Notify target-unit supervisors (Kepala Sub Bagian or Panitera Muda) to approve a cross-unit delegation.
     */
    protected function notifyCrossUnitApprovers(Tugas $tugas, User $delegator, string $approverRole): void
    {
        $approvers = User::where('jabatan', $approverRole)
            ->where('id', '!=', $delegator->id)
            ->get();

        if ($approvers->isNotEmpty()) {
            Notification::send($approvers, new TugasNotification(
                tugas: $tugas,
                sender: $delegator,
                type: 'persetujuan_lintas_bidang',
                catatan: $tugas->deskripsi,
            ));
        }
    }
}
