<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

final readonly class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse(mixed $request): JsonResponse|Response
    {

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended(route('hunts.index', absolute: false));
    }
}
