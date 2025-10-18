<?php

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for Authentication API endpoint
 *
 * Tests the /api/authorize endpoint:
 * - Invalid authentication attempts
 * - Error responses structure
 * - HTTP status codes
 *
 * Uses real database configured in the application
 */
class AuthenticationControllerTest extends WebTestCase
{
    /**
     * Test: Authentication with non-existent user returns 401
     *
     * Scenario:
     * - Attempt to login with random/non-existent username
     * - System should reject authentication
     * - Return 401 Unauthorized with error message
     */
    public function testAuthenticationWithNonExistentUserReturns401(): void
    {
        // Arrange
        $client = static::createClient();

        // Generate random username to ensure it doesn't exist
        $randomUsername = 'nonexistent_user_' . bin2hex(random_bytes(8));
        $randomPassword = 'random_password_' . bin2hex(random_bytes(8));

        // Act - attempt to authenticate with non-existent user
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $randomUsername,
                'password' => $randomPassword
            ])
        );

        // Assert - verify response
        $this->assertResponseStatusCodeSame(
            401,
            'Authentication with non-existent user should return 401 Unauthorized'
        );

        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);

        // Verify response structure
        $this->assertIsArray($response, 'Response should be a JSON array');
        $this->assertArrayHasKey('success', $response, 'Response should have "success" key');
        $this->assertArrayHasKey('error', $response, 'Response should have "error" key');

        // Verify response values
        $this->assertFalse($response['success'], 'Success should be false for failed authentication');
        $this->assertIsString($response['error'], 'Error should be a string');
        $this->assertNotEmpty($response['error'], 'Error message should not be empty');

        // Verify error message is generic (for security - don't reveal if user exists)
        $this->assertEquals(
            'Nieprawidłowa nazwa użytkownika lub hasło',
            $response['error'],
            'Error message should be generic and not reveal whether user exists'
        );

        // Verify that sessionId is NOT present in error response
        $this->assertArrayNotHasKey(
            'sessionId',
            $response,
            'Failed authentication should not return sessionId'
        );

        // Verify that user data is NOT present in error response
        $this->assertArrayNotHasKey(
            'user',
            $response,
            'Failed authentication should not return user data'
        );
    }

    /**
     * Test: Authentication with existing user but wrong password returns 401
     *
     * Scenario:
     * - Use existing username from fixtures (konsultant1)
     * - Provide incorrect password
     * - System should reject authentication
     * - Return same generic error message (security best practice)
     */
    public function testAuthenticationWithWrongPasswordReturns401(): void
    {
        // Arrange
        $client = static::createClient();

        // Use existing user from fixtures
        $existingUsername = 'konsultant1';
        $wrongPassword = 'definitely_wrong_password_' . bin2hex(random_bytes(8));

        // Act
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $existingUsername,
                'password' => $wrongPassword
            ])
        );

        // Assert
        $this->assertResponseStatusCodeSame(
            401,
            'Authentication with wrong password should return 401 Unauthorized'
        );

        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);

        // Verify response structure and values
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertFalse($response['success']);

        // Verify same generic error message as non-existent user (security)
        $this->assertEquals(
            'Nieprawidłowa nazwa użytkownika lub hasło',
            $response['error'],
            'Error message should be identical for wrong password and non-existent user'
        );

        $this->assertArrayNotHasKey('sessionId', $response);
        $this->assertArrayNotHasKey('user', $response);
    }

    /**
     * Test: Authentication with missing username field returns 400
     *
     * Scenario:
     * - Send request without username field
     * - System should reject request
     * - Return 400 Bad Request
     */
    public function testAuthenticationWithMissingUsernameReturns400(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - send request with only password (missing username)
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'somepassword'
            ])
        );

        // Assert
        $this->assertResponseStatusCodeSame(
            400,
            'Authentication with missing username should return 400 Bad Request'
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertFalse($response['success']);
        $this->assertEquals(
            'Nieprawidłowe dane. Wymagane pola: username, password',
            $response['error']
        );
    }

    /**
     * Test: Authentication with missing password field returns 400
     *
     * Scenario:
     * - Send request without password field
     * - System should reject request
     * - Return 400 Bad Request
     */
    public function testAuthenticationWithMissingPasswordReturns400(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - send request with only username (missing password)
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'someuser'
            ])
        );

        // Assert
        $this->assertResponseStatusCodeSame(
            400,
            'Authentication with missing password should return 400 Bad Request'
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertEquals(
            'Nieprawidłowe dane. Wymagane pola: username, password',
            $response['error']
        );
    }

    /**
     * Test: Authentication with empty credentials returns 400
     *
     * Scenario:
     * - Send request with empty username and password strings
     * - System should reject request
     * - Return 400 Bad Request
     */
    public function testAuthenticationWithEmptyCredentialsReturns400(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - send request with empty strings
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => '',
                'password' => ''
            ])
        );

        // Assert
        $this->assertResponseStatusCodeSame(
            400,
            'Authentication with empty credentials should return 400 Bad Request'
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertEquals(
            'Nazwa użytkownika i hasło nie mogą być puste',
            $response['error']
        );
    }

    /**
     * Test: Authentication with invalid JSON returns 400
     *
     * Scenario:
     * - Send request with malformed JSON
     * - System should reject request
     * - Return 400 Bad Request
     *
     * Note: When JSON is invalid, json_decode returns null, which is then
     * treated as missing fields by the controller
     */
    public function testAuthenticationWithInvalidJsonReturns400(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - send request with invalid JSON
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'this-is-not-valid-json{'
        );

        // Assert
        $this->assertResponseStatusCodeSame(
            400,
            'Authentication with invalid JSON should return 400 Bad Request'
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($response);
        $this->assertFalse($response['success']);

        // When JSON is malformed, json_decode returns null, which leads to missing fields error
        // This is acceptable behavior - the important part is returning 400
        $this->assertArrayHasKey('error', $response);
        $this->assertNotEmpty($response['error']);
    }

    /**
     * Test: Using GET method instead of POST returns 405 Method Not Allowed
     *
     * Scenario:
     * - Attempt to access /api/authorize with GET method
     * - Endpoint only allows POST
     * - Should return 405 Method Not Allowed
     */
    public function testAuthenticationWithGetMethodReturns405(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - use GET instead of POST
        $client->request('GET', '/api/authorize');

        // Assert
        $this->assertResponseStatusCodeSame(
            405,
            'GET request to /api/authorize should return 405 Method Not Allowed'
        );
    }

    /**
     * Test: Successful authentication returns 200 with sessionId
     * (Bonus test to verify correct authentication works)
     *
     * Scenario:
     * - Use valid credentials from fixtures
     * - System should authenticate successfully
     * - Return 200 OK with sessionId and user data
     */
    public function testSuccessfulAuthenticationReturns200WithSessionId(): void
    {
        // Arrange
        $client = static::createClient();

        // Use valid credentials from fixtures
        $validUsername = 'konsultant1';
        $validPassword = 'test'; // Password from fixtures

        // Act
        $client->request(
            'POST',
            '/api/authorize',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $validUsername,
                'password' => $validPassword
            ])
        );

        // Assert
        $this->assertResponseIsSuccessful('Valid credentials should result in successful authentication');
        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true);

        // Verify success response structure
        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('sessionId', $response);
        $this->assertArrayHasKey('user', $response);

        // Verify values
        $this->assertTrue($response['success']);
        $this->assertIsString($response['sessionId']);
        $this->assertNotEmpty($response['sessionId'], 'Session ID should not be empty');

        // Verify user data structure
        $this->assertIsArray($response['user']);
        $this->assertArrayHasKey('id', $response['user']);
        $this->assertArrayHasKey('username', $response['user']);
        $this->assertArrayHasKey('name', $response['user']);
        $this->assertArrayHasKey('roles', $response['user']);

        $this->assertEquals($validUsername, $response['user']['username']);
        $this->assertIsArray($response['user']['roles']);
        $this->assertNotEmpty($response['user']['roles'], 'User should have at least one role');
    }
}
