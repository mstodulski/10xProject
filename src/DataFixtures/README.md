# Data Fixtures Documentation

## Przegląd

System fixtures dla aplikacji demo do zarządzania oględzinami pojazdów powypadkowych.

## Fixtures zostały zaprojektowane do:
- Wczytywania co środę jako zestaw startowy dla strony demo
- Działania we wszystkich środowiskach (dev, test, prod)
- Generowania realistycznych danych z polskim kontekstem

## Struktura Fixtures

### 1. PolishDataGenerator
Klasa pomocnicza generująca losowe dane w polskim kontekście:
- Imiona i nazwiska (np. "Jan Kowalski", "Anna Nowak")
- Numery telefonów w formacie polskim (+48 XXX XXX XXX)
- Tablice rejestracyjne (np. "WA12345", "KR1234A")
- Marki i modele samochodów popularnych w Polsce

### 2. UserFixtures
**Liczba użytkowników:** 5
- 4 konsultantów (`konsultant1`, `konsultant2`, `konsultant3`, `konsultant4`)
- 1 inspektor (`inspektor1`)

**Dane logowania:**
- Login: `konsultant1` / `konsultant2` / `konsultant3` / `konsultant4` / `inspektor1`
- Hasło: `test` (dla wszystkich użytkowników)
- Imiona i nazwiska: losowe polskie

**Uprawnienia:**
- Konsultanci: `ROLE_CONSULTANT` (pełny dostęp do zarządzania)
- Inspektor: `ROLE_INSPECTOR` (tylko odczyt)

### 3. InspectionFixtures
**Zakres czasowy:**
- **-12 dni** od daty wczytania fixtures (przeszłość)
- **+7 dni** od daty wczytania fixtures (przyszłość)
- **UWAGA:** Fixtures założone są na wczytywanie w środy

**Parametry oględzin:**
- Tylko dni robocze (poniedziałek-piątek)
- 2-5 oględzin dziennie
- Godziny: 07:00 - 15:30 (sloty co 15 minut)
- Czas trwania: 30 minut
- Bufor między oględzinami: 15 minut
- Dane: losowe polskie (pojazdy, klienci, telefony)
- Równomierne rozłożenie między 4 konsultantów

## Użycie

### Wczytanie fixtures (czyści bazę danych)
```bash
php bin/console doctrine:fixtures:load
```

### Wczytanie fixtures z potwierdzeniem
```bash
php bin/console doctrine:fixtures:load -n
```

### Wczytanie fixtures w środowisku produkcyjnym
```bash
php bin/console doctrine:fixtures:load --env=prod -n
```

## Przykładowe dane

### Użytkownicy
| Login | Hasło | Rola | Imię i nazwisko |
|-------|-------|------|-----------------|
| konsultant1 | test | ROLE_CONSULTANT | (losowe) |
| konsultant2 | test | ROLE_CONSULTANT | (losowe) |
| konsultant3 | test | ROLE_CONSULTANT | (losowe) |
| konsultant4 | test | ROLE_CONSULTANT | (losowe) |
| inspektor1 | test | ROLE_INSPECTOR | (losowe) |

### Oględziny (przykład)
- **Data:** 2025-10-08 (środa)
- **Godzina:** 10:15 - 10:45
- **Pojazd:** Toyota Corolla (WA12345)
- **Klient:** Jan Kowalski (+48 512 345 678)
- **Utworzone przez:** konsultant1

## Harmonogram odświeżania (środy)

Fixtures zaprojektowane są do wczytywania co środę:

```bash
# Przykład: każda środa o 00:00
0 0 * * 3 cd /var/www && php bin/console doctrine:fixtures:load --env=prod -n
```

## Uwagi techniczne

1. **Zależności:** InspectionFixtures wymaga UserFixtures (DependentFixtureInterface)
2. **Czas relatywny:** Wszystkie daty są obliczane względem `new DateTimeImmutable()`
3. **Walidacja:** Fixtures generują tylko poprawne dane (bez kolizji, tylko dni robocze)
4. **Performance:** ~60-95 rekordów oględzin (2-5 dziennie × 15 dni roboczych)

## Rozwiązywanie problemów

### Błąd: Foreign key constraint
```bash
# Upewnij się że baza jest pusta lub użyj --purge-with-truncate
php bin/console doctrine:fixtures:load --purge-with-truncate
```

### Błąd: Duplicate entry
```bash
# Wyczyść bazę przed wczytaniem
php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create
php bin/console doctrine:fixtures:load -n
```

## Do zrobienia w przyszłości

- [ ] Dodanie fixtures dla testów jednostkowych (specyficzne edge cases)
- [ ] Grupowanie fixtures (--group demo, --group test)
- [ ] Fixtures dla nieaktywnych użytkowników
- [ ] Fixtures z przykładami kolizji (dla testowania walidacji)
