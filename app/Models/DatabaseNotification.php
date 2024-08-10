<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Notifications\DatabaseNotification as ParentModel;

class DatabaseNotification extends ParentModel
{
    use MassPrunable;

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return $this
//            ->whereNotNull('read_at')
            ->where('created_at', '<=', now()->subDays(30));
    }
}
