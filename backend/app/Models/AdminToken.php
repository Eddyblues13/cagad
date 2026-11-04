<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One admin sign-in from one browser, sent by the React admin as a bearer token.
 *
 * Only a SHA-256 hash is stored, so the plain-text token exists only in the
 * browser that signed in and a copy of the database can't be used to sign in.
 */
#[Fillable(['token', 'expires_at'])]
class AdminToken extends Model
{
    private const LIFETIME_HOURS = 24;

    private const REMEMBERED_LIFETIME_DAYS = 30;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Sign the admin in and return the plain-text token, which is never stored.
     * Their expired tokens are cleared out at the same time.
     *
     * @return array{token: string, expires_at: string}
     */
    public static function issue(Admin $admin, bool $remember): array
    {
        $admin->tokens()->where('expires_at', '<=', now())->delete();

        $plainText = Str::random(64);
        $token = $admin->tokens()->create([
            'token' => self::hash($plainText),
            'expires_at' => $remember ? now()->addDays(self::REMEMBERED_LIFETIME_DAYS) : now()->addHours(self::LIFETIME_HOURS),
        ]);

        return ['token' => $plainText, 'expires_at' => $token->expires_at->toIso8601String()];
    }

    /**
     * The unexpired token matching a bearer token from a request, if any.
     */
    public static function findValid(?string $plainText): ?self
    {
        if (blank($plainText)) {
            return null;
        }

        return self::with('admin')
            ->where('token', self::hash($plainText))
            ->where('expires_at', '>', now())
            ->first();
    }

    private static function hash(string $plainText): string
    {
        return hash('sha256', $plainText);
    }
}
