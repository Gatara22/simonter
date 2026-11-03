<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    /**
     * Display the account settings page for authenticated user.
     */
    public function index(): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $data = [
            'title' => 'Pengaturan Akun',
            'menuPengaturan' => 'active',
            'user' => $currentUser,
            'isAdmin' => $currentUser->jabatan === 'Admin',
        ];

        return view('pengaturan.index', $data);
    }

    /**
     * Update account settings (profile photo, phone number, and password).
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'kontak' => ['nullable', 'string', 'max:20'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'hapus_foto' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'kontak.max' => 'Nomor telepon / kontak maksimal 20 karakter.',
            'foto.image' => 'Berkas foto profil harus berupa file gambar.',
            'foto.mimes' => 'Format foto profil harus berupa JPEG, PNG, JPG, atau WEBP.',
            'foto.max' => 'Ukuran file foto profil maksimal 2 MB.',
            'current_password.required_with' => 'Password saat ini wajib diisi jika ingin mengubah password.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'password.min' => 'Password baru minimal harus terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Handle removing profile photo
        if ($request->boolean('hapus_foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = null;
        }

        // Handle uploading new profile photo
        if ($request->hasFile('foto')) {
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->file('foto');
            $user->foto = $this->storeUploadedFile($uploadedFile, 'avatars');
        }

        // Update phone number
        $user->kontak = $request->input('kontak');

        // Update password if filled
        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        $user->save();

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan profil Anda berhasil diperbarui.');
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
            throw new \RuntimeException('Berkas foto profil yang diunggah tidak ditemukan pada direktori sementara server.');
        }

        $hashName = $file->hashName();
        $storedPath = Storage::disk('public')->putFileAs($directory, $sourcePath, $hashName);

        if (! $storedPath) {
            throw new \RuntimeException('Gagal menyimpan foto profil ke penyimpanan.');
        }

        return $storedPath;
    }
}
