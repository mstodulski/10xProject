# 📋 Dokumentacja API - Informacje

## ✅ Utworzone pliki

Dokumentacja API została utworzona w katalogu `/public/api-docs/`:

```
public/api-docs/
├── openapi.json    (22KB) - Specyfikacja OpenAPI 3.0.3
├── index.html      (3.9KB) - Interfejs Swagger UI
└── README.md       (5.0KB) - Instrukcje korzystania z API
```

## 🌐 Dostęp do dokumentacji

### Swagger UI (interfejs graficzny)
Otwórz w przeglądarce:
```
http://localhost/api-docs/
```

### Surowa specyfikacja OpenAPI
```
http://localhost/api-docs/openapi.json
```

## 📚 Dokumentowane endpointy

### 1. POST /api/authorize
**Opis:** Logowanie użytkownika i otrzymanie session ID

**Request Body:**
```json
{
  "username": "consultant1",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Zalogowano pomyślnie",
  "sessionId": "abc123...",
  "user": {
    "id": 1,
    "username": "consultant1",
    "name": "Jan Kowalski",
    "roles": ["ROLE_USER", "ROLE_CONSULTANT"]
  }
}
```

### 2. POST /api/logout
**Opis:** Wylogowanie użytkownika i unieważnienie sesji

**Wymaga:** Session ID (PHPSESSID cookie)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Wylogowano pomyślnie"
}
```

### 3. GET /api/inspections
**Opis:** Pobieranie listy oględzin z filtrami i paginacją

**Wymaga:** Session ID (PHPSESSID cookie)

**Query Parameters:**
- `startDate` (string, YYYY-MM-DD) - data początkowa
- `endDate` (string, YYYY-MM-DD) - data końcowa
- `createdByUserId` (integer) - ID konsultanta
- `page` (integer, default: 1) - numer strony
- `limit` (integer, 1-100, default: 50) - liczba wyników na stronie

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "startDatetime": "2025-10-15T10:00:00+00:00",
      "endDatetime": "2025-10-15T10:30:00+00:00",
      "vehicleMake": "Toyota",
      "vehicleModel": "Corolla",
      "licensePlate": "WA12345",
      "clientName": "Jan Kowalski",
      "phoneNumber": "123456789",
      "createdByUserId": 11,
      "createdByUserName": "Konsultant Jan",
      "createdAt": "2025-10-10T08:30:00+00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 45,
    "totalPages": 3
  }
}
```

## 🔐 Jak testować API w Swagger UI

1. Otwórz http://localhost/api-docs/
2. Znajdź endpoint **POST /api/authorize**
3. Kliknij **"Try it out"**
4. Wprowadź dane logowania i kliknij **"Execute"**
5. Skopiuj `sessionId` z odpowiedzi
6. Kliknij przycisk **"Authorize"** (ikona kłódki) na górze strony
7. Wklej `sessionId` do pola **PHPSESSID** i kliknij **"Authorize"**
8. Teraz możesz testować pozostałe endpointy!

## 📦 Import do innych narzędzi

### Postman
1. Otwórz Postman
2. Import → Link → wklej: `http://localhost/api-docs/openapi.json`
3. Kolekcja zostanie automatycznie utworzona

### Insomnia
1. Otwórz Insomnia
2. Import/Export → Import Data → From URL
3. Wklej: `http://localhost/api-docs/openapi.json`

### VS Code REST Client
Możesz także pobrać plik `openapi.json` lokalnie i użyć go w swoich narzędziach.

## ✨ Funkcje dokumentacji

- ✅ Pełna specyfikacja OpenAPI 3.0.3
- ✅ Szczegółowe opisy wszystkich endpointów
- ✅ Przykłady requestów i responses
- ✅ Dokumentacja wszystkich parametrów i schematów
- ✅ Obsługa autoryzacji przez session ID (cookie)
- ✅ Kody błędów i komunikaty walidacji
- ✅ Interaktywne testowanie przez Swagger UI
- ✅ Możliwość eksportu do Postman/Insomnia
- ✅ Filtrowanie i wyszukiwanie endpointów
- ✅ Podświetlanie składni JSON

## 📖 Dodatkowe zasoby

- **README API:** `/public/api-docs/README.md` - szczegółowy opis API
- **Skrypty testowe:**
  - `.ai/test-api-authorize.sh` - test autoryzacji
  - `.ai/test-api-logout.sh` - test wylogowania
  - `.ai/test-api-inspections.sh` - test pobierania oględzin

---

**Data utworzenia:** 2025-10-18
**Wersja API:** 1.0.0
