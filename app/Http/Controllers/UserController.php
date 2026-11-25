<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Ensure the currently authenticated user is an Admin.
     */
    protected function ensureAdmin(): ?RedirectResponse
    {
        if (Auth::user()?->jabatan !== 'Admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Hanya Administrator yang berwenang mengelola Data User.');
        }

        return null;
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $activeKlasifikasi = $request->query('klasifikasi');
        if (! in_array($activeKlasifikasi, ['Pimpinan', 'Kesekretariatan', 'Kepaniteraan', 'belum_diatur'], true)) {
            $activeKlasifikasi = null;
        }

        $allUsers = User::latest()->get();

        $userStats = [
            'total' => $allUsers->count(),
            'admin' => $allUsers->where('jabatan', 'Admin')->count(),
            'kasubbag' => $allUsers->where('jabatan', 'Kepala Sub Bagian')->count(),
            'panmud' => $allUsers->where('jabatan', 'Panitera Muda')->count(),
            'petugas' => $allUsers->where('jabatan', 'Petugas')->count(),
            'bertugas' => $allUsers->where('status', 'Sedang Bertugas')->count(),
            'pimpinan' => $allUsers->where('klasifikasi', 'Pimpinan')->count(),
            'kesekretariatan' => $allUsers->where('klasifikasi', 'Kesekretariatan')->count(),
            'kepaniteraan' => $allUsers->where('klasifikasi', 'Kepaniteraan')->count(),
            'belum_klasifikasi' => $allUsers->whereNull('klasifikasi')->count(),
        ];

        $users = $allUsers;
        if ($activeKlasifikasi === 'belum_diatur') {
            $users = $allUsers->whereNull('klasifikasi')->values();
        } elseif ($activeKlasifikasi !== null) {
            $users = $allUsers->where('klasifikasi', $activeKlasifikasi)->values();
        }

        $data = [
            'title' => 'Data User',
            'menuAdminUser' => 'active',
            'users' => $users,
            'userStats' => $userStats,
            'activeKlasifikasi' => $activeKlasifikasi,
        ];

        return view('admin.user.index', $data);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View|RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $data = [
            'title' => 'Tambah Data User',
            'menuAdminUser' => 'active',
        ];

        return view('admin.user.create', $data);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'kontak' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'in:Admin,Kepala Sub Bagian,Panitera Muda,Petugas'],
            'klasifikasi' => ['nullable', 'string', 'in:Pimpinan,Kesekretariatan,Kepaniteraan'],
            'status' => ['required', 'string', 'in:Belum Ditugaskan,Sedang Bertugas'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'klasifikasi.in' => 'Klasifikasi tidak valid (pilih Pimpinan, Kesekretariatan, atau Kepaniteraan).',
            'status.required' => 'Status penugasan wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create($validated);

        return redirect()->route('user.index')->with('success', 'Data user berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View|RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $data = [
            'title' => 'Edit Data User',
            'menuAdminUser' => 'active',
            'user' => $user,
        ];

        return view('admin.user.edit', $data);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip,'.$user->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'kontak' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'in:Admin,Kepala Sub Bagian,Panitera Muda,Petugas'],
            'klasifikasi' => ['nullable', 'string', 'in:Pimpinan,Kesekretariatan,Kepaniteraan'],
            'status' => ['required', 'string', 'in:Belum Ditugaskan,Sedang Bertugas'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'klasifikasi.in' => 'Klasifikasi tidak valid (pilih Pimpinan, Kesekretariatan, atau Kepaniteraan).',
            'status.required' => 'Status penugasan wajib dipilih.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('user.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', 'Data user berhasil dihapus.');
    }

    /**
     * Export user report to Excel (.xls) with optional period filter.
     */
    public function exportExcel(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $query = User::query();

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->query('tanggal_akhir'));
        }
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->query('jabatan'));
        }
        if ($request->query('klasifikasi') === 'belum_diatur') {
            $query->whereNull('klasifikasi');
        } elseif ($request->filled('klasifikasi')) {
            $query->where('klasifikasi', $request->query('klasifikasi'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $users = $query->latest()->get();

        $periodeString = 'Semua Waktu';
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $periodeString = date('d/m/Y', strtotime($request->tanggal_awal)).' s/d '.date('d/m/Y', strtotime($request->tanggal_akhir));
        } elseif ($request->filled('tanggal_awal')) {
            $periodeString = 'Mulai '.date('d/m/Y', strtotime($request->tanggal_awal));
        } elseif ($request->filled('tanggal_akhir')) {
            $periodeString = 'Sampai '.date('d/m/Y', strtotime($request->tanggal_akhir));
        }

        $filterKlasifikasi = match ($request->query('klasifikasi')) {
            'belum_diatur' => 'Belum Diatur',
            'Pimpinan', 'Kesekretariatan', 'Kepaniteraan' => $request->query('klasifikasi'),
            default => 'Semua Klasifikasi',
        };

        $filename = 'Laporan_Data_User_'.date('Ymd_His').'.xls';

        $content = view('admin.user.excel', [
            'users' => $users,
            'periodeString' => $periodeString,
            'filterJabatan' => $request->jabatan ?? 'Semua Jabatan',
            'filterKlasifikasi' => $filterKlasifikasi,
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
     * Export user report to PDF with optional period filter.
     */
    public function exportPdf(Request $request): Response|RedirectResponse
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $query = User::query();

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('created_at', '>=', $request->query('tanggal_awal'));
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->query('tanggal_akhir'));
        }
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->query('jabatan'));
        }
        if ($request->query('klasifikasi') === 'belum_diatur') {
            $query->whereNull('klasifikasi');
        } elseif ($request->filled('klasifikasi')) {
            $query->where('klasifikasi', $request->query('klasifikasi'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $users = $query->latest()->get();

        $periodeString = 'Semua Waktu';
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $periodeString = date('d/m/Y', strtotime($request->tanggal_awal)).' s/d '.date('d/m/Y', strtotime($request->tanggal_akhir));
        } elseif ($request->filled('tanggal_awal')) {
            $periodeString = 'Mulai '.date('d/m/Y', strtotime($request->tanggal_awal));
        } elseif ($request->filled('tanggal_akhir')) {
            $periodeString = 'Sampai '.date('d/m/Y', strtotime($request->tanggal_akhir));
        }

        $filterKlasifikasi = match ($request->query('klasifikasi')) {
            'belum_diatur' => 'Belum Diatur',
            'Pimpinan', 'Kesekretariatan', 'Kepaniteraan' => $request->query('klasifikasi'),
            default => 'Semua Klasifikasi',
        };

        $pdf = Pdf::loadView('admin.user.pdf', [
            'title' => 'Laporan Data User',
            'users' => $users,
            'periodeString' => $periodeString,
            'filterJabatan' => $request->jabatan ?? 'Semua Jabatan',
            'filterKlasifikasi' => $filterKlasifikasi,
            'filterStatus' => $request->status ?? 'Semua Status',
        ]);

        return $pdf->download('Laporan_Data_User_'.date('Ymd_His').'.pdf');
    }
}
