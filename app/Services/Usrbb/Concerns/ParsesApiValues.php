<?php

namespace App\Services\Usrbb\Concerns;

use Illuminate\Support\Carbon;

trait ParsesApiValues
{
    /**
     * The API returns some nested objects as JSON-encoded strings.
     */
    protected function decodeJson(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (! is_string($value)) {
            return $value;
        }

        return json_decode($value, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Convert an ISO-8601 API timestamp to the application timezone.
     */
    protected function parseDate(?string $value): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value)->setTimezone(config('app.timezone'));
    }
}
