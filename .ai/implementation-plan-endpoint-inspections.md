# Plan Implementacji Endpointu API: GET /api/inspections

## 1. Przegląd punktu końcowego

Endpoint `GET /api/inspections` służy do pobierania listy zaplanowanych oględzin pojazdów powypadkowych z możliwością 
filtrowania po zakresie dat i twórcy oraz z obsługą paginacji. Endpoint jest dostępny dla wszystkich zalogowanych 
użytkowników (zarówno konsultantów jak i inspektorów) i zwraca dane w formacie JSON zgodnym z potrzebami aplikacji 
frontendowej oraz komponentu FullCalendar.

**Główne funkcjonalności:**
- Pobieranie listy oględzin z paginacją
- Filtrowanie po zakresie dat (startDate, endDate)
- Filtrowanie po twórcy (createdByUserId)
- Zwracanie metadanych paginacji (total, currentPage, totalPages, perPage)
- Wzbogacenie danych o informacje o użytkowniku, który utworzył oględziny

## 2. Szczegóły żądania

### 2.1. Metoda HTTP i URL
- **Metoda:** `GET`
- **URL:** `/api/inspections`
- **Format:** `GET /api/inspections?startDate=2025-10-15&endDate=2025-10-22&page=1&limit=50&createdByUserId=1`

### 2.2. Parametry zapytania (Query Parameters)

Wszystkie parametry są **opcjonalne**:

| Parametr | Typ | Domyślna wartość | Opis | Walidacja |
|----------|-----|------------------|------|-----------|
| `startDate` | string | null | Data rozpoczęcia zakresu (YYYY-MM-DD) | Format YYYY-MM-DD, poprawna data |
| `endDate` | string | null | Data zakończenia zakresu (YYYY-MM-DD) | Format YYYY-MM-DD, poprawna data, >= startDate |
| `page` | integer | 1 | Numer strony | Integer > 0 |
| `limit` | integer | 50 | Liczba wyników na stronę | Integer, zakres 1-100 |
| `createdByUserId` | integer | null | ID użytkownika twórcy | Integer > 0, użytkownik musi istnieć |

### 2.3. Headers
- **Authorization:** Session cookie (automatycznie obsługiwane przez Symfony Security)
- **Accept:** `application/json`

### 2.4. Request Body
Brak (endpoint GET)

## 3. Wykorzystywane typy

### 3.1. Istniejące encje
- **`App\Entity\Inspection`** - główna encja reprezentująca oględziny
- **`App\Entity\User`** - encja użytkownika powiązana z oględzinami

### 3.2. DTOs do utworzenia

#### 3.2.1. `InspectionResponseDto`
Reprezentuje pojedyncze oględziny w odpowiedzi API.

```php
namespace App\Dto;

use DateTimeImmutable;

class InspectionResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $startDatetime,
        public readonly string $endDatetime,
        public readonly string $vehicleMake,
        public readonly string $vehicleModel,
        public readonly string $licensePlate,
        public readonly string $clientName,
        public readonly string $phoneNumber,
        public readonly UserBasicDto $createdByUser,
        public readonly string $createdAt,
        public readonly bool $isPast
    ) {}
}
```

#### 3.2.2. `UserBasicDto`
Reprezentuje podstawowe informacje o użytkowniku w odpowiedzi API.

```php
namespace App\Dto;

class UserBasicDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}
}
```

#### 3.2.3. `InspectionListResponseDto`
Reprezentuje całą odpowiedź API z paginacją.

```php
namespace App\Dto;

class InspectionListResponseDto
{
    /**
     * @param InspectionResponseDto[] $data
     * @param PaginationMetaDto $meta
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationMetaDto $meta
    ) {}
}
```

#### 3.2.4. `PaginationMetaDto`
Reprezentuje metadane paginacji.

```php
namespace App\Dto;

class PaginationMetaDto
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages
    ) {}
}
```

### 3.3. Klasa żądania (Request Query DTO)
Dla walidacji parametrów zapytania.

#### `InspectionListQueryDto`
```php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class InspectionListQueryDto
{
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
    )]
    public ?string $startDate = null;

    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
    )]
    public ?string $endDate = null;

    #[Assert\Positive(message: 'Parametr page musi być liczbą całkowitą większą od 0')]
    public int $page = 1;

    #[Assert\Range(
        min: 1,
        max: 100,
        notInRangeMessage: 'Parametr limit musi być w zakresie od {{ min }} do {{ max }}'
    )]
    public int $limit = 50;

    #[Assert\Positive(message: 'Parametr createdByUserId musi być liczbą całkowitą większą od 0')]
    public ?int $createdByUserId = null;
}
```

## 4. Szczegóły odpowiedzi

### 4.1. Sukces (200 OK)

**Format odpowiedzi:**
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

**Opis pól:**
- `data` - tablica obiektów reprezentujących oględziny
- `meta.currentPage` - aktualny numer strony
- `meta.perPage` - liczba wyników na stronę
- `meta.total` - całkowita liczba wyników
- `meta.totalPages` - całkowita liczba stron
- `startDatetime`, `endDatetime`, `createdAt` - w formacie ISO 8601 z timezone
- `isPast` - boolean informujący czy oględziny są w przeszłości

### 4.2. Pusta lista (200 OK)
```json
{
  "data": [],
  "meta": {
    "currentPage": 1,
    "perPage": 50,
    "total": 0,
    "totalPages": 0
  }
}
```

## 5. Przepływ danych

### 5.1. Diagram przepływu

```
1. Request -> Symfony Router
2. Router -> Security Layer (sprawdzenie ROLE_USER)
3. Security -> InspectionController::list()
4. Controller -> Walidacja query parameters (InspectionListQueryDto)
5. Controller -> InspectionService::getInspections()
6. Service -> InspectionRepository::findWithFiltersAndPagination()
7. Repository -> Database Query (z JOIN do users)
8. Database -> Return Inspection entities
9. Repository -> Service (array of Inspection entities)
10. Service -> Transform to InspectionResponseDto[]
11. Service -> Calculate pagination metadata
12. Service -> Return InspectionListResponseDto
13. Controller -> Serialize to JSON
14. Controller -> Return JsonResponse (200 OK)
```

### 5.2. Szczegółowy przepływ w serwisie

**InspectionService::getInspections():**
1. Walidacja zakresu dat (jeśli oba parametry podane, sprawdź czy startDate <= endDate)
2. Jeśli podano `createdByUserId`, sprawdź czy użytkownik istnieje
3. Wywołanie repozytorium z filtrami
4. Obliczenie offset dla paginacji: `offset = (page - 1) * limit`
5. Pobranie danych z bazy (z limitem i offsetem)
6. Transformacja każdego `Inspection` do `InspectionResponseDto`
7. Pobranie całkowitej liczby wyników (total count)
8. Obliczenie liczby stron: `totalPages = ceil(total / limit)`
9. Utworzenie `PaginationMetaDto`
10. Zwrócenie `InspectionListResponseDto`

### 5.3. Zapytanie do bazy danych

```sql
SELECT i.*, u.id as user_id, u.name as user_name
FROM inspections i
INNER JOIN users u ON i.created_by_user_id = u.id
WHERE
  (i.start_datetime >= :startDate OR :startDate IS NULL)
  AND (i.start_datetime <= :endDate OR :endDate IS NULL)
  AND (i.created_by_user_id = :userId OR :userId IS NULL)
ORDER BY i.start_datetime ASC
LIMIT :limit OFFSET :offset;

-- Osobne zapytanie dla count:
SELECT COUNT(i.id)
FROM inspections i
WHERE
  (i.start_datetime >= :startDate OR :startDate IS NULL)
  AND (i.start_datetime <= :endDate OR :endDate IS NULL)
  AND (i.created_by_user_id = :userId OR :userId IS NULL);
```

## 6. Względy bezpieczeństwa

### 6.1. Uwierzytelnianie
- **Mechanizm:** Session-based authentication (Symfony Security)
- **Wymagana rola:** `ROLE_USER` (udzielana automatycznie wszystkim zalogowanym użytkownikom)
- **Konfiguracja w security.yaml:**
```yaml
access_control:
    - { path: ^/api/inspections$, roles: ROLE_USER, methods: [GET] }
```

### 6.2. Autoryzacja
- Wszyscy zalogowani użytkownicy (konsultanci i inspektorzy) mogą przeglądać wszystkie oględziny
- Brak dodatkowych ograniczeń dostępu na poziomie danych

### 6.3. Ochrona przed atakami

#### SQL Injection
- **Zabezpieczenie:** Doctrine ORM automatycznie używa parametryzowanych zapytań
- **Action:** Upewnić się, że wszystkie parametry są przekazywane przez `setParameter()`

#### Mass Assignment
- **Nie dotyczy** - endpoint tylko do odczytu (GET)

#### Excessive Data Exposure
- **Zabezpieczenie:** Używanie DTOs ogranicza dane tylko do niezbędnych pól
- **Action:** Nie zwracać hasła użytkownika ani innych wrażliwych danych

#### IDOR (Insecure Direct Object Reference)
- **Potencjalne zagrożenie:** Parametr `createdByUserId` pozwala na filtrowanie po konkretnym użytkowniku
- **Ocena ryzyka:** Niskie - zgodnie z PRD wszyscy użytkownicy mogą widzieć wszystkie oględziny
- **Action:** Brak dodatkowych zabezpieczeń wymaganych

#### Rate Limiting
- **Status:** Nie wymagane dla MVP
- **Przyszłe rozszerzenie:** Rozważyć dodanie limitu żądań (np. 100 żądań/minutę)

### 6.4. Walidacja danych wejściowych
- Wszystkie parametry query muszą przejść walidację przez Symfony Validator
- Niebezpieczne znaki w parametrach są automatycznie eskejpowane przez Doctrine
- Daty muszą być w formacie YYYY-MM-DD
- Wartości numeryczne muszą być w dopuszczalnych zakresach

### 6.5. HTTPS
- **Wymagane w produkcji:** Tak
- **Action:** Skonfigurować redirect HTTP -> HTTPS w konfiguracji serwera/routingu

## 7. Obsługa błędów

### 7.1. Tabela kodów błędów

| Kod HTTP | Scenariusz | Response Body | Logowanie |
|----------|-----------|---------------|-----------|
| 200 | Sukces | Lista oględzin z meta | Nie |
| 400 | Nieprawidłowy format daty | `{"success": false, "error": "Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD"}` | Warning |
| 400 | startDate > endDate | `{"success": false, "error": "Data rozpoczęcia nie może być późniejsza niż data zakończenia"}` | Warning |
| 400 | Nieprawidłowa wartość page | `{"success": false, "error": "Parametr page musi być liczbą całkowitą większą od 0"}` | Warning |
| 400 | limit > 100 | `{"success": false, "error": "Parametr limit musi być w zakresie od 1 do 100"}` | Warning |
| 400 | Błędy walidacji (wiele) | `{"success": false, "errors": {"page": "...", "limit": "..."}}` | Warning |
| 401 | Brak uwierzytelnienia | `{"success": false, "error": "Wymagane uwierzytelnienie"}` | Info |
| 404 | Użytkownik nie istnieje | `{"success": false, "error": "Nie znaleziono użytkownika o podanym ID"}` | Warning |
| 500 | Błąd serwera/bazy | `{"success": false, "error": "Wystąpił błąd serwera"}` | Error + Stack trace |

### 7.2. Struktura odpowiedzi błędu

**Pojedynczy błąd:**
```json
{
  "success": false,
  "error": "Opis błędu"
}
```

**Wiele błędów walidacji:**
```json
{
  "success": false,
  "errors": {
    "page": "Parametr page musi być liczbą całkowitą większą od 0",
    "limit": "Parametr limit musi być w zakresie od 1 do 100"
  }
}
```

### 7.3. Obsługa wyjątków w kontrolerze

```php
try {
    // Walidacja
    // Wywołanie serwisu
    // Zwrot odpowiedzi
} catch (InvalidArgumentException $e) {
    $this->logger->warning('Invalid request parameters', [
        'exception' => $e->getMessage(),
        'params' => $request->query->all()
    ]);
    return new JsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], Response::HTTP_BAD_REQUEST);
} catch (\Exception $e) {
    $this->logger->error('Error fetching inspections', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    return new JsonResponse([
        'success' => false,
        'error' => 'Wystąpił błąd serwera'
    ], Response::HTTP_INTERNAL_SERVER_ERROR);
}
```

### 7.4. Logowanie

**Format logów:**
- **Level INFO:** Udane żądania z podstawowymi informacjami (opcjonalnie)
- **Level WARNING:** Błędy walidacji, nieprawidłowe parametry
- **Level ERROR:** Błędy serwera, błędy bazy danych

**Przykład:**
```
[2025-10-18 15:30:45] app.WARNING: Invalid request parameters {"exception":"Nieprawidłowy format daty","params":{"startDate":"2025-13-40"}}
```

## 8. Rozważania dotyczące wydajności

### 8.1. Potencjalne wąskie gardła

1. **Problem N+1 zapytań** - pobieranie relacji User dla każdego Inspection
   - **Rozwiązanie:** Użyć JOIN w zapytaniu repozytorium

2. **Duża liczba wyników** - brak limitów na liczbę zwracanych wyników
   - **Rozwiązanie:** Wymuszenie maksymalnego limitu (100) i domyślnego (50)

3. **Sortowanie i filtrowanie na dużych zestawach danych**
   - **Rozwiązanie:** Wykorzystanie istniejących indeksów (start_datetime, end_datetime, created_by_user_id)

4. **Brak cache'owania często używanych zapytań**
   - **Rozwiązanie:** Opcjonalnie dodać cache dla statycznych danych (np. Symfony Cache)

### 8.2. Optymalizacje

#### 8.2.1. Eager loading relacji User
```php
// W InspectionRepository
public function findWithFiltersAndPagination(...): array
{
    return $this->createQueryBuilder('i')
        ->select('i', 'u')  // SELECT both entities
        ->innerJoin('i.createdByUser', 'u')  // JOIN user
        ->where(...)
        ->getQuery()
        ->getResult();
}
```

#### 8.2.2. Wykorzystanie indeksów
- Indeksy już istnieją na `start_datetime`, `end_datetime`, `created_by_user_id`
- Zapytania powinny wykorzystywać te indeksy automatycznie

#### 8.2.3. Paginacja
- Używać `setFirstResult()` i `setMaxResults()` w Doctrine
- Osobne zapytanie COUNT dla obliczenia totalPages (zoptymalizowane)

#### 8.2.4. Partial selects (opcjonalnie)
- Jeśli niepotrzebne są wszystkie pola, można użyć partial selects
- **Uwaga:** Dla MVP nie jest to konieczne

### 8.3. Monitoring wydajności
- Monitorować czas odpowiedzi endpointu (powinien być < 500ms dla typowych zapytań)
- Monitorować liczbę zapytań SQL na request (powinno być max 2: SELECT + COUNT)
- Doctrine Query Logger w środowisku dev do debugowania

## 9. Etapy wdrożenia

### Krok 1: Przygotowanie struktury katalogów i konfiguracji
**Czas: 15 min**

1.1. Utworzyć katalog `src/Controller/Api/` jeśli nie istnieje

1.2. Utworzyć katalog `src/Dto/` jeśli nie istnieje

1.3. Utworzyć katalog `src/Service/` jeśli nie istnieje

1.4. Utworzyć plik konfiguracji routingu `config/routes/api.yaml`:
```yaml
# config/routes/api.yaml
api_inspections_list:
    path: /api/inspections
    controller: App\Controller\Api\InspectionController::list
    methods: [GET]
```

1.5. Zaimportować routing w głównym pliku `config/routes.yaml`:
```yaml
# config/routes.yaml
api_routes:
    resource: routes/api.yaml
```

1.6. Zaktualizować `config/packages/security.yaml`:
```yaml
security:
    access_control:
        # ... istniejące reguły
        - { path: ^/api/inspections$, roles: ROLE_USER, methods: [GET] }
        - { path: ^/api, roles: ROLE_USER }  # Ogólna reguła dla całego API
```

### Krok 2: Utworzenie DTOs
**Czas: 30 min**

2.1. Utworzyć `src/Dto/UserBasicDto.php`:
```php
<?php

namespace App\Dto;

class UserBasicDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

    public static function fromEntity(\App\Entity\User $user): self
    {
        return new self(
            id: $user->getId(),
            name: $user->getName()
        );
    }
}
```

2.2. Utworzyć `src/Dto/InspectionResponseDto.php`:
```php
<?php

namespace App\Dto;

use App\Entity\Inspection;

class InspectionResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $startDatetime,
        public readonly string $endDatetime,
        public readonly string $vehicleMake,
        public readonly string $vehicleModel,
        public readonly string $licensePlate,
        public readonly string $clientName,
        public readonly string $phoneNumber,
        public readonly UserBasicDto $createdByUser,
        public readonly string $createdAt,
        public readonly bool $isPast
    ) {}

    public static function fromEntity(Inspection $inspection): self
    {
        return new self(
            id: $inspection->getId(),
            startDatetime: $inspection->getStartDatetime()->format('c'), // ISO 8601
            endDatetime: $inspection->getEndDatetime()->format('c'),
            vehicleMake: $inspection->getVehicleMake(),
            vehicleModel: $inspection->getVehicleModel(),
            licensePlate: $inspection->getLicensePlate(),
            clientName: $inspection->getClientName(),
            phoneNumber: $inspection->getPhoneNumber(),
            createdByUser: UserBasicDto::fromEntity($inspection->getCreatedByUser()),
            createdAt: $inspection->getCreatedAt()->format('c'),
            isPast: $inspection->isPast()
        );
    }
}
```

2.3. Utworzyć `src/Dto/PaginationMetaDto.php`:
```php
<?php

namespace App\Dto;

class PaginationMetaDto
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages
    ) {}
}
```

2.4. Utworzyć `src/Dto/InspectionListResponseDto.php`:
```php
<?php

namespace App\Dto;

class InspectionListResponseDto
{
    /**
     * @param InspectionResponseDto[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationMetaDto $meta
    ) {}
}
```

2.5. Utworzyć `src/Dto/InspectionListQueryDto.php`:
```php
<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class InspectionListQueryDto
{
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
    )]
    public ?string $startDate = null;

    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
    )]
    public ?string $endDate = null;

    #[Assert\Positive(message: 'Parametr page musi być liczbą całkowitą większą od 0')]
    public int $page = 1;

    #[Assert\Range(
        min: 1,
        max: 100,
        notInRangeMessage: 'Parametr limit musi być w zakresie od {{ min }} do {{ max }}'
    )]
    public int $limit = 50;

    #[Assert\Positive(message: 'Parametr createdByUserId musi być liczbą całkowitą większą od 0')]
    public ?int $createdByUserId = null;
}
```

### Krok 3: Rozszerzenie InspectionRepository
**Czas: 30 min**

3.1. Dodać metodę `findWithFiltersAndPagination()` w `src/Repository/InspectionRepository.php`:

```php
/**
 * Find inspections with filters and pagination
 *
 * @return array{inspections: Inspection[], total: int}
 */
public function findWithFiltersAndPagination(
    ?\DateTimeImmutable $startDate,
    ?\DateTimeImmutable $endDate,
    ?int $createdByUserId,
    int $page,
    int $limit
): array {
    $offset = ($page - 1) * $limit;

    // Query builder for inspections
    $qb = $this->createQueryBuilder('i')
        ->select('i', 'u')  // Select both inspection and user to avoid N+1
        ->innerJoin('i.createdByUser', 'u');

    // Apply filters
    if ($startDate !== null) {
        $qb->andWhere('i.startDatetime >= :startDate')
            ->setParameter('startDate', $startDate);
    }

    if ($endDate !== null) {
        // End of the end date (23:59:59)
        $endOfDay = $endDate->setTime(23, 59, 59);
        $qb->andWhere('i.startDatetime <= :endDate')
            ->setParameter('endDate', $endOfDay);
    }

    if ($createdByUserId !== null) {
        $qb->andWhere('i.createdByUser = :userId')
            ->setParameter('userId', $createdByUserId);
    }

    // Clone query builder for count
    $countQb = clone $qb;
    $total = (int) $countQb
        ->select('COUNT(i.id)')
        ->getQuery()
        ->getSingleScalarResult();

    // Apply pagination and ordering
    $inspections = $qb
        ->orderBy('i.startDatetime', 'ASC')
        ->setFirstResult($offset)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();

    return [
        'inspections' => $inspections,
        'total' => $total
    ];
}
```

### Krok 4: Utworzenie InspectionService
**Czas: 45 min**

4.1. Utworzyć `src/Service/InspectionService.php`:

```php
<?php

namespace App\Service;

use App\Dto\InspectionListQueryDto;
use App\Dto\InspectionListResponseDto;
use App\Dto\InspectionResponseDto;
use App\Dto\PaginationMetaDto;
use App\Repository\InspectionRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;

class InspectionService
{
    public function __construct(
        private readonly InspectionRepository $inspectionRepository,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get inspections list with filters and pagination
     *
     * @throws \InvalidArgumentException
     */
    public function getInspections(InspectionListQueryDto $query): InspectionListResponseDto
    {
        // Validate date range
        if ($query->startDate !== null && $query->endDate !== null) {
            $start = \DateTimeImmutable::createFromFormat('Y-m-d', $query->startDate);
            $end = \DateTimeImmutable::createFromFormat('Y-m-d', $query->endDate);

            if ($start > $end) {
                throw new \InvalidArgumentException(
                    'Data rozpoczęcia nie może być późniejsza niż data zakończenia'
                );
            }
        }

        // Validate user exists if provided
        if ($query->createdByUserId !== null) {
            $user = $this->userRepository->find($query->createdByUserId);
            if ($user === null) {
                throw new \InvalidArgumentException(
                    'Nie znaleziono użytkownika o podanym ID'
                );
            }
        }

        // Convert string dates to DateTimeImmutable
        $startDate = $query->startDate !== null
            ? \DateTimeImmutable::createFromFormat('Y-m-d', $query->startDate)->setTime(0, 0, 0)
            : null;
        $endDate = $query->endDate !== null
            ? \DateTimeImmutable::createFromFormat('Y-m-d', $query->endDate)->setTime(23, 59, 59)
            : null;

        // Fetch data from repository
        $result = $this->inspectionRepository->findWithFiltersAndPagination(
            startDate: $startDate,
            endDate: $endDate,
            createdByUserId: $query->createdByUserId,
            page: $query->page,
            limit: $query->limit
        );

        // Transform entities to DTOs
        $inspectionDtos = array_map(
            fn($inspection) => InspectionResponseDto::fromEntity($inspection),
            $result['inspections']
        );

        // Calculate pagination metadata
        $totalPages = (int) ceil($result['total'] / $query->limit);
        $meta = new PaginationMetaDto(
            currentPage: $query->page,
            perPage: $query->limit,
            total: $result['total'],
            totalPages: $totalPages
        );

        $this->logger->info('Inspections list fetched', [
            'filters' => [
                'startDate' => $query->startDate,
                'endDate' => $query->endDate,
                'createdByUserId' => $query->createdByUserId
            ],
            'pagination' => [
                'page' => $query->page,
                'limit' => $query->limit,
                'total' => $result['total']
            ]
        ]);

        return new InspectionListResponseDto(
            data: $inspectionDtos,
            meta: $meta
        );
    }
}
```

### Krok 5: Utworzenie kontrolera API
**Czas: 45 min**

5.1. Utworzyć `src/Controller/Api/InspectionController.php`:

```php
<?php

namespace App\Controller\Api;

use App\Dto\InspectionListQueryDto;
use App\Service\InspectionService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InspectionController extends AbstractController
{
    public function __construct(
        private readonly InspectionService $inspectionService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get list of inspections with filters and pagination
     *
     * @Route("/api/inspections", name="api_inspections_list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        try {
            // Build query DTO from request
            $queryDto = new InspectionListQueryDto();
            $queryDto->startDate = $request->query->get('startDate');
            $queryDto->endDate = $request->query->get('endDate');
            $queryDto->page = (int) ($request->query->get('page', 1));
            $queryDto->limit = (int) ($request->query->get('limit', 50));
            $queryDto->createdByUserId = $request->query->has('createdByUserId')
                ? (int) $request->query->get('createdByUserId')
                : null;

            // Validate query parameters
            $violations = $this->validator->validate($queryDto);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                $this->logger->warning('Invalid request parameters', [
                    'errors' => $errors,
                    'params' => $request->query->all()
                ]);

                return new JsonResponse([
                    'success' => false,
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            // Call service
            $response = $this->inspectionService->getInspections($queryDto);

            // Return JSON response
            return new JsonResponse($response, Response::HTTP_OK);

        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Invalid request parameters', [
                'exception' => $e->getMessage(),
                'params' => $request->query->all()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            $this->logger->error('Error fetching inspections', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $request->query->all()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Wystąpił błąd serwera'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
```

### Krok 6: Testy jednostkowe i funkcjonalne
**Czas: 2 godziny**

6.1. Utworzyć test dla InspectionService: `tests/Service/InspectionServiceTest.php`

```php
<?php

namespace App\Tests\Service;

use App\Dto\InspectionListQueryDto;
use App\Service\InspectionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InspectionServiceTest extends KernelTestCase
{
    private InspectionService $inspectionService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->inspectionService = $container->get(InspectionService::class);
    }

    public function testGetInspectionsWithoutFilters(): void
    {
        $query = new InspectionListQueryDto();
        $response = $this->inspectionService->getInspections($query);

        $this->assertIsArray($response->data);
        $this->assertInstanceOf(PaginationMetaDto::class, $response->meta);
        $this->assertGreaterThanOrEqual(0, $response->meta->total);
    }

    public function testGetInspectionsWithDateRange(): void
    {
        $query = new InspectionListQueryDto();
        $query->startDate = '2025-10-01';
        $query->endDate = '2025-10-31';

        $response = $this->inspectionService->getInspections($query);

        $this->assertIsArray($response->data);
    }

    public function testInvalidDateRangeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data rozpoczęcia nie może być późniejsza niż data zakończenia');

        $query = new InspectionListQueryDto();
        $query->startDate = '2025-10-31';
        $query->endDate = '2025-10-01';

        $this->inspectionService->getInspections($query);
    }

    public function testInvalidUserIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie znaleziono użytkownika o podanym ID');

        $query = new InspectionListQueryDto();
        $query->createdByUserId = 99999; // Non-existent user

        $this->inspectionService->getInspections($query);
    }

    public function testPaginationWorks(): void
    {
        $query = new InspectionListQueryDto();
        $query->page = 1;
        $query->limit = 10;

        $response = $this->inspectionService->getInspections($query);

        $this->assertEquals(1, $response->meta->currentPage);
        $this->assertEquals(10, $response->meta->perPage);
        $this->assertCount(min(10, $response->meta->total), $response->data);
    }
}
```

6.2. Utworzyć test funkcjonalny dla kontrolera: `tests/Controller/Api/InspectionControllerTest.php`

```php
<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InspectionControllerTest extends WebTestCase
{
    public function testListEndpointRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/inspections');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListEndpointReturnsJsonForAuthenticatedUser(): void
    {
        $client = static::createClient();

        // Mock authentication (adjust based on your test setup)
        // This assumes you have a way to authenticate test users

        $client->request('GET', '/api/inspections');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
    }

    public function testListEndpointWithInvalidDateFormat(): void
    {
        $client = static::createClient();

        // Authenticate...

        $client->request('GET', '/api/inspections?startDate=invalid-date');

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    public function testListEndpointWithValidFilters(): void
    {
        $client = static::createClient();

        // Authenticate...

        $client->request('GET', '/api/inspections', [
            'startDate' => '2025-10-01',
            'endDate' => '2025-10-31',
            'page' => 1,
            'limit' => 20
        ]);

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data['data']);
        $this->assertEquals(1, $data['meta']['currentPage']);
        $this->assertEquals(20, $data['meta']['perPage']);
    }
}
```

### Krok 7: Uruchomienie i testowanie
**Czas: 30 min**

7.1. Wyczyścić cache Symfony:
```bash
php bin/console cache:clear
```

7.2. Uruchomić testy:
```bash
vendor/bin/phpunit tests/Service/InspectionServiceTest.php
vendor/bin/phpunit tests/Controller/Api/InspectionControllerTest.php
```

7.3. Przetestować endpoint manualnie (używając curl lub Postman):
```bash
# Test 1: Basic request
curl -X GET "http://localhost/api/inspections" -H "Cookie: PHPSESSID=your-session-id"

# Test 2: With date filters
curl -X GET "http://localhost/api/inspections?startDate=2025-10-01&endDate=2025-10-31" -H "Cookie: PHPSESSID=your-session-id"

# Test 3: With pagination
curl -X GET "http://localhost/api/inspections?page=1&limit=10" -H "Cookie: PHPSESSID=your-session-id"

# Test 4: With user filter
curl -X GET "http://localhost/api/inspections?createdByUserId=1" -H "Cookie: PHPSESSID=your-session-id"

# Test 5: Invalid date format (should return 400)
curl -X GET "http://localhost/api/inspections?startDate=invalid" -H "Cookie: PHPSESSID=your-session-id"
```

### Krok 8: Dokumentacja i finalizacja
**Czas: 30 min**

8.1. Dodać dokumentację API w komentarzach kontrolera (PhpDoc)

8.2. Opcjonalnie: Utworzyć dokumentację OpenAPI/Swagger jeśli używany jest NelmioApiDocBundle

8.3. Zaktualizować README.md z informacją o nowym endpoincie

8.4. Code review - sprawdzić:
- Czy wszystkie testy przechodzą
- Czy kod jest zgodny ze standardami projektu (PSR-12)
- Czy logowanie działa poprawnie
- Czy obsługa błędów jest kompletna
- Czy wydajność jest akceptowalna (sprawdzić liczbę zapytań SQL)

8.5. Uruchomić Psalm dla statycznej analizy:
```bash
vendor/bin/psalm src/Controller/Api/InspectionController.php
vendor/bin/psalm src/Service/InspectionService.php
vendor/bin/psalm src/Dto/
```

## 10. Checklist implementacji

- [ ] Utworzono struktur katalogów (Api/, Dto/, Service/)
- [ ] Skonfigurowano routing w `config/routes/api.yaml`
- [ ] Zaktualizowano `security.yaml` z regułami dostępu
- [ ] Utworzono wszystkie DTOs (5 klas)
- [ ] Rozszerzono InspectionRepository o metodę `findWithFiltersAndPagination()`
- [ ] Utworzono InspectionService z metodą `getInspections()`
- [ ] Utworzono InspectionController z metodą `list()`
- [ ] Napisano testy jednostkowe dla InspectionService
- [ ] Napisano testy funkcjonalne dla InspectionController
- [ ] Wszystkie testy przechodzą pomyślnie
- [ ] Przetestowano endpoint manualnie
- [ ] Sprawdzono wydajność (liczba zapytań SQL, czas odpowiedzi)
- [ ] Dodano dokumentację w kodzie
- [ ] Przeprowadzono code review
- [ ] Uruchomiono Psalm i naprawiono błędy
- [ ] Zaktualizowano dokumentację projektu

## 11. Notatki implementacyjne

### 11.1. Użycie Symfony Serializer (alternatywne podejście)

Zamiast manualnej transformacji do DTOs, można użyć Symfony Serializer:

```php
// W kontrolerze
use Symfony\Component\Serializer\SerializerInterface;

return $this->json($response, Response::HTTP_OK, [], [
    'groups' => ['inspection:list']
]);
```

Wymaga to dodania grup serializacji w encjach:
```php
use Symfony\Component\Serializer\Annotation\Groups;

class Inspection
{
    #[Groups(['inspection:list'])]
    private int $id;

    // ...
}
```

**Zalecenie:** Dla MVP stosować DTOs - dają większą kontrolę i są bardziej jawne.

### 11.2. Walidacja dat - alternatywne podejście

Można dodać niestandardowy validator Assert\Callback dla walidacji zakresu dat:

```php
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class InspectionListQueryDto
{
    // ... pola

    #[Assert\Callback]
    public function validateDateRange(ExecutionContextInterface $context): void
    {
        if ($this->startDate && $this->endDate) {
            $start = \DateTimeImmutable::createFromFormat('Y-m-d', $this->startDate);
            $end = \DateTimeImmutable::createFromFormat('Y-m-d', $this->endDate);

            if ($start > $end) {
                $context->buildViolation('Data rozpoczęcia nie może być późniejsza niż data zakończenia')
                    ->atPath('startDate')
                    ->addViolation();
            }
        }
    }
}
```

### 11.3. Obsługa strefy czasowej

Wszystkie daty są zwracane w formacie ISO 8601 z timezone (format 'c'):
```php
$inspection->getStartDatetime()->format('c'); // 2025-10-15T10:00:00+02:00
```

Upewnić się, że timezone jest poprawnie skonfigurowane w `php.ini`:
```ini
date.timezone = Europe/Warsaw
```

### 11.4. Cache (przyszłe rozszerzenie)

Dla często używanych zapytań można dodać cache:
```php
use Symfony\Contracts\Cache\CacheInterface;

$cacheKey = sprintf('inspections_list_%s', md5(json_encode($query)));

$response = $this->cache->get($cacheKey, function() use ($query) {
    return $this->inspectionService->getInspections($query);
});
```

**Uwaga:** Cache należy invalidować przy tworzeniu/edycji/usuwaniu oględzin.

### 11.5. API Versioning (przyszłe rozszerzenie)

Jeśli w przyszłości będzie potrzeba wersjonowania API:
```yaml
# config/routes/api.yaml
api_v1_inspections_list:
    path: /api/v1/inspections
    controller: App\Controller\Api\V1\InspectionController::list
```

## 12. Potencjalne problemy i rozwiązania

| Problem | Rozwiązanie |
|---------|-------------|
| Brak sesji użytkownika w testach | Mockować Security lub używać test fixtures z autentykacją |
| Problem N+1 zapytań | Używać JOIN w query builder (już zaimplementowane) |
| Zbyt duży limit stron | Dodać maksymalny limit (100) - już zaimplementowane |
| Nieprawidłowe timezone w datach | Ustawić timezone w php.ini i Symfony config |
| Wolne zapytania dla dużej liczby rekordów | Monitorować i dodać cache jeśli potrzeba |
| Błędy walidacji nie są przetłumaczone | Użyć translation domain w komunikatach walidacyjnych |

## 13. Podsumowanie

Plan implementacji obejmuje:
- ✅ 5 nowych klas DTO dla struktury danych
- ✅ 1 nową metodę w InspectionRepository
- ✅ 1 nowy serwis InspectionService
- ✅ 1 nowy kontroler API InspectionController
- ✅ Konfigurację routingu i security
- ✅ Testy jednostkowe i funkcjonalne
- ✅ Obsługę błędów i logowanie
- ✅ Paginację i filtrowanie
- ✅ Optymalizację wydajności (eager loading, indeksy)

**Szacowany całkowity czas implementacji:** 5-6 godzin

**Priorytet:** Wysoki (kluczowy endpoint dla MVP)

**Zależności:** Brak (wszystkie wymagane encje już istnieją)

**Ryzyka:** Niskie (standardowa implementacja REST API w Symfony)
