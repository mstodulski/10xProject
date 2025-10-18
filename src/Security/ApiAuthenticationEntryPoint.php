<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Custom authentication entry point for API endpoints
 * Returns JSON error instead of redirecting to login page
 */
class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $message = $authException?->getMessage() ?? 'Wymagane uwierzytelnienie';

        return new JsonResponse([
            'success' => false,
            'error' => $message
        ], Response::HTTP_UNAUTHORIZED);
    }
}
