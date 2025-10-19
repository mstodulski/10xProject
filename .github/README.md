# GitHub Configuration

Ten katalog zawiera konfigurację GitHub Actions CI/CD dla projektu.

## Pliki

### `workflows/deploy.yml`
Główny workflow CI/CD do testowania i deploymentu aplikacji na produkcję.

**Uruchamianie**: Manualnie przez zakładkę Actions w GitHub

**Co robi**:
1. Instaluje PHP 8.4 i Node.js 22
2. Instaluje zależności (Composer + npm)
3. Uruchamia testy PHPUnit na fixtures
4. Buduje frontend (Webpack Encore)
5. Generuje routing i translacje JS
6. Czyści cache Symfony
7. Wysyła pliki przez FTP na serwer produkcyjny

### `GITHUB_SECRETS_SETUP.md`
Szczegółowa instrukcja konfiguracji GitHub Secrets i użytkowania CI/CD.

**Przeczytaj przed pierwszym deploymentem!**

## Wymagane GitHub Secrets

W Settings → Secrets and variables → Actions dodaj:

1. `APP_SECRET` - Symfony secret key
2. `DATABASE_URL` - URL do bazy produkcyjnej
3. `FTP_HOST` - Host serwera FTP
4. `FTP_USERNAME` - Użytkownik FTP
5. `FTP_PASSWORD` - Hasło FTP

## Szybki start

1. Dodaj secrets w Settings → Secrets and variables → Actions
2. Przejdź do Actions → Deploy to Production
3. Kliknij "Run workflow" → wybierz branch `main` → "Run workflow"
4. Monitoruj przebieg w czasie rzeczywistym
5. Sprawdź czy wszystkie kroki się powiodły (zielony checkmark)

## Wsparcie

Szczegółowe informacje i troubleshooting w pliku `GITHUB_SECRETS_SETUP.md`.
