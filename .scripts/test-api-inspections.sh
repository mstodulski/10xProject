#!/bin/bash

# Skrypt do testowania endpointu GET /api/inspections
# Uwaga: Wymaga uwierzytelnienia (sesja użytkownika)

echo "=== Test 1: Podstawowe żądanie bez filtrów ==="
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 2: Z filtrem dat ==="
curl -X GET "http://localhost/api/inspections?startDate=2025-09-30&endDate=2025-10-31" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 3: Z paginacją ==="
curl -X GET "http://localhost/api/inspections?page=1&limit=10" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 4: Z filtrem użytkownika ==="
curl -X GET "http://localhost/api/inspections?createdByUserId=11" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 5: Wszystkie filtry razem ==="
curl -X GET "http://localhost/api/inspections?startDate=2025-09-30&endDate=2025-10-31&createdByUserId=11&page=1&limit=20" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 6: Nieprawidłowy format daty (powinno zwrócić 400) ==="
curl -X GET "http://localhost/api/inspections?startDate=invalid-date" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 7: startDate > endDate (powinno zwrócić 400) ==="
curl -X GET "http://localhost/api/inspections?startDate=2025-10-31&endDate=2025-09-30" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 8: Nieprawidłowy limit (powinno zwrócić 400) ==="
curl -X GET "http://localhost/api/inspections?limit=200" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 9: Nieistniejący użytkownik (powinno zwrócić 400) ==="
curl -X GET "http://localhost/api/inspections?createdByUserId=99999" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id" \
  -w "\nHTTP Status: %{http_code}\n\n"

echo "=== Test 10: Bez uwierzytelnienia (powinno zwrócić 401) ==="
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\n\n"
