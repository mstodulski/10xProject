# Konfiguracja GitHub Secrets dla CI/CD

## Instrukcja krok po kroku

### 1. Przejdź do ustawień repozytorium

1. Otwórz swoje repozytorium na GitHub.com
2. Kliknij w zakładkę **Settings** (Ustawienia)
3. W lewym menu znajdź sekcję **Secrets and variables** → **Actions**
4. Kliknij przycisk **New repository secret**

### 2. Dodaj wymagane sekrety

Musisz dodać **6 sekretów**. Dla każdego z nich:
- Kliknij **New repository secret**
- Wpisz nazwę (Name)
- Wpisz wartość (Secret)
- Kliknij **Add secret**

#### Secret 1: APP_SECRET
```
Name: APP_SECRET
Secret: 3de380e0aabb72ad26902e2635234c4b
```
**Opis**: Klucz bezpieczeństwa Symfony używany do szyfrowania sesji i tokenów CSRF.

---

#### Secret 2: DATABASE_URL
```
Name: DATABASE_URL
Secret: mysql://test_user:test_password@mariadb12.iq.pl:3306/zaliczenie
```
**Opis**: URL połączenia z bazą danych produkcyjną.

⚠️ **UWAGA**: Podane dane są testowe! Przed deploymentem na produkcję **MUSISZ** zmienić to na prawdziwe dane dostępowe do bazy:
- Zamień `test_user` na prawdziwego użytkownika MySQL
- Zamień `test_password` na prawdziwe hasło
- Zweryfikuj host `mariadb12.iq.pl` i nazwę bazy `zaliczenie`

---

#### Secret 3: FTP_HOST
```
Name: FTP_HOST
Secret: ftp-iq.pl
```
**Opis**: Adres serwera FTP.

---

#### Secret 4: FTP_USERNAME
```
Name: FTP_USERNAME
Secret: user_ftp
```
**Opis**: Nazwa użytkownika FTP.

⚠️ **UWAGA**: Zamień `user_ftp` na prawdziwą nazwę użytkownika FTP!

---

#### Secret 5: DEFAULT_URI
```
Name: DEFAULT_URI
Secret: https://your-production-domain.com
```
**Opis**: URL aplikacji używany do generowania linków w komendach CLI (np. w e-mailach, exportach).

⚠️ **UWAGA**: Zamień `https://your-production-domain.com` na prawdziwy URL Twojej produkcyjnej aplikacji!
- Przykład: `https://inspekcje.twojadomena.pl`
- Musi zawierać protokół (`https://` lub `http://`)

---

#### Secret 6: FTP_PASSWORD
```
Name: FTP_PASSWORD
Secret: password_ftp
```
**Opis**: Hasło do konta FTP.

⚠️ **UWAGA**: Zamień `password_ftp` na prawdziwe hasło FTP!

---

### 3. Weryfikacja

Po dodaniu wszystkich sekretów powinieneś zobaczyć listę:
- ✅ APP_SECRET
- ✅ DATABASE_URL
- ✅ DEFAULT_URI
- ✅ FTP_HOST
- ✅ FTP_USERNAME
- ✅ FTP_PASSWORD

**6 sekretów w sumie.**

### Zmienne ustawiane automatycznie

Następujące zmienne są **automatycznie konfigurowane** w workflow i nie wymagają dodawania jako sekrety:

- **MESSENGER_TRANSPORT_DSN**: Ustawiony na `doctrine://default` (przechowywanie wiadomości asynchronicznych w bazie danych)
- **MAILER_DSN**: Ustawiony na `null://null` (nie wysyłamy maili z tego systemu)

---

## Jak uruchomić deployment?

### Krok 1: Przejdź do Actions
1. Otwórz repozytorium na GitHub
2. Kliknij zakładkę **Actions**
3. W lewym menu znajdź workflow **"Deploy to Production"**

### Krok 2: Uruchom manualnie
1. Kliknij na workflow **"Deploy to Production"**
2. Po prawej stronie zobaczysz przycisk **"Run workflow"**
3. Upewnij się, że wybrany jest branch **main**
4. Kliknij **"Run workflow"**

### Krok 3: Monitoruj przebieg
1. Workflow pojawi się na liście z żółtym kolorem (w trakcie)
2. Kliknij na nazwę uruchomienia, aby zobaczyć szczegóły
3. Zobaczysz poszczególne kroki (steps):
   - ✅ Checkout code
   - ✅ Setup PHP 8.4
   - ✅ Setup Node.js 22
   - ✅ Install Composer dependencies
   - ✅ Install npm dependencies
   - ✅ Create .env file for testing
   - ✅ Create database schema
   - ✅ Load fixtures
   - ✅ Run PHPUnit tests
   - ✅ Create .env file for production
   - ✅ Generate FOS JS Routing
   - ✅ Generate Bazinga JS Translation
   - ✅ Build frontend assets
   - ✅ Install Symfony assets
   - ✅ Clear Symfony cache
   - ✅ Warmup Symfony cache
   - ✅ Prepare deployment package
   - ✅ Deploy to production via FTP
   - ✅ Deployment complete

### Krok 4: Sprawdź status
- **Zielony checkmark (✓)** = Deployment zakończony sukcesem
- **Czerwony krzyżyk (✗)** = Wystąpił błąd
  - Kliknij na czerwony krok, aby zobaczyć logi błędu
  - Napraw problem i uruchom ponownie

---

## Co się dzieje podczas deploymentu?

### Faza 1: Przygotowanie środowiska (2-3 min)
- Instalacja PHP 8.4 z rozszerzeniami
- Instalacja Node.js 22 i npm 10.9
- Cache dependencies dla szybszych kolejnych deploymentów

### Faza 2: Instalacja zależności (3-5 min)
- `composer install` - instalacja pakietów PHP z `composer.lock`
- `npm ci` - instalacja pakietów JavaScript z `package-lock.json`

### Faza 3: Testy (2-4 min)
- Uruchomienie MySQL service
- Utworzenie schematu bazy danych
- Załadowanie fixtures
- Uruchomienie testów PHPUnit

⚠️ **Jeśli testy nie przejdą, deployment się zatrzyma!**

### Faza 4: Build aplikacji (3-5 min)
- Generowanie pliku `.env` produkcyjnego
- Generowanie routingu JS (FOS JS Routing)
- Generowanie tłumaczeń JS (Bazinga)
- Build frontendu przez Webpack Encore (`npm run build`)
- Instalacja assetów Symfony
- Czyszczenie i rozgrzanie cache Symfony

### Faza 5: Deployment na FTP (5-15 min, zależnie od prędkości FTP)
- Przygotowanie paczki deployment
- Upload przez FTP na serwer produkcyjny
- Pliki wykluczone z uploadu:
  - `var/**` - katalog var (cache i logi) nie jest nadpisywany!
  - `.git/**` - pliki gita
  - `tests/**` - testy
  - `node_modules/**` - zależności node

### Pliki wysłane na serwer:
- ✅ `vendor/` - zależności PHP
- ✅ `src/` - kod źródłowy aplikacji
- ✅ `config/` - konfiguracja
- ✅ `templates/` - szablony Twig
- ✅ `translations/` - tłumaczenia
- ✅ `bin/` - pliki binarne Symfony
- ✅ `public/build/` - skompilowane assety frontendu
- ✅ `public/bundles/` - assety z bundli Symfony
- ✅ `public/js/` - routing JS (fos_js_routes.json)
- ✅ `public/index.php` - entry point
- ✅ `public/.htaccess` - konfiguracja Apache
- ✅ `.env` - plik konfiguracyjny środowiska

### Katalogi NIE wysyłane (zarządzane na serwerze):
- ❌ `var/` - cache i logi są generowane i zarządzane przez Symfony na serwerze produkcyjnym

---

## Troubleshooting - najczęstsze problemy

### Problem 1: "Secrets not found"
**Rozwiązanie**: Sprawdź czy wszystkie 6 sekretów zostało poprawnie dodanych w Settings → Secrets and variables → Actions

### Problem 2: Testy PHPUnit nie przechodzą
**Rozwiązanie**:
- Kliknij na czerwony krok "Run PHPUnit tests"
- Przeczytaj logi błędów
- Napraw testy lokalnie i wypchnij poprawkę
- Uruchom workflow ponownie

### Problem 3: FTP connection failed
**Rozwiązanie**:
- Sprawdź czy dane FTP są poprawne (FTP_HOST, FTP_USERNAME, FTP_PASSWORD)
- Sprawdź czy serwer FTP jest dostępny
- Sprawdź czy port 21 nie jest zablokowany

### Problem 4: "npm run build" fails
**Rozwiązanie**:
- Sprawdź logi kroku "Build frontend assets"
- Upewnij się że `package.json` i `package-lock.json` są poprawne
- Napraw błędy lokalnie i wypchnij poprawkę

### Problem 5: Cache Symfony errors
**Rozwiązanie**:
- Na serwerze produkcyjnym katalog `var/cache/` może być nieczytelny przez Apache
- Zaloguj się przez FTP i ustaw uprawnienia `chmod 755` dla `var/cache/` i `var/log/`
- Jeśli katalog `var/` nie istnieje na serwerze, utwórz go ręcznie:
  ```
  mkdir -p var/cache var/log
  chmod -R 755 var/
  ```
- Symfony automatycznie wygeneruje cache przy pierwszym uruchomieniu

---

## Bezpieczeństwo

⚠️ **NIGDY** nie commituj do repozytorium:
- Plików `.env` z prawdziwymi danymi produkcyjnymi
- Haseł FTP
- Haseł do bazy danych
- APP_SECRET

✅ Wszystkie wrażliwe dane powinny być przechowywane jako **GitHub Secrets**.

---

## Wsparcie

Jeśli masz problemy z konfiguracją CI/CD:
1. Sprawdź logi w zakładce Actions → kliknij na konkretny workflow run
2. Każdy krok ma swoje logi - rozwiń czerwone kroki aby zobaczyć błędy
3. Jeśli problem dotyczy FTP, sprawdź czy dane dostępowe są poprawne
4. Jeśli problem dotyczy testów, uruchom je lokalnie: `vendor/bin/phpunit`

---

**Powodzenia z deploymentem! 🚀**
