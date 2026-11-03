<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';
        $canDelegate = $isAdmin || $isKasubbag;

        if ($isAdmin) {
            $tugasQuery = Tugas::with(['admin', 'petugas'])->latest();

            $totalTugas = Tugas::count();
            $totalDidelegasikan = Tugas::whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])->count();
            $totalMenungguKonfirmasi = Tugas::where('status', 'Menunggu Konfirmasi')->count();
            $totalSelesai = Tugas::where('status', 'Selesai')->count();
        } elseif ($isKasubbag) {
            $baseScope = function ($q) use ($currentUser) {
                $q->where('petugas_id', $currentUser->id)
                    ->orWhere('admin_id', $currentUser->id);
            };

            $tugasQuery = Tugas::with(['admin', 'petugas'])
                ->where($baseScope)
                ->latest();

            $totalTugas = Tugas::where($baseScope)->count();
            $totalDidelegasikan = Tugas::where($baseScope)
                ->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])
                ->count();
            $totalMenungguKonfirmasi = Tugas::where($baseScope)
                ->where('status', 'Menunggu Konfirmasi')
                ->count();
            $totalSelesai = Tugas::where($baseScope)
                ->where('status', 'Selesai')
                ->count();
        } else {
            $tugasQuery = Tugas::with(['admin', 'petugas'])
                ->where('petugas_id', $currentUser->id)
                ->latest();

            $totalTugas = Tugas::where('petugas_id', $currentUser->id)->count();
            $totalDidelegasikan = Tugas::where('petugas_id', $currentUser->id)
                ->whereIn('status', ['Didelegasikan', 'Sedang Dikerjakan'])
                ->count();
            $totalMenungguKonfirmasi = Tugas::where('petugas_id', $currentUser->id)
                ->where('status', 'Menunggu Konfirmasi')
                ->count();
            $totalSelesai = Tugas::where('petugas_id', $currentUser->id)
                ->where('status', 'Selesai')
                ->count();
        }

        // Optional status filter
        if ($request->filled('status')) {
            $tugasQuery->where('status', $request->query('status'));
        }

        $title = 'Data Tugas Saya';
        if ($isAdmin) {
            $title = 'Data Tugas (Delegasi Admin)';
        } elseif ($isKasubbag) {
            $title = 'Data Tugas & Delegasi (Kepala Sub Bagian)';
        }

        $petugasList = collect();
        if ($isAdmin) {
            $petugasList = User::whereIn('jabatan', ['Kepala Sub Bagian', 'Petugas'])->orderBy('name')->get();
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
            'stats' => [
                'total' => $totalTugas,
                'didelegasikan' => $totalDidelegasikan,
                'menunggu' => $totalMenungguKonfirmasi,
                'selesai' => $totalSelesai,
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        if (! $isAdmin && ! $isKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin dan Kepala Sub Bagian yang dapat mendelegasikan tugas.');
        }

        $petugasList = $isAdmin
            ? User::whereIn('jabatan', ['Kepala Sub Bagian', 'Petugas'])->orderBy('name')->get()
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
            'menuAdminTugas' => $isAdmin ? 'active' : '',
            'menuKasubbagTugas' => $isKasubbag ? 'active' : '',
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
            'petugasList' => $petugasList,
            'sumberTugas' => $sumberTugas,
        ];

        return view('admin.tugas.create', $data);
    }

    /**
     * Store a newly delegated task.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        if (! $isAdmin && ! $isKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin dan Kepala Sub Bagian yang dapat mendelegasikan tugas.');
        }

        $allowedRecipientRoles = $isAdmin ? ['Kepala Sub Bagian', 'Petugas'] : ['Petugas'];

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'petugas_id' => [
                'required',
                Rule::exists('users', 'id')->whereIn('jabatan', $allowedRecipientRoles),
            ],
            'deskripsi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'file_tugas' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png'],
            'sumber_tugas_id' => ['nullable', 'exists:tugas,id'],
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'petugas_id.required' => 'Pilih petugas pelaksana tugas.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak valid atau tidak sesuai wewenang.',
            'deskripsi.required' => 'Deskripsi tugas dan instruksi wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai tugas wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai / deadline wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'file_tugas.file' => 'Berkas lampiran tugas harus berupa file yang valid.',
            'file_tugas.max' => 'Ukuran file lampiran tugas maksimal 10 MB.',
        ]);

        if ($request->hasFile('file_tugas')) {
            $file = $request->file('file_tugas');
            $path = $this->storeUploadedFile($file, 'tugas');
            $validated['file_tugas'] = $path;
            $validated['file_tugas_name'] = $file->getClientOriginalName();
        } elseif ($request->filled('sumber_tugas_id')) {
            $sumberTugas = Tugas::find($request->input('sumber_tugas_id'));
            if ($sumberTugas && $sumberTugas->file_tugas) {
                $validated['file_tugas'] = $sumberTugas->file_tugas;
                $validated['file_tugas_name'] = $sumberTugas->file_tugas_name;
            }
        }

        unset($validated['sumber_tugas_id']);

        $validated['admin_id'] = $currentUser->id;
        $validated['status'] = 'Didelegasikan';

        $tugas = Tugas::create($validated);

        $targetUser = User::find($validated['petugas_id']);
        $tugas->recordLog(
            action: 'delegasi',
            status: 'Didelegasikan',
            keterangan: 'Tugas dibuat dan didelegasikan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') kepada '.($targetUser?->name ?? 'Pelaksana').' ('.($targetUser?->jabatan ?? 'Petugas').').',
            catatan: null,
            userId: $currentUser->id,
        );

        // Update officer's status to 'Sedang Bertugas'
        User::where('id', $validated['petugas_id'])->update(['status' => 'Sedang Bertugas']);

        if ($targetUser && $targetUser->id !== $currentUser->id) {
            $targetUser->notify(new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'tugas_baru',
                catatan: $tugas->deskripsi,
            ));
        }

        return redirect()->route('tugas.index')->with('success', 'Tugas berhasil didelegasikan kepada petugas.');
    }

    /**
     * Delegate a task received by Kepala Sub Bagian (or Admin) to a Petugas.
     */
    public function delegasikanKePetugas(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        if (! $isAdmin && (! $isKasubbag || $tugas->petugas_id !== $currentUser->id)) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk mendelegasikan tugas ini.');
        }

        $validated = $request->validate([
            'petugas_id' => [
                'required',
                Rule::exists('users', 'id')->where('jabatan', 'Petugas'),
            ],
            'catatan_delegasi' => ['nullable', 'string'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:'.$tugas->tanggal_mulai->format('Y-m-d')],
        ], [
            'petugas_id.required' => 'Pilih Petugas yang akan menerima delegasi tugas.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak valid (harus jabatan Petugas).',
            'tanggal_selesai.after_or_equal' => 'Batas selesai tidak boleh sebelum tanggal mulai tugas.',
        ]);

        $previousAssigneeId = $tugas->petugas_id;

        if (! empty($validated['catatan_delegasi'])) {
            $tugas->deskripsi = $tugas->deskripsi."\n\n[Instruksi Tambahan ".$currentUser->name."]:\n".$validated['catatan_delegasi'];
        }

        if (! empty($validated['tanggal_selesai'])) {
            $tugas->tanggal_selesai = $validated['tanggal_selesai'];
        }

        $tugas->admin_id = $currentUser->id;
        $tugas->petugas_id = (int) $validated['petugas_id'];
        $tugas->status = 'Didelegasikan';
        $tugas->save();

        $targetPetugas = User::find($tugas->petugas_id);
        $tugas->recordLog(
            action: 'delegasi',
            status: 'Didelegasikan',
            keterangan: 'Tugas didelegasikan lanjutan oleh '.$currentUser->name.' ('.$currentUser->jabatan.') kepada '.($targetPetugas?->name ?? 'Petugas').' (Petugas).',
            catatan: ! empty($validated['catatan_delegasi']) ? $validated['catatan_delegasi'] : null,
            userId: $currentUser->id,
        );

        // Update new Petugas status to 'Sedang Bertugas'
        User::where('id', $tugas->petugas_id)->update(['status' => 'Sedang Bertugas']);

        if ($targetPetugas && $targetPetugas->id !== $currentUser->id) {
            $targetPetugas->notify(new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'delegasi_lanjutan',
                catatan: ! empty($validated['catatan_delegasi']) ? $validated['catatan_delegasi'] : $tugas->deskripsi,
            ));
        }

        // Update previous assignee (e.g. Kepala Sub Bagian) status if they have no other active assigned tasks
        $activeCount = Tugas::where('petugas_id', $previousAssigneeId)
            ->where('status', '!=', 'Selesai')
            ->count();

        if ($activeCount === 0) {
            User::where('id', $previousAssigneeId)->update(['status' => 'Belum Ditugaskan']);
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Tugas berhasil didelegasikan kepada Petugas.');
    }

    /**
     * Display the specified task details.
     */
    public function show(Tugas $tugas): View|RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';
        $canManageTask = $isAdmin || ($isKasubbag && $tugas->admin_id === $currentUser->id);

        // Non-admin can only view tasks assigned to them or delegated by them
        if (! $isAdmin && $tugas->petugas_id !== $currentUser->id && $tugas->admin_id !== $currentUser->id) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki akses ke tugas ini.');
        }

        // Automatically mark unread notifications related to this task for the viewing user as read
        $currentUser->unreadNotifications
            ->filter(fn ($n): bool => ($n->data['tugas_id'] ?? null) === $tugas->id)
            ->markAsRead();

        // If the task was just delegated and the assigned officer opens it, mark it in progress
        if (! $isAdmin && $tugas->status === 'Didelegasikan' && $tugas->petugas_id === $currentUser->id) {
            $tugas->update(['status' => 'Sedang Dikerjakan']);
            $tugas->recordLog(
                action: 'mulai_dikerjakan',
                status: 'Sedang Dikerjakan',
                keterangan: 'Tugas dibuka dan mulai dikerjakan oleh pelaksana '.$currentUser->name.' ('.$currentUser->jabatan.').',
                catatan: null,
                userId: $currentUser->id,
            );
        }

        $tugas->load(['admin', 'petugas', 'logs.user']);

        $petugasList = ($isAdmin || $isKasubbag)
            ? User::where('jabatan', 'Petugas')->orderBy('name')->get()
            : collect();

        $data = [
            'title' => 'Detail Tugas: '.$tugas->judul,
            'menuAdminTugas' => $isAdmin ? 'active' : '',
            'menuKasubbagTugas' => $isKasubbag ? 'active' : '',
            'menuPetugasTugas' => (! $isAdmin && ! $isKasubbag) ? 'active' : '',
            'tugas' => $tugas,
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
            'canManageTask' => $canManageTask,
            'petugasList' => $petugasList,
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        if (! $isAdmin && (! $isKasubbag || ($tugas->admin_id !== $currentUser->id && $tugas->petugas_id !== $currentUser->id))) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin dan Kepala Sub Bagian terkait yang dapat mengedit tugas.');
        }

        $petugasList = $isAdmin
            ? User::whereIn('jabatan', ['Kepala Sub Bagian', 'Petugas'])->orderBy('name')->get()
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        if (! $isAdmin && (! $isKasubbag || ($tugas->admin_id !== $currentUser->id && $tugas->petugas_id !== $currentUser->id))) {
            return redirect()->route('tugas.index')->with('error', 'Hanya Admin dan Kepala Sub Bagian terkait yang dapat mengedit tugas.');
        }

        $allowedRecipientRoles = $isAdmin ? ['Kepala Sub Bagian', 'Petugas'] : ['Petugas'];

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
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
            'petugas_id.required' => 'Pilih petugas pelaksana tugas.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak terdaftar atau tidak sesuai wewenang.',
            'deskripsi.required' => 'Deskripsi tugas dan instruksi wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai tugas wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai / deadline wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'file_tugas.file' => 'Berkas lampiran tugas harus berupa file yang valid.',
            'file_tugas.max' => 'Ukuran file lampiran tugas maksimal 10 MB.',
        ]);

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

        $tugas->update($validated);

        $tugas->recordLog(
            action: 'update',
            status: $tugas->status,
            keterangan: 'Data dan ketentuan tugas diperbarui oleh '.$currentUser->name.' ('.$currentUser->jabatan.').',
            catatan: null,
            userId: $currentUser->id,
        );

        if ((int) $validated['petugas_id'] !== $previousAssigneeId) {
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

            $activeCount = Tugas::where('petugas_id', $previousAssigneeId)
                ->where('status', '!=', 'Selesai')
                ->count();

            if ($activeCount === 0) {
                User::where('id', $previousAssigneeId)->update(['status' => 'Belum Ditugaskan']);
            }
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

        if ($currentUser->jabatan !== 'Admin' && $tugas->petugas_id !== $currentUser->id) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk menindaklanjuti tugas ini.');
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

        if ($currentUser->jabatan !== 'Admin' && $tugas->petugas_id !== $currentUser->id) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk menindaklanjuti tugas ini.');
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
     * Admin or delegating Kepala Sub Bagian review and verification of submitted task (Complete or Request Revision).
     */
    public function verifikasi(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isDelegatorKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian' && $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isDelegatorKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk memverifikasi tugas ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Selesai,Perlu Revisi'],
            'catatan_admin' => ['nullable', 'string'],
        ], [
            'status.required' => 'Pilih keputusan verifikasi.',
            'status.in' => 'Status verifikasi tidak valid.',
        ]);

        $tugas->status = $validated['status'];
        $tugas->catatan_admin = $validated['catatan_admin'] ?? null;
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

        // Notify the assigned officer of verification result (Selesai or Perlu Revisi)
        $petugas = $tugas->petugas;
        if ($petugas && $petugas->id !== $currentUser->id) {
            $petugas->notify(new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: $validated['status'] === 'Selesai' ? 'selesai' : 'revisi',
                catatan: $validated['catatan_admin'] ?? null,
            ));
        }

        // If completed, check if officer has any other active tasks
        if ($validated['status'] === 'Selesai') {
            $activeCount = Tugas::where('petugas_id', $tugas->petugas_id)
                ->where('id', '!=', $tugas->id)
                ->where('status', '!=', 'Selesai')
                ->count();

            if ($activeCount === 0) {
                User::where('id', $tugas->petugas_id)->update(['status' => 'Belum Ditugaskan']);
            }
        }

        $message = $validated['status'] === 'Selesai'
            ? 'Tugas berhasil diverifikasi dan ditandai Selesai.'
            : 'Tugas telah dikembalikan ke petugas untuk revisi.';

        return redirect()->route('tugas.show', $tugas->id)->with('success', $message);
    }

    /**
     * Reopen a closed task and redelegate to the assigned officer due to work errors.
     */
    public function bukaKembali(Request $request, Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isDelegatorKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian' && $tugas->admin_id === $currentUser->id;

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

        // Update assigned officer's status back to 'Sedang Bertugas'
        User::where('id', $tugas->petugas_id)->update(['status' => 'Sedang Bertugas']);

        $petugas = $tugas->petugas;
        $tugas->recordLog(
            action: 'buka_kembali',
            status: 'Didelegasikan',
            keterangan: 'Tugas yang telah ditutup (closed) dibuka kembali dan didelegasikan ulang kepada '.($petugas?->name ?? 'Petugas').' ('.($petugas?->jabatan ?? 'Petugas').') oleh '.$currentUser->name.' ('.$currentUser->jabatan.') karena terdapat kesalahan/perbaikan pengerjaan.',
            catatan: $validated['catatan'],
            userId: $currentUser->id,
        );

        if ($petugas && $petugas->id !== $currentUser->id) {
            $petugas->notify(new TugasNotification(
                tugas: $tugas,
                sender: $currentUser,
                type: 'buka_kembali',
                catatan: $validated['catatan'],
            ));
        }

        return redirect()->route('tugas.show', $tugas->id)->with('success', 'Tugas berhasil dibuka kembali dan didelegasikan ulang kepada petugas yang bersangkutan.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Tugas $tugas): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isDelegatorKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian' && $tugas->admin_id === $currentUser->id;

        if (! $isAdmin && ! $isDelegatorKasubbag) {
            return redirect()->route('tugas.index')->with('error', 'Anda tidak memiliki wewenang untuk menghapus tugas ini.');
        }

        $petugasId = $tugas->petugas_id;

        if ($tugas->file_tugas) {
            Storage::disk('public')->delete($tugas->file_tugas);
        }
        if ($tugas->file_evidence) {
            Storage::disk('public')->delete($tugas->file_evidence);
        }

        $tugas->delete();

        // Check if officer still has other active tasks
        $activeCount = Tugas::where('petugas_id', $petugasId)
            ->where('status', '!=', 'Selesai')
            ->count();

        if ($activeCount === 0) {
            User::where('id', $petugasId)->update(['status' => 'Belum Ditugaskan']);
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

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
            $query->where('petugas_id', $currentUser->id);
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
        $isKasubbag = $currentUser->jabatan === 'Kepala Sub Bagian';

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
            $query->where('petugas_id', $currentUser->id);
            $filterPetugas = $currentUser->name;
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
}
