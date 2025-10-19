#!/bin/bash

echo "=========================================="
echo "Test 1: Brak sesji (brak cookie)"
echo "=========================================="
echo "Oczekiwany wynik: 401 Unauthorized z JSON error"
echo ""
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -i -s | head -20

echo ""
echo ""
echo "=========================================="
echo "Test 2: Nieprawidłowa sesja (błędny PHPSESSID)"
echo "=========================================="
echo "Oczekiwany wynik: 401 Unauthorized z JSON error"
echo ""
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=invalid-session-id-12345" \
  -i -s | head -20

echo ""
echo ""
echo "=========================================="
echo "Test 3: Pusty PHPSESSID"
echo "=========================================="
echo "Oczekiwany wynik: 401 Unauthorized z JSON error"
echo ""
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=" \
  -i -s | head -20

echo ""
echo ""
echo "=========================================="
echo "Podsumowanie:"
echo "=========================================="
echo "Wszystkie testy powinny zwrócić:"
echo "- Status HTTP: 401"
echo "- Content-Type: application/json"
echo "- Body: {\"success\":false,\"error\":\"...\"}"
echo ""
echo "WAŻNE: Jeśli widzisz przekierowanie (302) lub HTML,"
echo "to oznacza, że konfiguracja nie działa poprawnie."
echo ""
