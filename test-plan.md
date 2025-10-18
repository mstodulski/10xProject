# Plan Testów - InspectionService::getInspections()

## 1. Wprowadzenie

Dokument opisuje plan testów jednostkowych dla metody `InspectionService::getInspections()` w systemie zarządzania
oględzinami pojazdów powypadkowych. Testy będą wykonywane z użyciem PHPUnit i mockowania zależności, bez użycia 
rzeczywistej bazy danych.

## 2. Testowana funkcjonalność

### 2.1. Opis metody

Metoda `getInspections(InspectionListQueryDto $query): InspectionListResponseDto` odpowiada za:
- Walidację parametrów zapytania (daty, ID użytkownika)
- Pobieranie listy oględzin z repozytorium z zastosowaniem filtrów
- Paginację wyników
- Transformację encji na obiekty DTO
- Zwracanie odpowiedzi w ustandaryzowanym formacie

### 2.2. Zależności do mockowania

- `InspectionRepository` - repozytorium oględzin
- `UserRepository` - repozytorium użytkowników
- `LoggerInterface` - logger do logowania operacji

## 3. Scenariusze testowe

### 3.1. Testy pomyślnego pobierania danych

#### TC-001: Pobieranie listy oględzin bez filtrów
**Cel:** Sprawdzenie, czy metoda poprawnie pobiera listę oględzin bez zastosowania filtrów.

**Warunki wstępne:**
- Brak parametrów filtrowania (startDate, endDate, createdByUserId = null)
- Domyślne wartości paginacji (page=1, limit=50)

**Oczekiwany rezultat:**
- Repozytorium zostaje wywołane z parametrami null dla filtrów
- Zwrócona lista zawiera przekonwertowane DTOs
- Metadane paginacji są poprawnie wyliczone
- Logger rejestruje operację

**Asercje:**
- `assertInstanceOf(InspectionListResponseDto::class, $result)`
- `assertCount(expectedCount, $result->data)`
- Weryfikacja wywołania `inspectionRepository->findWithFiltersAndPagination()`
- Weryfikacja wywołania `logger->info()`

#### TC-002: Pobieranie listy z filtrem dat
**Cel:** Sprawdzenie filtrowania według zakresu dat.

**Warunki wstępne:**
- startDate = '2025-10-15'
- endDate = '2025-10-22'
- Prawidłowy format dat

**Oczekiwany rezultat:**
- Daty są konwertowane na DateTimeImmutable
- startDate ustawiona na 00:00:00
- endDate ustawiona na 23:59:59
- Repozytorium wywołane z poprawnymi obiektami DateTimeImmutable

**Asercje:**
- Weryfikacja parametrów wywołania repozytorium
- Sprawdzenie konwersji dat

#### TC-003: Pobieranie listy z filtrem użytkownika
**Cel:** Sprawdzenie filtrowania według ID użytkownika, który utworzył oględziny.

**Warunki wstępne:**
- createdByUserId = 1
- Użytkownik o ID=1 istnieje w systemie

**Oczekiwany rezultat:**
- UserRepository sprawdza istnienie użytkownika
- Repozytorium zostaje wywołane z parametrem createdByUserId=1
- Zwrócone wyniki są przefiltrowane

**Asercje:**
- Weryfikacja wywołania `userRepository->find(1)`
- Weryfikacja przekazania parametru do repozytorium

#### TC-004: Pobieranie pustej listy
**Cel:** Sprawdzenie obsługi przypadku braku wyników.

**Warunki wstępne:**
- Repozytorium zwraca pustą tablicę

**Oczekiwany rezultat:**
- Zwrócona odpowiedź zawiera pustą tablicę data
- Metadane paginacji wskazują total=0, totalPages=0
- Brak błędów

**Asercje:**
- `assertCount(0, $result->data)`
- `assertEquals(0, $result->meta->total)`
- `assertEquals(0, $result->meta->totalPages)`

#### TC-005: Paginacja - pierwsza strona
**Cel:** Sprawdzenie poprawności paginacji dla pierwszej strony.

**Warunki wstępne:**
- page = 1
- limit = 10
- Łącznie 25 rekordów w bazie

**Oczekiwany rezultat:**
- Zwrócone 10 rekordów
- total = 25
- totalPages = 3
- currentPage = 1

**Asercje:**
- `assertEquals(1, $result->meta->currentPage)`
- `assertEquals(10, $result->meta->perPage)`
- `assertEquals(25, $result->meta->total)`
- `assertEquals(3, $result->meta->totalPages)`

#### TC-006: Paginacja - ostatnia strona
**Cel:** Sprawdzenie paginacji dla ostatniej strony z niepełną liczbą rekordów.

**Warunki wstępne:**
- page = 3
- limit = 10
- Łącznie 25 rekordów (ostatnia strona ma 5 rekordów)

**Oczekiwany rezultat:**
- Zwrócone 5 rekordów
- Poprawne metadane paginacji

**Asercje:**
- `assertCount(5, $result->data)`
- `assertEquals(3, $result->meta->currentPage)`
- `assertEquals(3, $result->meta->totalPages)`

### 3.2. Testy walidacji i błędów

#### TC-007: Nieprawidłowy format daty startowej
**Cel:** Sprawdzenie walidacji formatu daty.

**Warunki wstępne:**
- startDate = 'invalid-date'
- endDate = '2025-10-22'

**Oczekiwany rezultat:**
- Wyrzucony wyjątek InvalidArgumentException
- Komunikat: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'

**Asercje:**
- `expectException(InvalidArgumentException::class)`
- `expectExceptionMessage('Nieprawidłowy format daty')`

#### TC-008: Nieprawidłowy format daty końcowej
**Cel:** Sprawdzenie walidacji formatu daty końcowej.

**Warunki wstępne:**
- startDate = '2025-10-15'
- endDate = '22-10-2025' (nieprawidłowy format)

**Oczekiwany rezultat:**
- Wyrzucony wyjątek InvalidArgumentException
- Komunikat o błędnym formacie

**Asercje:**
- `expectException(InvalidArgumentException::class)`

#### TC-009: Data rozpoczęcia późniejsza niż data zakończenia
**Cel:** Sprawdzenie walidacji zakresu dat.

**Warunki wstępne:**
- startDate = '2025-10-22'
- endDate = '2025-10-15'

**Oczekiwany rezultat:**
- Wyrzucony wyjątek InvalidArgumentException
- Komunikat: 'Data rozpoczęcia nie może być późniejsza niż data zakończenia'

**Asercje:**
- `expectException(InvalidArgumentException::class)`
- `expectExceptionMessage('Data rozpoczęcia nie może być późniejsza')`

#### TC-010: Nieistniejący użytkownik
**Cel:** Sprawdzenie walidacji istnienia użytkownika.

**Warunki wstępne:**
- createdByUserId = 999 (nieistniejący)
- UserRepository->find(999) zwraca null

**Oczekiwany rezultat:**
- Wyrzucony wyjątek InvalidArgumentException
- Komunikat: 'Nie znaleziono użytkownika o podanym ID'

**Asercje:**
- `expectException(InvalidArgumentException::class)`
- `expectExceptionMessage('Nie znaleziono użytkownika')`
- Weryfikacja wywołania `userRepository->find(999)`

### 3.3. Testy logowania

#### TC-011: Logowanie pomyślnej operacji
**Cel:** Sprawdzenie, czy operacje są poprawnie logowane.

**Warunki wstępne:**
- Pomyślne pobranie listy oględzin

**Oczekiwany rezultat:**
- Logger wywołany z poziomem 'info'
- Log zawiera informacje o filtrach i paginacji

**Asercje:**
- Weryfikacja wywołania `logger->info()` z odpowiednimi parametrami
- Sprawdzenie struktury przekazanych danych do loggera

### 3.4. Testy transformacji danych

#### TC-012: Konwersja encji na DTOs
**Cel:** Sprawdzenie poprawności transformacji encji Inspection na InspectionResponseDto.

**Warunki wstępne:**
- Repozytorium zwraca listę encji Inspection

**Oczekiwany rezultat:**
- Każda encja jest konwertowana za pomocą InspectionResponseDto::fromEntity()
- Zwrócona lista zawiera obiekty InspectionResponseDto

**Asercje:**
- Sprawdzenie typu obiektów w tablicy data
- Weryfikacja, że liczba DTOs odpowiada liczbie encji

#### TC-013: Kalkulacja metadanych paginacji
**Cel:** Sprawdzenie poprawności obliczeń dla różnych scenariuszy paginacji.

**Warunki wstępne:**
- Różne kombinacje total, page, limit

**Przykładowe przypadki:**
- total=0, limit=10 → totalPages=0
- total=1, limit=10 → totalPages=1
- total=10, limit=10 → totalPages=1
- total=11, limit=10 → totalPages=2
- total=100, limit=10 → totalPages=10

**Oczekiwany rezultat:**
- Poprawnie wyliczona liczba stron (ceil(total/limit))

**Asercje:**
- Weryfikacja wartości totalPages dla różnych scenariuszy

### 3.5. Testy przypadków brzegowych

#### TC-014: Tylko startDate bez endDate
**Cel:** Sprawdzenie obsługi częściowego zakresu dat.

**Warunki wstępne:**
- startDate = '2025-10-15'
- endDate = null

**Oczekiwany rezultat:**
- Metoda działa poprawnie
- Tylko startDate jest konwertowana na DateTimeImmutable
- endDate pozostaje null

**Asercje:**
- Weryfikacja parametrów wywołania repozytorium

#### TC-015: Tylko endDate bez startDate
**Cel:** Sprawdzenie obsługi częściowego zakresu dat.

**Warunki wstępne:**
- startDate = null
- endDate = '2025-10-22'

**Oczekiwany rezultat:**
- Metoda działa poprawnie
- Tylko endDate jest konwertowana
- Walidacja zakresu jest pomijana (brak obu dat)

**Asercje:**
- Weryfikacja parametrów wywołania repozytorium

#### TC-016: Limit = 1 (minimalna paginacja)
**Cel:** Sprawdzenie paginacji z minimalnym limitem.

**Warunki wstępne:**
- limit = 1
- total = 5

**Oczekiwany rezultat:**
- totalPages = 5
- Każda strona zawiera 1 element

**Asercje:**
- `assertEquals(5, $result->meta->totalPages)`

#### TC-017: Bardzo duży limit
**Cel:** Sprawdzenie zachowania przy dużym limicie.

**Warunki wstępne:**
- limit = 1000
- total = 50

**Oczekiwany rezultat:**
- totalPages = 1
- Wszystkie wyniki na jednej stronie

**Asercje:**
- `assertEquals(1, $result->meta->totalPages)`

#### TC-018: Ta sama data rozpoczęcia i zakończenia
**Cel:** Sprawdzenie filtrowania dla jednego dnia.

**Warunki wstępne:**
- startDate = '2025-10-15'
- endDate = '2025-10-15'

**Oczekiwany rezultat:**
- Metoda działa poprawnie
- Zakres od 00:00:00 do 23:59:59 tego samego dnia

**Asercje:**
- Weryfikacja czasu w przekazanych datach do repozytorium

## 4. Struktura testów

### 4.1. Klasa testowa

```
InspectionServiceTest extends TestCase
```

### 4.2. Metody pomocnicze

- `setUp()`: Inicjalizacja mocków i instancji InspectionService
- `tearDown()`: Czyszczenie po testach (jeśli potrzebne)
- `createMockInspection()`: Tworzenie przykładowej encji Inspection
- `createMockUser()`: Tworzenie przykładowej encji User

### 4.3. Organizacja testów

Testy pogrupowane według kategorii:
- Testy sukcesu (test*)
- Testy walidacji (testValidation*)
- Testy przypadków brzegowych (testEdgeCase*)
- Testy logowania (testLogging*)

## 5. Mockowanie

### 5.1. InspectionRepository

**Mockowane metody:**
- `findWithFiltersAndPagination(startDate, endDate, createdByUserId, page, limit)`

**Zwracane dane:**
```php
[
    'inspections' => [...], // tablica encji
    'total' => int
]
```

### 5.2. UserRepository

**Mockowane metody:**
- `find($id)` - zwraca User lub null

### 5.3. LoggerInterface

**Mockowane metody:**
- `info($message, $context)`

## 6. Asercje

### 6.1. Typy asercji

- `assertEquals()` - porównanie wartości
- `assertSame()` - porównanie identyczności
- `assertCount()` - sprawdzenie liczby elementów
- `assertInstanceOf()` - sprawdzenie typu obiektu
- `assertNull()` / `assertNotNull()` - sprawdzenie null
- `expectException()` - oczekiwanie wyjątku
- `expectExceptionMessage()` - oczekiwanie komunikatu wyjątku

### 6.2. Weryfikacja wywołań mocków

- `expects($this->once())` - metoda wywołana raz
- `expects($this->never())` - metoda nigdy nie wywołana
- `with()` - weryfikacja parametrów wywołania
- `willReturn()` - określenie wartości zwracanej

## 7. Pokrycie testami

### 7.1. Cele pokrycia

- **Pokrycie linii kodu:** minimum 90%
- **Pokrycie ścieżek:** wszystkie główne ścieżki logiczne
- **Pokrycie warunków brzegowych:** wszystkie zidentyfikowane przypadki

### 7.2. Metryki

| Metryka | Cel |
|---------|-----|
| Line Coverage | 90%+ |
| Branch Coverage | 85%+ |
| Method Coverage | 100% |

## 8. Harmonogram wykonania

### 8.1. Faza 1: Testy podstawowe (TC-001 do TC-006)
- Testy pomyślnego pobierania danych
- Podstawowa paginacja

### 8.2. Faza 2: Testy walidacji (TC-007 do TC-010)
- Walidacja formatu dat
- Walidacja zakresu dat
- Walidacja użytkownika

### 8.3. Faza 3: Testy dodatkowe (TC-011 do TC-013)
- Logowanie
- Transformacja danych

### 8.4. Faza 4: Testy przypadków brzegowych (TC-014 do TC-018)
- Częściowe zakresy dat
- Ekstremalne wartości paginacji

## 9. Kryteria akceptacji

### 9.1. Wszystkie testy przechodzą

```bash
vendor/bin/phpunit tests/Service/InspectionServiceTest.php
```

### 9.2. Brak użycia rzeczywistej bazy danych

Wszystkie zależności są mockowane, brak połączeń z bazą danych podczas testów.

### 9.3. Szybkość wykonania

Wszystkie testy powinny wykonać się w czasie < 1 sekundy.

### 9.4. Czytelność i utrzymanie

- Nazwy testów jasno opisują testowany scenariusz
- Kod testów jest czytelny i dobrze udokumentowany
- Łatwe dodawanie nowych testów w przyszłości

## 10. Narzędzia i komendy

### 10.1. Uruchamianie testów

```bash
# Wszystkie testy InspectionService
vendor/bin/phpunit tests/Service/InspectionServiceTest.php

# Konkretny test
vendor/bin/phpunit tests/Service/InspectionServiceTest.php --filter testGetInspectionsWithoutFilters

# Z pokryciem kodu
vendor/bin/phpunit --coverage-html coverage/ tests/Service/InspectionServiceTest.php
```

### 10.2. Analiza statyczna

```bash
# Psalm
vendor/bin/psalm tests/Service/InspectionServiceTest.php
```

## 11. Uwagi techniczne

### 11.1. Wersje narzędzi

- PHPUnit: ^12.4 (zgodnie z composer.json)
- PHP: 8.2+
- Symfony: 7.3

### 11.2. Namespace

```php
namespace App\Tests\Service;
```

### 11.3. Autoloading

Testy znajdują się w katalogu `tests/Service/` zgodnie z konfiguracją autoloadera w composer.json.

## 12. Potencjalne problemy i rozwiązania

### 12.1. Problem: DateTimeImmutable w mockach
**Rozwiązanie:** Użycie callback w expects()->with() do weryfikacji typu i wartości dat.

### 12.2. Problem: Statyczna metoda InspectionResponseDto::fromEntity()
**Rozwiązanie:** Testowanie pośrednie - weryfikacja, że wynik zawiera poprawne DTOs.

### 12.3. Problem: Złożone asercje dla metadanych paginacji
**Rozwiązanie:** Tworzenie dedykowanych metod pomocniczych do weryfikacji struktur DTO.

## 13. Dokumentacja dla developerów

### 13.1. Jak dodać nowy test

1. Zidentyfikuj nowy scenariusz do przetestowania
2. Dodaj opis w tym dokumencie (sekcja 3)
3. Stwórz metodę testową z prefiksem `test`
4. Użyj mocków dla zależności
5. Dodaj odpowiednie asercje
6. Upewnij się, że test przechodzi
7. Zaktualizuj dokumentację

### 13.2. Dobre praktyki

- Jeden test = jeden scenariusz
- Nazwy testów w formacie: `testMetodaScenariuszOczekiwanyRezultat`
- Używaj sekcji Arrange-Act-Assert (AAA) w kodzie testów
- Zawsze mockuj wszystkie zależności zewnętrzne
- Nie testuj implementacji, tylko zachowanie (behavior)

## 14. Podsumowanie

Plan testów obejmuje 18 głównych scenariuszy testowych dla metody `InspectionService::getInspections()`. Testy zapewnią:

- ✅ Pokrycie wszystkich ścieżek logicznych
- ✅ Walidację danych wejściowych
- ✅ Poprawność transformacji danych
- ✅ Obsługę błędów i wyjątków
- ✅ Weryfikację integracji z zależnościami (poprzez mocki)
- ✅ Testowanie przypadków brzegowych

Implementacja wszystkich testów zgodnie z tym planem zapewni wysoką jakość i niezawodność metody `getInspections()`.
