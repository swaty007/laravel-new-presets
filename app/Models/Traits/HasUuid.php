<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use RuntimeException;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function (Model $model) {
            $model->provideUuidKey($model);
        });
    }

    /**
     * @param $model
     * @return void
     */
    protected function provideUuidKey($model)
    {
        if ($model->incrementing) {
            report(new RuntimeException(
                sprintf('$incrementing must be false on class "%s" to support uuid', get_class($this))
            ));
        }

        if ($model->keyType !== 'uuid') {
            report(new RuntimeException(
                sprintf('$keyType must be "uuid" on class "%s" to support uuid', get_class($this))
            ));
        }

        $key = $model->getKeyName();
        if (empty($model->$key)) {
            $model->$key = Uuid::uuid4()->toString();
        }
    }
}
