<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    public static function boot()
    {
        parent::boot();
        // When updating, cancel normal update and manually update
        // the table asynchronously every hour.
        static::updating(function (self $personalAccessToken) {
            $dirty = $personalAccessToken->getDirty();
            if (
                count($dirty) !== 1 ||
                !isset($dirty['last_used_at']) ||
                !Cache::has('user-personal-token-' . $personalAccessToken->id)
            ) {
                Cache::put('user-personal-token-' . $personalAccessToken->id, true, 60);
                return true;
            }
            return false;
        });
    }
    /**
     * Limit saving of PersonalAccessToken records
     *
     * We only want to actually save when there is something other than
     * the last_used_at column that has changed. It prevents extra DB writes
     * since we aren't going to use that column for anything.
     *
     * @param  array  $options
     * @return bool
     */
    //    public function save(array $options = [])
    //    {
    //        $changes = $this->getDirty();
    //        // Check for 2 changed values because one is always the updated_at column
    //        if (! array_key_exists('last_used_at', $changes) || count($changes) > 2) {
    //            parent::save();
    //        }
    //        return false;
    //    }

    /**
     * Get the entity's additionalData.
     *
     * @return MorphOne
     */
    //    public function additionalData(): MorphOne
    //    {
    //        return $this->morphOne(AdditionalData::class, 'model');
    //    }
}
