<?php

namespace App\Models;

use App\Enums\AdminRole;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

/**
 * An account that signs in to the admin panel through the "admin" guard.
 *
 * Admins live in their own table, separate from App\Models\User, so access to
 * the panel never depends on the public "users" table.
 */
#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => AdminRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Posts this admin created.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === AdminRole::SuperAdmin;
    }

    /**
     * The first letters of the first and last names, e.g. "Ada M. Obi" gives "AO".
     */
    public function initials(): string
    {
        $words = Str::of($this->name)->squish()->explode(' ');

        $initials = Str::substr($words->first(), 0, 1);

        if ($words->count() > 1) {
            $initials .= Str::substr($words->last(), 0, 1);
        }

        return Str::upper($initials);
    }

    /**
     * Store emails in lower case so sign-in doesn't depend on how they were typed.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::lower(trim($value)),
        );
    }
}
