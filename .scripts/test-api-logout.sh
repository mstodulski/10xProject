#!/bin/bash

# Skrypt do testowania endpointu POST /api/logout
# Ten endpoint służy do wylogowania użytkownika i unieważnienia session ID

BASE_URL="http://localhost"
COOKIE_FILE="/tmp/api_logout_test_cookies.txt"

echo "=================================================="
echo "Test API Endpoint: POST /api/logout"
echo "=================================================="
echo ""

echo "=== Test 1: Pełny flow - logowanie, dostęp, wylogowanie, brak dostępu ==="
echo ""
echo "1a. Logowanie użytkownika..."
response=$(curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1","password":"password123"}' \
  -c "${COOKIE_FILE}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s)
echo "$response"

# Wyciągnij session ID
session_id=$(echo "$response" | grep -o '"sessionId":"[^"]*"' | cut -d'"' -f4)
if [ -n "$session_id" ]; then
    echo "✓ Session ID otrzymane: $session_id"
else
    echo "✗ Nie otrzymano session ID - przerywam test"
    exit 1
fi
echo ""

echo "1b. Weryfikacja dostępu do /api/inspections z session ID..."
curl -X GET "${BASE_URL}/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s | head -20
echo "..."
echo ""

echo "1c. Wylogowanie użytkownika..."
curl -X POST "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "1d. Próba dostępu do /api/inspections po wylogowaniu (powinno zwrócić 401)..."
curl -X GET "${BASE_URL}/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 2: Próba wylogowania bez session ID (powinno zwrócić 401) ==="
curl -X POST "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 3: Próba wylogowania z nieprawidłowym session ID (powinno zwrócić 401) ==="
curl -X POST "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -b "PHPSESSID=invalid-session-id-12345" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 4: Próba użycia GET zamiast POST (powinno zwrócić 405) ==="
# Najpierw zaloguj się ponownie
response=$(curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1","password":"password123"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s)
session_id=$(echo "$response" | grep -o '"sessionId":"[^"]*"' | cut -d'"' -f4)

curl -X GET "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 5: Podwójne wylogowanie (powinno zwrócić 401 przy drugim) ==="
echo "5a. Pierwsze wylogowanie..."
curl -X POST "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "5b. Drugie wylogowanie z tym samym session ID (powinno zwrócić 401)..."
curl -X POST "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 6: Logowanie -> Wylogowanie -> Ponowne logowanie ==="
echo "6a. Logowanie..."
response=$(curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1","password":"password123"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s)
session_id_1=$(echo "$response" | grep -o '"sessionId":"[^"]*"' | cut -d'"' -f4)
echo "Session ID 1: $session_id_1"
echo ""

echo "6b. Wylogowanie..."
curl -X POST "${BASE_URL}/api/logout" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id_1}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "6c. Ponowne logowanie..."
response=$(curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1","password":"password123"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s)
session_id_2=$(echo "$response" | grep -o '"sessionId":"[^"]*"' | cut -d'"' -f4)
echo "Session ID 2: $session_id_2"
echo ""

echo "6d. Weryfikacja że nowy session działa..."
curl -X GET "${BASE_URL}/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id_2}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s | head -10
echo "..."
echo ""

echo "6e. Weryfikacja że stary session NIE działa..."
curl -X GET "${BASE_URL}/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=${session_id_1}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=================================================="
echo "Testy zakończone!"
echo "=================================================="
echo ""
echo "Plik cookie sesji: ${COOKIE_FILE}"
echo ""

# Cleanup - wyloguj ostatnią sesję
if [ -n "$session_id_2" ]; then
    echo "Czyszczenie - wylogowanie ostatniej sesji testowej..."
    curl -X POST "${BASE_URL}/api/logout" \
      -H "Accept: application/json" \
      -b "PHPSESSID=${session_id_2}" \
      -s > /dev/null
    echo "✓ Wyczyszczono"
fi
