<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\UserChecker;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * API Controller for user authentication
 */
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserChecker $userChecker,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Authenticate user and return session ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/authorize', name: 'api_authorize', methods: ['POST'])]
    public function authorize(Request $request): JsonResponse
    {
        try {
            // Get JSON data from request
            $data = json_decode($request->getContent(), true);

            // Validate input data
            if (!isset($data['username']) || !isset($data['password'])) {
                $this->logger->warning('Missing credentials in authorization request', [
                    'hasUsername' => isset($data['username']),
                    'hasPassword' => isset($data['password'])
                ]);

                return new JsonResponse([
                    'success' => false,
                    'error' => 'Nieprawidłowe dane. Wymagane pola: username, password'
                ], Response::HTTP_BAD_REQUEST);
            }

            $username = trim($data['username']);
            $password = $data['password'];

            // Validate non-empty fields
            if (empty($username) || empty($password)) {
                $this->logger->warning('Empty credentials in authorization request');

                return new JsonResponse([
                    'success' => false,
                    'error' => 'Nazwa użytkownika i hasło nie mogą być puste'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Find user by username
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                $this->logger->warning('User not found during authorization', [
                    'username' => $username
                ]);

                return new JsonResponse([
                    'success' => false,
                    'error' => 'Nieprawidłowa nazwa użytkownika lub hasło'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Check if user is active (using UserChecker)
            try {
                $this->userChecker->checkPreAuth($user);
            } catch (CustomUserMessageAccountStatusException $e) {
                $this->logger->warning('Inactive user tried to authenticate', [
                    'username' => $username,
                    'userId' => $user->getId()
                ]);

                return new JsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Verify password
            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                $this->logger->warning('Invalid password during authorization', [
                    'username' => $username,
                    'userId' => $user->getId()
                ]);

                return new JsonResponse([
                    'success' => false,
                    'error' => 'Nieprawidłowa nazwa użytkownika lub hasło'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Create authentication token and log in the user
            $token = new UsernamePasswordToken(
                $user,
                'api', // firewall name
                $user->getRoles()
            );

            // Store the token in the token storage
            $this->container->get('security.token_storage')->setToken($token);

            // Get session and ensure it's started
            $session = $request->getSession();
            $session->set('_security_api', serialize($token));

            // Get session ID
            $sessionId = $session->getId();

            $this->logger->info('User authenticated successfully via API', [
                'username' => $username,
                'userId' => $user->getId(),
                'sessionId' => $sessionId
            ]);

            // Return success response with session ID
            return new JsonResponse([
                'success' => true,
                'message' => 'Zalogowano pomyślnie',
                'sessionId' => $sessionId,
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'roles' => $user->getRoles()
                ]
            ], Response::HTTP_OK);

        } catch (\JsonException $e) {
            $this->logger->error('Invalid JSON in authorization request', [
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Nieprawidłowy format danych JSON'
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            $this->logger->error('Unexpected error during authorization', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Wystąpił błąd serwera'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout user and invalidate session
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        try {
            // Get current user (if authenticated)
            $user = $this->getUser();

            if (!$user) {
                $this->logger->warning('Logout attempt without authentication');

                return new JsonResponse([
                    'success' => false,
                    'error' => 'Użytkownik nie jest zalogowany'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Log the logout action
            $this->logger->info('User logging out via API', [
                'username' => $user->getUserIdentifier(),
                'userId' => $user->getId(),
                'sessionId' => $request->getSession()->getId()
            ]);

            // Get session before invalidating
            $session = $request->getSession();
            $sessionId = $session->getId();

            // Clear the security token
            $tokenStorage = $this->container->get('security.token_storage');
            $tokenStorage->setToken(null);

            // Invalidate the session
            $session->invalidate();

            $this->logger->info('User logged out successfully via API', [
                'username' => $user->getUserIdentifier(),
                'userId' => $user->getId(),
                'invalidatedSessionId' => $sessionId
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => 'Wylogowano pomyślnie'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $this->logger->error('Unexpected error during logout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Wystąpił błąd serwera'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
