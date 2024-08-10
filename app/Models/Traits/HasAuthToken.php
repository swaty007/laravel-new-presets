<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasAuthToken
{
    public function createAuthToken(): string
    {
        $token = Str::random(40);
        $this->forceFill([
            'auth_token' => $token
        ])->save();
        return $token;
    }
    public function getAuthToken(): string | null
    {
        return $this->auth_token;
    }
    public function removeAuthToken(): void
    {
        $this->forceFill([
            'auth_token' => null
        ])->save();
    }
}
