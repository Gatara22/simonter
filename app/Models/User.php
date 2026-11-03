<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'nip', 'email', 'kontak', 'jabatan', 'status', 'password', 'foto'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the user's profile photo URL or default avatar.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && Storage::disk('public')->exists($this->foto)) {
            return asset('storage/'.$this->foto);
        }

        return asset('sbadmin2/img/undraw_profile.svg');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Tasks delegated by this user (Admin).
     */
    public function tugasDiberikan(): HasMany
    {
        return $this->hasMany(Tugas::class, 'admin_id');
    }

    /**
     * Tasks assigned to this user (Petugas).
     */
    public function tugasDiterima(): HasMany
    {
        return $this->hasMany(Tugas::class, 'petugas_id');
    }
}
