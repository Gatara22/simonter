<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PetunjukController extends Controller
{
    /**
     * Display the comprehensive application guide and documentation page.
     */
    public function index(): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser->jabatan === 'Admin';
        $isKasubbag = in_array($currentUser->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true);

        $data = [
            'title' => 'Petunjuk Aplikasi & Dokumentasi Sistem',
            'menuPetunjuk' => 'active',
            'user' => $currentUser,
            'isAdmin' => $isAdmin,
            'isKasubbag' => $isKasubbag,
        ];

        return view('petunjuk.index', $data);
    }
}
