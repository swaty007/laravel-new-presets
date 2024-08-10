<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DatabaseSession extends Model
{
    use MassPrunable;

    /**
     * @var string
     */
    protected $table = 'sessions';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];
    /**
     * @var string[]
     */
    protected $casts = [
        'last_activity' => 'datetime',
    ];

    // Метод для расшифровки и десериализации payload

    /**
     * @param $value
     * @return mixed|null
     */
    public function getPayloadAttribute($value): mixed
    {
        try {
            $decryptedPayload = Crypt::decrypt(base64_decode($value));
            return unserialize($decryptedPayload);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return $this
            ->where('last_activity', '<=', now()->subDays(30)->getTimestamp() - (config('session.lifetime') * 60));
    }
}
