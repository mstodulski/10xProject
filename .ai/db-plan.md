# Schemat Bazy Danych - System Zarządzania Oględzinami Pojazdów Powypadkowych

## 1. Tabele

### 1.1 Tabela `users`

Przechowuje informacje o użytkownikach systemu (konsultanci i inspektorzy).

| Kolumna      | Typ        | Ograniczenia                  | Opis                                           |
|--------------|------------|-------------------------------|------------------------------------------------|
| id           | INT        | PRIMARY KEY, AUTO_INCREMENT   | Unikalny identyfikator użytkownika             |
| login        | VARCHAR(64)| NOT NULL, UNIQUE              | Login użytkownika (unikalny)                   |
| password     | VARCHAR(255)| NOT NULL                     | Zahaszowane hasło użytkownika                  |
| name         | VARCHAR(64)| NOT NULL                      | Imię i nazwisko użytkownika                    |
| role         | ENUM       | NOT NULL                      | Rola: 'consultant' lub 'inspector'             |
| is_active    | BOOLEAN    | NOT NULL, DEFAULT TRUE        | Status aktywności konta                        |
| created_at   | DATETIME   | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data utworzenia konta                    |

### 1.2 Tabela `inspections`

Przechowuje informacje o terminach oględzin pojazdów.

| Kolumna          | Typ          | Ograniczenia                   | Opis                                        |
|------------------|--------------|--------------------------------|---------------------------------------------|
| id               | INT          | PRIMARY KEY, AUTO_INCREMENT    | Unikalny identyfikator oględzin             |
| start_datetime   | DATETIME     | NOT NULL, INDEX                | Data i godzina rozpoczęcia oględzin         |
| end_datetime     | DATETIME     | NOT NULL, INDEX                | Data i godzina zakończenia oględzin         |
| vehicle_make     | VARCHAR(64)  | NOT NULL                       | Marka pojazdu                              |
| vehicle_model    | VARCHAR(64)  | NOT NULL                       | Model pojazdu                               |
| license_plate    | VARCHAR(20)  | NOT NULL                       | Numer rejestracyjny pojazdu                |
| client_name      | VARCHAR(64)  | NOT NULL                       | Imię i nazwisko klienta                    |
| phone_number     | VARCHAR(20)  | NOT NULL                       | Numer telefonu klienta                     |
| created_by_user_id | INT        | NOT NULL, INDEX, FOREIGN KEY   | ID konsultanta, który utworzył termin       |
| created_at       | DATETIME     | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Data utworzenia rekordu oględzin      |

## 2. Relacje między tabelami

### 2.1 `inspections` do `users`

- **Relacja:** Wiele do jednego (Many-to-One)
- **Opis:** Każde oględziny są utworzone przez jednego użytkownika (konsultanta). Jeden konsultant może utworzyć wiele terminów oględzin.
- **Implementacja:** Klucz obcy `created_by_user_id` w tabeli `inspections` odnosi się do `id` w tabeli `users`.

```sql
FOREIGN KEY (created_by_user_id) REFERENCES users(id)
```

## 3. Indeksy

### 3.1 Tabela `users`
- Indeks PRIMARY na kolumnie `id`
- Indeks UNIQUE na kolumnie `login`

### 3.2 Tabela `inspections`
- Indeks PRIMARY na kolumnie `id`
- Indeks na kolumnie `start_datetime` - optymalizacja wyszukiwania terminów na dany dzień/tydzień
- Indeks na kolumnie `end_datetime` - optymalizacja sprawdzania kolizji terminów
- Indeks na kolumnie `created_by_user_id` - optymalizacja wyszukiwania terminów utworzonych przez danego konsultanta

## 4. Ograniczenia i zasady biznesowe

Większość zasad biznesowych będzie implementowana na poziomie aplikacji (Symfony), a nie na poziomie bazy danych:

1. Walidacja terminów oględzin (godziny pracy, nie weekendy, maksymalnie 2 tygodnie do przodu)
2. Sprawdzanie kolizji terminów z uwzględnieniem 15-minutowych przerw
3. Walidacja numeru telefonu (minimum 8 znaków)
4. Automatyczne ustawianie `end_datetime` na 30 minut po `start_datetime`
5. Walidacja czasu rozpoczęcia (pełna godzina lub 15, 30, 45 minut po pełnej godzinie)

## 5. Przykładowe zapytania SQL

### 5.1 Pobranie wszystkich terminów oględzin na dany dzień

```sql
SELECT i.*, u.name AS consultant_name
FROM inspections i
JOIN users u ON i.created_by_user_id = u.id
WHERE DATE(i.start_datetime) = '2025-10-15'
ORDER BY i.start_datetime;
```

### 5.2 Sprawdzenie, czy dany termin jest dostępny (nie koliduje z istniejącymi)

```sql
SELECT COUNT(*) AS collision_count
FROM inspections
WHERE 
    -- Sprawdzenie, czy nowy termin nie nakłada się z istniejącymi
    -- z uwzględnieniem 15-minutowych przerw
    (
        -- Nowy termin zaczyna się podczas trwającego terminu
        ('2025-10-15 10:00:00' BETWEEN DATE_SUB(start_datetime, INTERVAL 15 MINUTE) AND DATE_ADD(end_datetime, INTERVAL 15 MINUTE))
        OR
        -- Nowy termin kończy się podczas trwającego terminu
        ('2025-10-15 10:30:00' BETWEEN DATE_SUB(start_datetime, INTERVAL 15 MINUTE) AND DATE_ADD(end_datetime, INTERVAL 15 MINUTE))
        OR
        -- Istniejący termin zawiera się całkowicie w nowym terminie
        (start_datetime BETWEEN '2025-10-15 10:00:00' AND '2025-10-15 10:30:00')
        OR 
        (end_datetime BETWEEN '2025-10-15 10:00:00' AND '2025-10-15 10:30:00')
    );
```

### 5.3 Pobranie wszystkich aktywnych użytkowników

```sql
SELECT *
FROM users
WHERE is_active = TRUE
ORDER BY name;
```

### 5.4 Pobranie wszystkich terminów utworzonych przez danego konsultanta

```sql
SELECT i.*, u.name AS consultant_name
FROM inspections i
JOIN users u ON i.created_by_user_id = u.id
WHERE i.created_by_user_id = 1
ORDER BY i.start_datetime DESC;
```

## 6. Uwagi implementacyjne

1. **Zarządzanie migracjami:** Wykorzystanie Doctrine ORM z Symfony do automatycznego zarządzania schematem bazy danych i migracjami.

2. **Bezpieczeństwo:**
    - Hasła użytkowników będą hashowane przy użyciu algorytmu bcrypt w warstwie aplikacji
    - Dostęp do bazy danych będzie ograniczony tylko do niezbędnych uprawnień
    - Parametryzowane zapytania SQL w celu zapobiegania atakom SQL Injection

3. **Wydajność:**
    - Indeksy na najczęściej przeszukiwanych kolumnach
    - Możliwość partycjonowania tabeli `inspections` po kolumnie `start_datetime` w przypadku wzrostu do dużych rozmiarów

4. **Skalowalność:**
    - Schemat jest przygotowany na ewentualny przyszły rozwój (np. dodanie większej liczby inspektorów)
    - W przyszłości możliwe rozszerzenie o osobne tabele dla klientów i pojazdów, z relacjami do tabeli `inspections`

5. **Zachowanie prostoty dla MVP:**
    - Minimalistyczny schemat zgodny z wymaganiami MVP
    - Brak zbędnych tabel i relacji, które nie są konieczne na tym etapie

## 7. Decyzje projektowe

1. **Brak osobnych tabel dla pojazdów i klientów:**
   Na etapie MVP, zgodnie z notatkami z sesji planowania, dane pojazdów i klientów będą przechowywane bezpośrednio w tabeli `inspections`. To upraszcza schemat i jest wystarczające dla obecnych potrzeb.

2. **Brak osobnej tabeli dla dni wolnych:**
   Weekendy są zawsze wolne (zgodnie z PRD), więc nie ma potrzeby tworzenia osobnej tabeli. Ta logika będzie implementowana na poziomie aplikacji.

3. **Relacja z inspektorem:**
   System zakłada jednego inspektora (jako rolę), więc nie ma potrzeby osobnej relacji między oględzinami a inspektorem.

4. **Długości pól:**
   Maksymalne długości pól tekstowych (VARCHAR) zostały ustawione na 64 znaki zgodnie z limitem określonym w PRD.

5. **Przechowywanie informacji o twórcy:**
   System rejestruje, który konsultant utworzył dany termin oględzin poprzez pole `created_by_user_id`.
