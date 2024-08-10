<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\DatabaseSession;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasDatabaseSessions
{
    public function databaseSessions(): HasMany
    {
        return $this->hasMany(DatabaseSession::class, 'user_id')
            ->orderByDesc('last_activity');
    }
}
