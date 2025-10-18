# Dokumentacja API - System Zarządzania Oględzinami Pojazdów Powypadkowych

## 📖 Przeglądanie dokumentacji

### Opcja 1: Swagger UI (interfejs graficzny)
Otwórz w przeglądarce:
```
http://localhost/api-docs/
```

Interfejs Swagger UI pozwala na:
- Przeglądanie wszystkich endpointów
- Testowanie endpointów bezpośrednio z przeglądarki
- Przeglądanie schematów request/response
- Eksport specyfikacji OpenAPI

### Opcja 2: Plik JSON (surowa specyfikacja)
Pobierz lub przeglądaj surowy plik OpenAPI:
```
http://localhost/api-docs/openapi.json
```

Ten plik można zaimportować do narzędzi takich jak:
- Postman
- Insomnia
- VS Code REST Client
- Inne narzędzia obsługujące OpenAPI 3.0

## 🔐 Autoryzacja

API wymaga autoryzacji dla większości endpointów. Proces autoryzacji:

### 1. Zaloguj się
```bash
curl -X POST http://localhost/api/authorize \
  -H "Content-Type: application/json" \
  -d '{"username":"consultant1","password":"password123"}'
```

Odpowiedź:
```json
{
  "success": true,
  "message": "Zalogowano pomyślnie",
  "sessionId": "a1b2c3d4e5f6g7h8i9j0",
  "user": {
    "id": 1,
    "username": "consultant1",
    "name": "Jan Kowalski",
    "roles": ["ROLE_USER", "ROLE_CONSULTANT"]
  }
}
```

### 2. Używaj sessionId w kolejnych zapytaniach

#### Curl
```bash
curl -X GET http://localhost/api/inspections \
  -H "Accept: application/json" \
  -b "PHPSESSID=a1b2c3d4e5f6g7h8i9j0"
```

#### Swagger UI
1. Kliknij przycisk **"Authorize"** (ikonka kłódki) na górze strony
2. Wklej `sessionId` do pola **PHPSESSID**
3. Kliknij **"Authorize"**
4. Teraz możesz testować endpointy bezpośrednio w Swagger UI

#### JavaScript/Fetch
```javascript
fetch('http://localhost/api/inspections', {
  method: 'GET',
  headers: {
    'Accept': 'application/json'
  },
  credentials: 'include',  // Ważne! Przesyła cookies
  // lub manualnie:
  // headers: {
  //   'Cookie': 'PHPSESSID=a1b2c3d4e5f6g7h8i9j0'
  // }
})
```

### 3. Wylogowanie (opcjonalnie)
```bash
curl -X POST http://localhost/api/logout \
  -b "PHPSESSID=a1b2c3d4e5f6g7h8i9j0"
```

## 📚 Dostępne endpointy

### Authentication
- `POST /api/authorize` - Logowanie użytkownika
- `POST /api/logout` - Wylogowanie użytkownika

### Inspections
- `GET /api/inspections` - Pobieranie listy oględzin z filtrami i paginacją

## 🧪 Przykłady użycia

### Pobieranie oględzin z filtrem dat
```bash
curl -X GET "http://localhost/api/inspections?startDate=2025-10-01&endDate=2025-10-31" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id"
```

### Pobieranie oględzin z paginacją
```bash
curl -X GET "http://localhost/api/inspections?page=1&limit=20" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id"
```

### Filtrowanie po użytkowniku
```bash
curl -X GET "http://localhost/api/inspections?createdByUserId=11" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id"
```

### Wszystkie filtry razem
```bash
curl -X GET "http://localhost/api/inspections?startDate=2025-10-01&endDate=2025-10-31&createdByUserId=11&page=1&limit=20" \
  -H "Accept: application/json" \
  -b "PHPSESSID=your-session-id"
```

## 📝 Parametry zapytania dla /api/inspections

| Parametr | Typ | Wymagany | Domyślnie | Opis |
|----------|-----|----------|-----------|------|
| `startDate` | string (YYYY-MM-DD) | Nie | - | Data początkowa zakresu |
| `endDate` | string (YYYY-MM-DD) | Nie | - | Data końcowa zakresu |
| `createdByUserId` | integer | Nie | - | ID użytkownika (konsultanta) |
| `page` | integer (min: 1) | Nie | 1 | Numer strony |
| `limit` | integer (1-100) | Nie | 50 | Liczba wyników na stronie |

## ⚠️ Kody błędów

| Kod | Znaczenie |
|-----|-----------|
| 200 | OK - Zapytanie wykonane pomyślnie |
| 400 | Bad Request - Błędne parametry zapytania |
| 401 | Unauthorized - Brak autoryzacji lub sesja wygasła |
| 405 | Method Not Allowed - Nieprawidłowa metoda HTTP |
| 500 | Internal Server Error - Błąd serwera |

## 🛠️ Narzędzia do testowania

### Skrypty testowe
W projekcie dostępne są gotowe skrypty do testowania:

```bash
# Test autoryzacji
.ai/test-api-authorize.sh

# Test wylogowania
.ai/test-api-logout.sh

# Test pobierania oględzin
.ai/test-api-inspections.sh
```

### Import do Postman
1. Otwórz Postman
2. Kliknij **Import**
3. Wybierz plik `openapi.json`
4. Kolekcja zostanie automatycznie utworzona ze wszystkimi endpointami

### Import do Insomnia
1. Otwórz Insomnia
2. Kliknij **Import/Export** → **Import Data** → **From File**
3. Wybierz plik `openapi.json`
4. Wszystkie endpointy zostaną zaimportowane

## 📚 Więcej informacji

- Specyfikacja OpenAPI 3.0: https://swagger.io/specification/
- Dokumentacja Swagger UI: https://swagger.io/tools/swagger-ui/

## 🔄 Aktualizacja dokumentacji

Gdy API się zmienia, należy zaktualizować plik `openapi.json`. Można to zrobić:
1. Ręcznie edytując plik
2. Używając narzędzi do generowania OpenAPI (np. NelmioApiDocBundle dla Symfony)
3. Używając edytora Swagger: https://editor.swagger.io/

---

**Wersja:** 1.0.0
**Data ostatniej aktualizacji:** 2025-10-18
