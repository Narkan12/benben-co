<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'session_reference',
        'ip_address',
        'user_agent',
        'device_info',
        'login_at',
        'last_activity_at',
        'logout_at',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'logout_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public static function hashReference(int $userId, string $sessionId): string
    {
        return hash('sha256', $userId.'|'.$sessionId.'|'.config('app.key'));
    }

    protected static function tableReady(): bool
    {
        return Schema::hasTable((new static)->getTable());
    }

    public static function registerActiveSession(User $user, Request $request): ?self
    {
        if (! self::tableReady()) {
            return null;
        }

        $reference = self::hashReference((int) $user->id_user, (string) $request->session()->getId());

        $session = self::query()->where('session_reference', $reference)->first();
        if (! $session) {
            $session = new self();
        }

        $session->user_id = $user->id_user;
        $session->session_reference = $reference;
        $session->ip_address = $request->ip() ?: null;
        $session->user_agent = $request->userAgent();
        $session->device_info = self::detectDeviceInfo($request);
        $session->login_at = $session->login_at ?? now();
        $session->last_activity_at = now();
        $session->logout_at = null;
        $session->status = 'active';
        $session->save();

        return $session;
    }

    public static function syncExpiredSessions(?int $userId = null): void
    {
        if (! self::tableReady()) {
            return;
        }

        $query = self::query()
            ->where('status', 'active')
            ->where('last_activity_at', '<', now()->subMinutes((int) config('session.lifetime', 120)));

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $query->update([
            'status' => 'expired',
            'logout_at' => now(),
        ]);
    }

    public static function closeCurrentSession(User $user, Request $request): void
    {
        if (! self::tableReady()) {
            return;
        }

        $reference = self::hashReference((int) $user->id_user, (string) $request->session()->getId());

        self::query()
            ->where('session_reference', $reference)
            ->update([
                'status' => 'logged_out',
                'logout_at' => now(),
                'last_activity_at' => now(),
            ]);
    }

    protected static function detectDeviceInfo(Request $request): ?string
    {
        $deviceParts = array_filter([
            $request->header('Sec-CH-UA-Platform'),
            $request->header('Sec-CH-UA-Model'),
            $request->header('User-Agent'),
        ]);

        return empty($deviceParts) ? null : implode(' • ', $deviceParts);
    }
}
