#!/bin/bash

# Skrypt do testowania endpointu POST /api/authorize
# Ten endpoint służy do autoryzacji użytkownika i otrzymania session ID (PHPSESSID)

BASE_URL="http://localhost"
COOKIE_FILE="/tmp/api_session_cookies.txt"

echo "=================================================="
echo "Test API Endpoint: POST /api/authorize"
echo "=================================================="
echo ""

echo "=== Test 1: Poprawne logowanie (istniejący użytkownik) ==="
echo "Wysyłanie żądania z poprawnymi danymi..."
response=$(curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1","password":"password123"}' \
  -c "${COOKIE_FILE}" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s)
echo "$response"
echo ""

# Wyciągnij session ID z odpowiedzi JSON
session_id=$(echo "$response" | grep -o '"sessionId":"[^"]*"' | cut -d'"' -f4)
if [ -n "$session_id" ]; then
    echo "✓ Session ID otrzymane: $session_id"
    export TEST_SESSION_ID="$session_id"
else
    echo "✗ Nie otrzymano session ID"
fi
echo ""

echo "=== Test 2: Weryfikacja, czy session działa z /api/inspections ==="
if [ -n "$session_id" ]; then
    echo "Wysyłanie żądania do /api/inspections z otrzymanym session ID..."
    curl -X GET "${BASE_URL}/api/inspections" \
      -H "Accept: application/json" \
      -b "PHPSESSID=${session_id}" \
      -w "\nHTTP Status: %{http_code}\n" \
      -s
    echo ""
else
    echo "⊘ Pominięto - brak session ID z testu 1"
    echo ""
fi

echo "=== Test 3: Nieprawidłowe hasło ==="
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1","password":"wrongpassword"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 4: Nieistniejący użytkownik ==="
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"nonexistentuser","password":"password123"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 5: Brak username w żądaniu ==="
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"password":"password123"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 6: Brak password w żądaniu ==="
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"consultant1"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 7: Puste pola ==="
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"","password":""}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 8: Nieprawidłowy format JSON ==="
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d 'not-a-json' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 9: Próba użycia GET zamiast POST (powinno zwrócić 405) ==="
curl -X GET "${BASE_URL}/api/authorize" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=== Test 10: Logowanie jako nieaktywny użytkownik (jeśli taki istnieje) ==="
echo "Uwaga: Ten test zadziała tylko jeśli w bazie jest nieaktywny użytkownik"
curl -X POST "${BASE_URL}/api/authorize" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"inactive_user","password":"password123"}' \
  -w "\nHTTP Status: %{http_code}\n" \
  -s
echo ""

echo "=================================================="
echo "Testy zakończone!"
echo "=================================================="
echo ""
echo "Plik cookie sesji zapisany w: ${COOKIE_FILE}"
echo "Session ID do wykorzystania w innych testach: ${TEST_SESSION_ID}"
echo ""
