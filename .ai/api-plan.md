# Plan API REST - System Zarządzania Oględzinami Pojazdów Powypadkowych

## 1. Zasoby

### 1.1. Users (Użytkownicy)
- **Encja:** `App\Entity\User`
- **Tabela:** `users`
- **Opis:** Użytkownicy systemu (konsultanci i inspektorzy)

### 1.2. Inspections (Oględziny)
- **Encja:** `App\Entity\Inspection`
- **Tabela:** `inspections`
- **Opis:** Terminy oględzin pojazdów powypadkowych

## 2. Endpointy API

### 2.1. Uwierzytelnianie

#### 2.1.1. POST /api/login
**Opis:** Logowanie użytkownika do systemu

**Dostęp:** Publiczny (niezalogowani użytkownicy)

**Parametry zapytania:** Brak

**Request Body:**
```json
{
  "username": "string",
  "password": "string"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "jan.kowalski",
    "name": "Jan Kowalski",
    "roles": ["ROLE_CONSULTANT"],
    "isActive": true
  }
}
```

**Kody błędów:**
- `400 Bad Request` - Brakujące pola username lub password
```json
{
  "success": false,
  "error": "Wymagane pola: username, password"
}
```
- `401 Unauthorized` - Niepoprawne dane logowania
```json
{
  "success": false,
  "error": "Nieprawidłowy login lub hasło"
}
```
- `403 Forbidden` - Użytkownik nieaktywny
```json
{
  "success": false,
  "error": "Konto użytkownika jest nieaktywne"
}
```

---

#### 2.1.2. POST /api/logout
**Opis:** Wylogowanie użytkownika z systemu

**Dostęp:** Zalogowani użytkownicy (ROLE_USER)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Pomyślnie wylogowano"
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```

---

#### 2.1.3. GET /api/me
**Opis:** Pobranie informacji o aktualnie zalogowanym użytkowniku

**Dostęp:** Zalogowani użytkownicy (ROLE_USER)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "id": 1,
  "username": "jan.kowalski",
  "name": "Jan Kowalski",
  "roles": ["ROLE_CONSULTANT"],
  "isActive": true,
  "createdAt": "2025-01-15T10:30:00+01:00"
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```

---

### 2.2. Zarządzanie Oględzinami

#### 2.2.1. GET /api/inspections
**Opis:** Pobranie listy oględzin z możliwością filtrowania

**Dostęp:** Zalogowani użytkownicy (ROLE_USER)

**Parametry zapytania:**
- `startDate` (opcjonalny) - Data rozpoczęcia zakresu w formacie `YYYY-MM-DD`
- `endDate` (opcjonalny) - Data zakończenia zakresu w formacie `YYYY-MM-DD`
- `page` (opcjonalny, domyślnie: 1) - Numer strony dla paginacji
- `limit` (opcjonalny, domyślnie: 50, max: 100) - Liczba wyników na stronę
- `createdByUserId` (opcjonalny) - ID użytkownika, który utworzył oględziny

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "startDatetime": "2025-10-15T10:00:00+02:00",
      "endDatetime": "2025-10-15T10:30:00+02:00",
      "vehicleMake": "Toyota",
      "vehicleModel": "Corolla",
      "licensePlate": "WA12345",
      "clientName": "Anna Nowak",
      "phoneNumber": "+48123456789",
      "createdByUser": {
        "id": 1,
        "name": "Jan Kowalski"
      },
      "createdAt": "2025-10-01T14:20:00+02:00",
      "isPast": false
    }
  ],
  "meta": {
    "currentPage": 1,
    "perPage": 50,
    "total": 15,
    "totalPages": 1
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```
- `400 Bad Request` - Nieprawidłowe parametry filtrowania
```json
{
  "success": false,
  "error": "Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD"
}
```

---

#### 2.2.2. GET /api/inspections/{id}
**Opis:** Pobranie szczegółów pojedynczych oględzin

**Dostęp:** Zalogowani użytkownicy (ROLE_USER)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "id": 1,
  "startDatetime": "2025-10-15T10:00:00+02:00",
  "endDatetime": "2025-10-15T10:30:00+02:00",
  "vehicleMake": "Toyota",
  "vehicleModel": "Corolla",
  "licensePlate": "WA12345",
  "clientName": "Anna Nowak",
  "phoneNumber": "+48123456789",
  "createdByUser": {
    "id": 1,
    "name": "Jan Kowalski",
    "username": "jan.kowalski"
  },
  "createdAt": "2025-10-01T14:20:00+02:00",
  "isPast": false,
  "isFuture": true,
  "isToday": false,
  "durationInMinutes": 30
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```
- `404 Not Found` - Oględziny nie istnieją
```json
{
  "success": false,
  "error": "Nie znaleziono oględzin o podanym ID"
}
```

---

#### 2.2.3. POST /api/inspections
**Opis:** Utworzenie nowych oględzin

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:**
```json
{
  "startDatetime": "2025-10-15T10:00:00+02:00",
  "vehicleMake": "Toyota",
  "vehicleModel": "Corolla",
  "licensePlate": "WA12345",
  "clientName": "Anna Nowak",
  "phoneNumber": "+48123456789"
}
```

**Uwagi:**
- Pole `endDatetime` jest automatycznie ustawiane na `startDatetime + 30 minut`
- Pole `createdByUser` jest automatycznie ustawiane na zalogowanego użytkownika

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Oględziny zostały pomyślnie utworzone",
  "data": {
    "id": 1,
    "startDatetime": "2025-10-15T10:00:00+02:00",
    "endDatetime": "2025-10-15T10:30:00+02:00",
    "vehicleMake": "Toyota",
    "vehicleModel": "Corolla",
    "licensePlate": "WA12345",
    "clientName": "Anna Nowak",
    "phoneNumber": "+48123456789",
    "createdByUser": {
      "id": 1,
      "name": "Jan Kowalski"
    },
    "createdAt": "2025-10-01T14:20:00+02:00"
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```
- `403 Forbidden` - Brak uprawnień (użytkownik nie jest konsultantem)
```json
{
  "success": false,
  "error": "Brak uprawnień. Tylko konsultanci mogą tworzyć oględziny"
}
```
- `400 Bad Request` - Błędy walidacji pól
```json
{
  "success": false,
  "errors": {
    "vehicleMake": "Pole marki pojazdu jest wymagane",
    "phoneNumber": "Numer telefonu musi mieć minimum 8 znaków"
  }
}
```
- `422 Unprocessable Entity` - Błędy walidacji biznesowej
```json
{
  "success": false,
  "error": "Termin musi być w przyszłości",
  "code": "PAST_DATETIME"
}
```
```json
{
  "success": false,
  "error": "Termin musi być w godzinach pracy (07:00-16:00)",
  "code": "OUTSIDE_WORKING_HOURS"
}
```
```json
{
  "success": false,
  "error": "Nie można umawiać oględzin w weekendy",
  "code": "WEEKEND_NOT_ALLOWED"
}
```
```json
{
  "success": false,
  "error": "Termin musi zaczynać się o pełnej godzinie lub 15, 30, 45 minut po",
  "code": "INVALID_TIME_SLOT"
}
```
```json
{
  "success": false,
  "error": "Można rezerwować terminy maksymalnie 2 tygodnie do przodu",
  "code": "TOO_FAR_IN_FUTURE"
}
```
- `409 Conflict` - Kolizja z istniejącymi terminami
```json
{
  "success": false,
  "error": "Ten termin koliduje z istniejącymi oględzinami",
  "code": "SCHEDULE_CONFLICT",
  "conflictingInspections": [
    {
      "id": 5,
      "startDatetime": "2025-10-15T09:30:00+02:00",
      "endDatetime": "2025-10-15T10:00:00+02:00"
    }
  ]
}
```

---

#### 2.2.4. PUT /api/inspections/{id}
**Opis:** Aktualizacja istniejących oględzin

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:**
```json
{
  "startDatetime": "2025-10-15T11:00:00+02:00",
  "vehicleMake": "Toyota",
  "vehicleModel": "Corolla",
  "licensePlate": "WA12345",
  "clientName": "Anna Nowak",
  "phoneNumber": "+48123456789"
}
```

**Uwagi:**
- Pole `endDatetime` jest automatycznie aktualizowane na `startDatetime + 30 minut`
- Nie można edytować oględzin z przeszłości

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Oględziny zostały pomyślnie zaktualizowane",
  "data": {
    "id": 1,
    "startDatetime": "2025-10-15T11:00:00+02:00",
    "endDatetime": "2025-10-15T11:30:00+02:00",
    "vehicleMake": "Toyota",
    "vehicleModel": "Corolla",
    "licensePlate": "WA12345",
    "clientName": "Anna Nowak",
    "phoneNumber": "+48123456789",
    "createdByUser": {
      "id": 1,
      "name": "Jan Kowalski"
    },
    "createdAt": "2025-10-01T14:20:00+02:00"
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```
- `403 Forbidden` - Brak uprawnień lub próba edycji przeszłych oględzin
```json
{
  "success": false,
  "error": "Nie można edytować oględzin z przeszłości"
}
```
- `404 Not Found` - Oględziny nie istnieją
```json
{
  "success": false,
  "error": "Nie znaleziono oględzin o podanym ID"
}
```
- `400 Bad Request` - Błędy walidacji pól (jak przy POST)
- `422 Unprocessable Entity` - Błędy walidacji biznesowej (jak przy POST)
- `409 Conflict` - Kolizja z istniejącymi terminami (jak przy POST)

---

#### 2.2.5. DELETE /api/inspections/{id}
**Opis:** Usunięcie oględzin

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Uwagi:**
- Nie można usuwać oględzin z przeszłości
- Konsultanci mogą usuwać wszystkie oględziny, nie tylko swoje

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Oględziny zostały pomyślnie usunięte"
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
```json
{
  "success": false,
  "error": "Wymagane uwierzytelnienie"
}
```
- `403 Forbidden` - Brak uprawnień lub próba usunięcia przeszłych oględzin
```json
{
  "success": false,
  "error": "Nie można usuwać oględzin z przeszłości"
}
```
- `404 Not Found` - Oględziny nie istnieją
```json
{
  "success": false,
  "error": "Nie znaleziono oględzin o podanym ID"
}
```

---

#### 2.2.6. GET /api/inspections/availability
**Opis:** Sprawdzenie dostępności dla danego terminu

**Dostęp:** Zalogowani użytkownicy (ROLE_USER)

**Parametry zapytania:**
- `startDatetime` (wymagany) - Data i godzina rozpoczęcia w formacie ISO 8601
- `excludeInspectionId` (opcjonalny) - ID oględzin do wykluczenia z sprawdzania (używane przy edycji)

**Request Body:** Brak

**Response (200 OK) - Termin dostępny:**
```json
{
  "available": true,
  "startDatetime": "2025-10-15T10:00:00+02:00",
  "endDatetime": "2025-10-15T10:30:00+02:00"
}
```

**Response (200 OK) - Termin niedostępny:**
```json
{
  "available": false,
  "startDatetime": "2025-10-15T10:00:00+02:00",
  "endDatetime": "2025-10-15T10:30:00+02:00",
  "reason": "Termin koliduje z istniejącymi oględzinami",
  "conflictingInspections": [
    {
      "id": 5,
      "startDatetime": "2025-10-15T09:30:00+02:00",
      "endDatetime": "2025-10-15T10:00:00+02:00",
      "vehicleMake": "Honda",
      "vehicleModel": "Civic",
      "licensePlate": "WA67890"
    }
  ]
}
```

**Response (200 OK) - Termin naruszający reguły biznesowe:**
```json
{
  "available": false,
  "startDatetime": "2025-10-15T06:00:00+02:00",
  "endDatetime": "2025-10-15T06:30:00+02:00",
  "reason": "Termin musi być w godzinach pracy (07:00-16:00)",
  "validationErrors": [
    {
      "code": "OUTSIDE_WORKING_HOURS",
      "message": "Termin musi być w godzinach pracy (07:00-16:00)"
    }
  ]
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
- `400 Bad Request` - Brak wymaganego parametru
```json
{
  "success": false,
  "error": "Parametr startDatetime jest wymagany"
}
```

---

### 2.3. Zarządzanie Użytkownikami

#### 2.3.1. GET /api/users
**Opis:** Pobranie listy wszystkich użytkowników

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:**
- `includeInactive` (opcjonalny, domyślnie: true) - Czy uwzględnić nieaktywnych użytkowników
- `role` (opcjonalny) - Filtrowanie po roli: `consultant` lub `inspector`
- `page` (opcjonalny, domyślnie: 1) - Numer strony
- `limit` (opcjonalny, domyślnie: 50, max: 100) - Liczba wyników na stronę

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "username": "jan.kowalski",
      "name": "Jan Kowalski",
      "roles": ["ROLE_CONSULTANT"],
      "isActive": true,
      "createdAt": "2025-01-10T10:00:00+01:00"
    },
    {
      "id": 2,
      "username": "anna.nowak",
      "name": "Anna Nowak",
      "roles": ["ROLE_INSPECTOR"],
      "isActive": false,
      "createdAt": "2025-01-12T14:30:00+01:00"
    }
  ],
  "meta": {
    "currentPage": 1,
    "perPage": 50,
    "total": 2,
    "totalPages": 1
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
- `403 Forbidden` - Brak uprawnień (użytkownik nie jest konsultantem)
```json
{
  "success": false,
  "error": "Brak uprawnień. Tylko konsultanci mogą zarządzać użytkownikami"
}
```

---

#### 2.3.2. GET /api/users/{id}
**Opis:** Pobranie szczegółów pojedynczego użytkownika

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "id": 1,
  "username": "jan.kowalski",
  "name": "Jan Kowalski",
  "roles": ["ROLE_CONSULTANT"],
  "isActive": true,
  "createdAt": "2025-01-10T10:00:00+01:00",
  "updatedAt": null
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
- `403 Forbidden` - Brak uprawnień
- `404 Not Found` - Użytkownik nie istnieje
```json
{
  "success": false,
  "error": "Nie znaleziono użytkownika o podanym ID"
}
```

---

#### 2.3.3. POST /api/users
**Opis:** Utworzenie nowego użytkownika

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:**
```json
{
  "username": "piotr.wisniewski",
  "password": "bezpieczneHaslo123",
  "name": "Piotr Wiśniewski",
  "roles": ["ROLE_CONSULTANT"]
}
```

**Uwagi:**
- Pole `roles` może zawierać: `["ROLE_CONSULTANT"]` lub `["ROLE_INSPECTOR"]`
- Hasło będzie automatycznie zahashowane przez system
- Nowy użytkownik jest domyślnie aktywny (`isActive: true`)

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Użytkownik został pomyślnie utworzony",
  "data": {
    "id": 3,
    "username": "piotr.wisniewski",
    "name": "Piotr Wiśniewski",
    "roles": ["ROLE_CONSULTANT"],
    "isActive": true,
    "createdAt": "2025-10-18T15:30:00+02:00"
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
- `403 Forbidden` - Brak uprawnień
- `400 Bad Request` - Błędy walidacji
```json
{
  "success": false,
  "errors": {
    "username": "Login jest wymagany",
    "password": "Hasło jest wymagane",
    "name": "Imię i nazwisko jest wymagane",
    "roles": "Rola jest wymagana"
  }
}
```
- `409 Conflict` - Login już istnieje
```json
{
  "success": false,
  "error": "Użytkownik o podanym loginie już istnieje"
}
```

---

#### 2.3.4. PATCH /api/users/{id}/activate
**Opis:** Aktywacja użytkownika

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Użytkownik został pomyślnie aktywowany",
  "data": {
    "id": 2,
    "username": "anna.nowak",
    "name": "Anna Nowak",
    "roles": ["ROLE_INSPECTOR"],
    "isActive": true,
    "updatedAt": "2025-10-18T15:45:00+02:00"
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
- `403 Forbidden` - Brak uprawnień
- `404 Not Found` - Użytkownik nie istnieje
- `422 Unprocessable Entity` - Użytkownik już jest aktywny
```json
{
  "success": false,
  "error": "Użytkownik jest już aktywny"
}
```

---

#### 2.3.5. PATCH /api/users/{id}/deactivate
**Opis:** Dezaktywacja użytkownika

**Dostęp:** Konsultanci (ROLE_CONSULTANT)

**Parametry zapytania:** Brak

**Request Body:** Brak

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Użytkownik został pomyślnie dezaktywowany",
  "data": {
    "id": 2,
    "username": "anna.nowak",
    "name": "Anna Nowak",
    "roles": ["ROLE_INSPECTOR"],
    "isActive": false,
    "updatedAt": "2025-10-18T15:50:00+02:00"
  }
}
```

**Kody błędów:**
- `401 Unauthorized` - Brak uwierzytelnienia
- `403 Forbidden` - Brak uprawnień
- `404 Not Found` - Użytkownik nie istnieje
- `422 Unprocessable Entity` - Użytkownik już jest nieaktywny
```json
{
  "success": false,
  "error": "Użytkownik jest już nieaktywny"
}
```

---

## 3. Uwierzytelnianie i Autoryzacja

### 3.1. Mechanizm uwierzytelniania

System wykorzystuje **session-based authentication** wbudowany w Symfony Security. Po zalogowaniu użytkownika, jego sesja jest przechowywana w cookies po stronie przeglądarki.

**Implementacja:**
- Wykorzystanie `Symfony\Component\Security\Core\Security`
- Konfiguracja w `config/packages/security.yaml`
- Session storage w PHP sessions (domyślnie)
- CSRF protection dla formularzy logowania

### 3.2. Role i uprawnienia

System wykorzystuje hierarchiczny system ról Symfony:

```yaml
role_hierarchy:
    ROLE_CONSULTANT: ROLE_USER
    ROLE_INSPECTOR: ROLE_USER
```

**Role:**
- `ROLE_USER` - Podstawowa rola dla wszystkich zalogowanych użytkowników
- `ROLE_CONSULTANT` - Konsultant (pełne uprawnienia: tworzenie, edycja, usuwanie oględzin oraz zarządzanie użytkownikami)
- `ROLE_INSPECTOR` - Inspektor (tylko odczyt kalendarza)

**Macierz uprawnień:**

| Endpoint | ROLE_INSPECTOR | ROLE_CONSULTANT |
|----------|----------------|-----------------|
| POST /api/login | ✓ (public) | ✓ (public) |
| POST /api/logout | ✓ | ✓ |
| GET /api/me | ✓ | ✓ |
| GET /api/inspections | ✓ | ✓ |
| GET /api/inspections/{id} | ✓ | ✓ |
| POST /api/inspections | ✗ | ✓ |
| PUT /api/inspections/{id} | ✗ | ✓ |
| DELETE /api/inspections/{id} | ✗ | ✓ |
| GET /api/inspections/availability | ✓ | ✓ |
| GET /api/users | ✗ | ✓ |
| GET /api/users/{id} | ✗ | ✓ |
| POST /api/users | ✗ | ✓ |
| PATCH /api/users/{id}/activate | ✗ | ✓ |
| PATCH /api/users/{id}/deactivate | ✗ | ✓ |

### 3.3. Zabezpieczenia

**CSRF Protection:**
- Ochrona CSRF dla endpointów modyfikujących dane (POST, PUT, PATCH, DELETE)
- Token CSRF w headerze: `X-CSRF-Token`

**Rate Limiting (przyszłe rozszerzenie):**
- Limit logowania: 5 prób w ciągu 15 minut na IP
- Limit tworzenia oględzin: 100 żądań/godzinę na użytkownika

**HTTPS:**
- Wymagane szyfrowane połączenie HTTPS w produkcji
- Secure cookies dla sesji

**Password Hashing:**
- Algorytm: bcrypt (Symfony default)
- Minimalna długość hasła: 8 znaków (zalecane)

---

## 4. Walidacja i Logika Biznesowa

### 4.1. Walidacja dla Inspection (Oględziny)

#### 4.1.1. Walidacja pól

| Pole | Walidacja |
|------|-----------|
| `startDatetime` | Wymagane, musi być DateTimeImmutable, przyszła data |
| `vehicleMake` | Wymagane, string, max 64 znaki |
| `vehicleModel` | Wymagane, string, max 64 znaki |
| `licensePlate` | Wymagane, string, max 20 znaków |
| `clientName` | Wymagane, string, max 64 znaki |
| `phoneNumber` | Wymagane, string, min 8 znaków, max 20 znaków |
| `endDatetime` | Automatycznie ustawiane (startDatetime + 30 minut) |
| `createdByUser` | Automatycznie ustawiane (zalogowany użytkownik) |

#### 4.1.2. Walidacja terminu (startDatetime)

**Reguła 1: Termin w przyszłości**
```
startDatetime > now()
```
Kod błędu: `PAST_DATETIME`
Komunikat: "Termin musi być w przyszłości"

**Reguła 2: Godziny pracy (07:00-16:00)**
```
07:00 <= startDatetime.time < 16:00
AND
07:00 <= endDatetime.time <= 16:00
```
Kod błędu: `OUTSIDE_WORKING_HOURS`
Komunikat: "Termin musi być w godzinach pracy (07:00-16:00)"

**Reguła 3: Nie weekendy**
```
startDatetime.dayOfWeek NOT IN (Saturday, Sunday)
```
Kod błędu: `WEEKEND_NOT_ALLOWED`
Komunikat: "Nie można umawiać oględzin w weekendy"

**Reguła 4: Sloty czasowe co 15 minut**
```
startDatetime.minute IN (0, 15, 30, 45)
```
Kod błędu: `INVALID_TIME_SLOT`
Komunikat: "Termin musi zaczynać się o pełnej godzinie lub 15, 30, 45 minut po"

**Reguła 5: Maksymalnie 2 tygodnie do przodu**
```
startDatetime <= now() + 14 days
```
Kod błędu: `TOO_FAR_IN_FUTURE`
Komunikat: "Można rezerwować terminy maksymalnie 2 tygodnie do przodu"

#### 4.1.3. Sprawdzanie kolizji terminów

Nowy termin koliduje z istniejącym, jeśli:

```sql
-- Sprawdzenie z uwzględnieniem 15-minutowych przerw
(
    -- Nowy termin zaczyna się podczas lub tuż przed/po istniejącym
    (new_start BETWEEN existing_start - 15min AND existing_end + 15min)
    OR
    -- Nowy termin kończy się podczas lub tuż przed/po istniejącym
    (new_end BETWEEN existing_start - 15min AND existing_end + 15min)
    OR
    -- Istniejący termin zawiera się w nowym
    (existing_start BETWEEN new_start AND new_end)
    OR
    (existing_end BETWEEN new_start AND new_end)
)
```

Kod błędu: `SCHEDULE_CONFLICT`
Komunikat: "Ten termin koliduje z istniejącymi oględzinami"

**Uwaga przy edycji:**
Przy edycji oględzin, sprawdzenie kolizji musi wykluczyć edytowane oględziny:
```sql
AND inspection.id != edited_inspection_id
```

#### 4.1.4. Walidacja przy edycji

**Reguła dodatkowa: Nie można edytować przeszłych oględzin**
```
inspection.isPast() == false
```
Kod błędu: `CANNOT_EDIT_PAST`
Komunikat: "Nie można edytować oględzin z przeszłości"

#### 4.1.5. Walidacja przy usuwaniu

**Reguła dodatkowa: Nie można usuwać przeszłych oględzin**
```
inspection.isPast() == false
```
Kod błędu: `CANNOT_DELETE_PAST`
Komunikat: "Nie można usuwać oględzin z przeszłości"

### 4.2. Walidacja dla User (Użytkownik)

#### 4.2.1. Walidacja pól

| Pole | Walidacja |
|------|-----------|
| `username` | Wymagane, string, max 64 znaki, unikalny |
| `password` | Wymagane przy tworzeniu, string, min 8 znaków (zalecane), max 255 znaków |
| `name` | Wymagane, string, max 64 znaki |
| `roles` | Wymagane, array, dozwolone wartości: `["ROLE_CONSULTANT"]` lub `["ROLE_INSPECTOR"]` |
| `isActive` | Boolean, domyślnie `true` |

#### 4.2.2. Walidacja unikalności username

```
username musi być unikalny w tabeli users
```
Kod błędu: `USERNAME_EXISTS`
Komunikat: "Użytkownik o podanym loginie już istnieje"

#### 4.2.3. Walidacja logowania

**Reguła 1: Użytkownik musi być aktywny**
```
user.isActive == true
```
Kod błędu: `USER_INACTIVE`
Komunikat: "Konto użytkownika jest nieaktywne"

**Reguła 2: Poprawne credentials**
```
password_verify(input_password, user.password) == true
```
Kod błędu: `INVALID_CREDENTIALS`
Komunikat: "Nieprawidłowy login lub hasło"

### 4.3. Implementacja walidacji w Symfony

**Wykorzystywane komponenty:**
1. **Symfony Validator** - walidacja pól formularzy
   - Constraints (Assert\NotBlank, Assert\Length, Assert\DateTime, etc.)
   - Custom validators dla logiki biznesowej

2. **Custom Validators** (do utworzenia):
   - `InspectionDatetimeValidator` - walidacja terminów oględzin (reguły 1-5)
   - `InspectionConflictValidator` - sprawdzanie kolizji terminów
   - `WorkingHoursValidator` - sprawdzanie godzin pracy
   - `TimeSlotValidator` - sprawdzanie slotów czasowych (co 15 min)

3. **Entity Validation** w kontrolerach:
```php
$violations = $validator->validate($inspection);
if (count($violations) > 0) {
    // Return 400 Bad Request z listą błędów
}
```

4. **Business Logic Validation** w serwisach:
```php
// src/Service/InspectionValidationService.php
class InspectionValidationService
{
    public function validateInspectionTime(DateTimeImmutable $startDateTime): array
    public function checkScheduleConflict(Inspection $inspection): ?array
    public function canEdit(Inspection $inspection): bool
    public function canDelete(Inspection $inspection): bool
}
```

### 4.4. Automatyczne operacje

#### 4.4.1. Ustawianie endDatetime
Przy tworzeniu i edycji oględzin:
```php
$inspection->setEndDatetime(
    $inspection->getStartDatetime()->modify('+30 minutes')
);
```

#### 4.4.2. Ustawianie createdByUser
Przy tworzeniu oględzin:
```php
$inspection->setCreatedByUser($this->security->getUser());
```

#### 4.4.3. Hashowanie hasła
Przy tworzeniu użytkownika:
```php
$hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
$user->setPassword($hashedPassword);
```

---

## 5. Struktura odpowiedzi błędów

### 5.1. Format ogólny błędów walidacji

```json
{
  "success": false,
  "errors": {
    "fieldName": "Opis błędu",
    "anotherField": "Inny błąd"
  }
}
```

### 5.2. Format błędów biznesowych

```json
{
  "success": false,
  "error": "Czytelny komunikat błędu",
  "code": "ERROR_CODE"
}
```

### 5.3. Format błędów kolizji

```json
{
  "success": false,
  "error": "Ten termin koliduje z istniejącymi oględzinami",
  "code": "SCHEDULE_CONFLICT",
  "conflictingInspections": [
    {
      "id": 5,
      "startDatetime": "2025-10-15T09:30:00+02:00",
      "endDatetime": "2025-10-15T10:00:00+02:00",
      "vehicleMake": "Honda",
      "vehicleModel": "Civic"
    }
  ]
}
```

---

## 6. Kody błędów

### 6.1. Ogólne kody HTTP

| Kod | Znaczenie |
|-----|-----------|
| 200 | OK - Sukces (GET, PUT, DELETE) |
| 201 | Created - Zasób utworzony (POST) |
| 204 | No Content - Sukces bez zawartości |
| 400 | Bad Request - Błędy walidacji pól |
| 401 | Unauthorized - Brak uwierzytelnienia |
| 403 | Forbidden - Brak autoryzacji |
| 404 | Not Found - Zasób nie istnieje |
| 409 | Conflict - Konflikt (np. kolizja terminów, duplikat) |
| 422 | Unprocessable Entity - Błędy walidacji biznesowej |
| 500 | Internal Server Error - Błąd serwera |

### 6.2. Kody błędów biznesowych dla Inspection

| Kod | Komunikat |
|-----|-----------|
| `PAST_DATETIME` | Termin musi być w przyszłości |
| `OUTSIDE_WORKING_HOURS` | Termin musi być w godzinach pracy (07:00-16:00) |
| `WEEKEND_NOT_ALLOWED` | Nie można umawiać oględzin w weekendy |
| `INVALID_TIME_SLOT` | Termin musi zaczynać się o pełnej godzinie lub 15, 30, 45 minut po |
| `TOO_FAR_IN_FUTURE` | Można rezerwować terminy maksymalnie 2 tygodnie do przodu |
| `SCHEDULE_CONFLICT` | Ten termin koliduje z istniejącymi oględzinami |
| `CANNOT_EDIT_PAST` | Nie można edytować oględzin z przeszłości |
| `CANNOT_DELETE_PAST` | Nie można usuwać oględzin z przeszłości |

### 6.3. Kody błędów dla User

| Kod | Komunikat |
|-----|-----------|
| `USERNAME_EXISTS` | Użytkownik o podanym loginie już istnieje |
| `USER_INACTIVE` | Konto użytkownika jest nieaktywne |
| `INVALID_CREDENTIALS` | Nieprawidłowy login lub hasło |
| `USER_ALREADY_ACTIVE` | Użytkownik jest już aktywny |
| `USER_ALREADY_INACTIVE` | Użytkownik jest już nieaktywny |

---

## 7. Paginacja

### 7.1. Parametry paginacji

Dla endpointów zwracających listy (`GET /api/inspections`, `GET /api/users`):

| Parametr | Domyślna wartość | Max | Opis |
|----------|------------------|-----|------|
| `page` | 1 | - | Numer strony |
| `limit` | 50 | 100 | Liczba wyników na stronę |

### 7.2. Format odpowiedzi z paginacją

```json
{
  "data": [ /* array of items */ ],
  "meta": {
    "currentPage": 1,
    "perPage": 50,
    "total": 150,
    "totalPages": 3
  }
}
```

---

## 8. Filtry i sortowanie

### 8.1. Filtry dla GET /api/inspections

| Parametr | Typ | Opis |
|----------|-----|------|
| `startDate` | string (YYYY-MM-DD) | Data początku zakresu |
| `endDate` | string (YYYY-MM-DD) | Data końca zakresu |
| `createdByUserId` | integer | ID użytkownika twórcy |

**Przykład:**
```
GET /api/inspections?startDate=2025-10-15&endDate=2025-10-22&createdByUserId=1
```

### 8.2. Filtry dla GET /api/users

| Parametr | Typ | Opis |
|----------|-----|------|
| `includeInactive` | boolean | Czy uwzględnić nieaktywnych (domyślnie: true) |
| `role` | string | Filtrowanie po roli: `consultant` lub `inspector` |

**Przykład:**
```
GET /api/users?includeInactive=false&role=consultant
```

### 8.3. Sortowanie (przyszłe rozszerzenie)

Format:
```
?sort=field:direction
```

Przykład:
```
GET /api/inspections?sort=startDatetime:asc
```

---

## 9. Integracja z FullCalendar.js

### 9.1. Endpoint dla FullCalendar

FullCalendar.js wymaga specyficznego formatu danych. Można dodać dedykowany endpoint:

#### GET /api/calendar/events

**Parametry zapytania:**
- `start` - Data początku w formacie ISO 8601
- `end` - Data końca w formacie ISO 8601

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "title": "Toyota Corolla (WA12345) - Anna Nowak",
    "start": "2025-10-15T10:00:00+02:00",
    "end": "2025-10-15T10:30:00+02:00",
    "backgroundColor": "#3788d8",
    "borderColor": "#2c6aad",
    "extendedProps": {
      "vehicleMake": "Toyota",
      "vehicleModel": "Corolla",
      "licensePlate": "WA12345",
      "clientName": "Anna Nowak",
      "phoneNumber": "+48123456789",
      "createdByUserName": "Jan Kowalski"
    }
  }
]
```

**Alternatywnie:**
Frontend może wykorzystać standardowy endpoint `GET /api/inspections` i przekształcić dane do formatu FullCalendar.

---

## 10. Versioning API (przyszłe rozszerzenie)

W przypadku przyszłych zmian w API, zaleca się wprowadzenie wersjonowania:

**Opcja 1: URL versioning**
```
/api/v1/inspections
/api/v2/inspections
```

**Opcja 2: Header versioning**
```
Accept: application/vnd.inspection.v1+json
```

Na etapie MVP versioning nie jest wymagane.

---

## 11. Notatki implementacyjne

### 11.1. Kontrolery do utworzenia

```
src/Controller/Api/
├── AuthenticationController.php (login, logout, me)
├── InspectionController.php (CRUD dla oględzin)
├── UserController.php (CRUD dla użytkowników)
└── CalendarController.php (opcjonalnie, dla integracji z FullCalendar)
```

### 11.2. Serwisy do utworzenia

```
src/Service/
├── InspectionValidationService.php (walidacja oględzin)
├── InspectionConflictChecker.php (sprawdzanie kolizji)
└── UserManagementService.php (zarządzanie użytkownikami)
```

### 11.3. Custom Validators

```
src/Validator/
├── Constraints/
│   ├── InspectionDatetime.php
│   ├── InspectionConflict.php
│   ├── WorkingHours.php
│   └── TimeSlot.php
└── Validators/
    ├── InspectionDatetimeValidator.php
    ├── InspectionConflictValidator.php
    ├── WorkingHoursValidator.php
    └── TimeSlotValidator.php
```

### 11.4. Serialization

Wykorzystanie Symfony Serializer do serializacji encji do JSON:

```php
use Symfony\Component\Serializer\SerializerInterface;

$json = $serializer->serialize($inspection, 'json', [
    'groups' => ['inspection:read']
]);
```

Konfiguracja grup serializacji w encjach:
```php
use Symfony\Component\Serializer\Annotation\Groups;

class Inspection
{
    #[Groups(['inspection:read', 'inspection:write'])]
    private string $vehicleMake;

    // ...
}
```

### 11.5. Security Configuration

Konfiguracja w `config/packages/security.yaml`:

```yaml
security:
    firewalls:
        api:
            pattern: ^/api
            stateless: false
            form_login:
                login_path: /api/login
                check_path: /api/login
            logout:
                path: /api/logout

    access_control:
        - { path: ^/api/login, roles: PUBLIC_ACCESS }
        - { path: ^/api/inspections$, roles: ROLE_USER, methods: [GET] }
        - { path: ^/api/inspections, roles: ROLE_CONSULTANT, methods: [POST, PUT, DELETE] }
        - { path: ^/api/users, roles: ROLE_CONSULTANT }
        - { path: ^/api, roles: ROLE_USER }
```

---

## 12. Testowanie API

### 12.1. Testy jednostkowe (PHPUnit)

```
tests/
├── Service/
│   ├── InspectionValidationServiceTest.php
│   └── InspectionConflictCheckerTest.php
└── Validator/
    ├── InspectionDatetimeValidatorTest.php
    └── InspectionConflictValidatorTest.php
```

### 12.2. Testy funkcjonalne (PHPUnit)

```
tests/
└── Controller/
    ├── Api/
    │   ├── AuthenticationControllerTest.php
    │   ├── InspectionControllerTest.php
    │   └── UserControllerTest.php
```

### 12.3. Dokumentacja API (opcjonalnie)

Dla automatycznej dokumentacji można użyć:
- **NelmioApiDocBundle** - generuje dokumentację Swagger/OpenAPI
- **API Platform** - kompletne rozwiązanie dla API REST (ale może być zbyt rozbudowane dla MVP)

---

## 13. Podsumowanie

Plan API REST obejmuje:

✅ **15 endpointów** pokrywających wszystkie wymagania z PRD:
- 3 endpointy uwierzytelniania
- 6 endpointów zarządzania oględzinami
- 5 endpointów zarządzania użytkownikami
- 1 dodatkowy endpoint dla FullCalendar (opcjonalnie)

✅ **Pełna walidacja** zgodna ze schematem bazy danych i PRD:
- Walidacja pól
- Walidacja terminów (godziny pracy, weekendy, sloty czasowe)
- Sprawdzanie kolizji z 15-minutowymi przerwami
- Walidacja unikalności loginów

✅ **Kontrola dostępu** oparta na rolach:
- Inspektorzy: tylko odczyt
- Konsultanci: pełne uprawnienia

✅ **Spójność z technologią**:
- Session-based authentication (Symfony Security)
- Doctrine ORM dla dostępu do bazy
- Symfony Validator dla walidacji
- RESTful design patterns

✅ **Gotowość do integracji z frontendem**:
- Format JSON
- Obsługa FullCalendar.js
- Paginacja i filtrowanie
- Szczegółowe komunikaty błędów

Ten plan API stanowi solidną podstawę do implementacji systemu zgodnie z wymaganiami PRD i jest dostosowany do stacku technologicznego Symfony 7.3.
