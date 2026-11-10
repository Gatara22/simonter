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
     * Display a listing of the users.
     */
    public function index(): View
    {
        $data = [
            'title' => 'Data User',
            'menuAdminUser' => 'active',
            'users' => User::latest()->get(),
        ];

        return view('admin.user.index', $data);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'kontak' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'in:Admin,Kepala Sub Bagian,Panitera Muda,Petugas'],
            'status' => ['required', 'string', 'in:Belum Ditugaskan,Sedang Bertugas'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
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
    public function edit(User $user): View
    {
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:users,nip,'.$user->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'kontak' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'in:Admin,Kepala Sub Bagian,Panitera Muda,Petugas'],
            'status' => ['required', 'string', 'in:Belum Ditugaskan,Sedang Bertugas'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
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
        if ($user->id === Auth::id()) {
            return redirect()->route('user.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', 'Data user berhasil dihapus.');
    }

    /**
     * Export user report to Excel (.xls) with optional period filter.
     */
    public function exportExcel(Request $request): Response
    {
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

        $filename = 'Laporan_Data_User_'.date('Ymd_His').'.xls';

        $content = view('admin.user.excel', [
            'users' => $users,
            'periodeString' => $periodeString,
            'filterJabatan' => $request->jabatan ?? 'Semua Jabatan',
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
    public function exportPdf(Request $request): Response
    {
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

        $pdf = Pdf::loadView('admin.user.pdf', [
            'title' => 'Laporan Data User',
            'users' => $users,
            'periodeString' => $periodeString,
            'filterJabatan' => $request->jabatan ?? 'Semua Jabatan',
            'filterStatus' => $request->status ?? 'Semua Status',
        ]);

        return $pdf->download('Laporan_Data_User_'.date('Ymd_His').'.pdf');
    }
}
