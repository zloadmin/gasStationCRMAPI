<?php

namespace App\Enums\Traits;

use Illuminate\Http\Response;

/**
 * @method static cases()
 */
trait HasDictionaryResponse
{
    public static function dictionary(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    public static function response(): Response
    {
        return response([
            'data' => self::dictionary()
        ]);
    }
}
