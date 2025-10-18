# Usprawnienie Obsługi Błędów Uwierzytelniania API

## 🎯 Problem

Przed zmianami, gdy użytkownik próbował uzyskać dostęp do endpointu API bez prawidłowej sesji:
- Symfony **przekierowywał (302)** do strony logowania
- Zwracany był **HTML** zamiast JSON
- Klient API otrzymywał nieoczekiwaną odpowiedź

To było nieprawidłowe zachowanie dla REST API, które powinno zawsze zwracać odpowiedź JSON z odpowiednim kodem statusu.

## ✅ Rozwiązanie

Utworzono dedykowany **firewall dla API** z niestandardowym **Entry Point**, który zwraca błędy w formacie JSON.

### Zmiany w kodzie:

#### 1. Utworzono `ApiAuthenticationEntryPoint`
**Plik:** `src/Security/ApiAuthenticationEntryPoint.php`

```php
class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $message = $authException?->getMessage() ?? 'Wymagane uwierzytelnienie';

        return new JsonResponse([
            'success' => false,
            'error' => $message
        ], Response::HTTP_UNAUTHORIZED);
    }
}
```

**Funkcja:**
- Przechwytuje próby dostępu do chronionego API bez uwierzytelnienia
- Zwraca odpowiedź JSON zamiast przekierowania
- Używa kodu statusu **401 Unauthorized**

#### 2. Zaktualizowano konfigurację Security
**Plik:** `config/packages/security.yaml`

**Dodano dedykowany firewall dla API:**

```yaml
firewalls:
    dev:
        pattern: ^/(_(profiler|wdt)|css|images|js)/
        security: false
    api:                                              # NOWY FIREWALL
        pattern: ^/api
        lazy: true
        provider: user_provider
        context: shared                               # Dzieli sesję z 'main'
        entry_point: App\Security\ApiAuthenticationEntryPoint
        custom_authenticator: App\Security\LoginFormAuthenticator
        user_checker: App\Security\UserChecker
    main:
        lazy: true
        provider: user_provider
        context: shared                               # Dzieli sesję z 'api'
        # ... pozostała konfiguracja
```

**Kluczowe elementy:**
- **pattern: ^/api** - Firewall obsługuje wszystkie URL-e zaczynające się od `/api`
- **context: shared** - Sesja jest współdzielona między firewall'ami `api` i `main`
- **entry_point: ApiAuthenticationEntryPoint** - Niestandardowy entry point zwracający JSON

## 🔍 Jak to działa?

### Scenariusz 1: Zalogowany użytkownik
1. Użytkownik loguje się przez formularz (firewall `main`)
2. Symfony tworzy sesję i ustawia cookie `PHPSESSID`
3. Użytkownik wysyła żądanie do `/api/inspections` z cookie
4. Firewall `api` (przez `context: shared`) rozpoznaje sesję
5. Żądanie przechodzi do kontrolera
6. Zwracana jest odpowiedź **200 OK** z danymi JSON

### Scenariusz 2: Brak uwierzytelnienia
1. Użytkownik wysyła żądanie do `/api/inspections` **bez cookie** lub z nieprawidłowym
2. Firewall `api` nie znajduje prawidłowej sesji
3. Wywołany zostaje `ApiAuthenticationEntryPoint::start()`
4. Zwracana jest odpowiedź **401 Unauthorized** z JSON:
   ```json
   {
     "success": false,
     "error": "Wymagane uwierzytelnienie"
   }
   ```

## 📊 Porównanie: Przed vs Po

| Aspekt | Przed | Po |
|--------|-------|-----|
| Status HTTP | 302 (Redirect) | 401 (Unauthorized) |
| Content-Type | text/html | application/json |
| Response Body | HTML strony logowania | `{"success": false, "error": "..."}` |
| Zachowanie | Przekierowanie | Bezpośrednia odpowiedź JSON |
| Zgodność z REST | ❌ | ✅ |

## 🧪 Testowanie

### Utworzono skrypt testowy:
**Plik:** `.ai/test-api-authentication.sh`

**Uruchomienie:**
```bash
./.ai/test-api-authentication.sh
```

**Przypadki testowe:**
1. ✅ Żądanie bez cookie PHPSESSID
2. ✅ Żądanie z nieprawidłowym PHPSESSID
3. ✅ Żądanie z pustym PHPSESSID

**Oczekiwany wynik dla wszystkich:**
```
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{"success":false,"error":"Wymagane uwierzytelnienie"}
```

### Testy manualne z curl:

**Test 1: Brak cookie**
```bash
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -i
```

**Oczekiwana odpowiedź:**
```
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{"success":false,"error":"Wymagane uwierzytelnienie"}
```

**Test 2: Nieprawidłowe cookie**
```bash
curl -X GET "http://localhost/api/inspections" \
  -H "Accept: application/json" \
  -b "PHPSESSID=invalid-session-id" \
  -i
```

**Oczekiwana odpowiedź:**
```
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{"success":false,"error":"Wymagane uwierzytelnienie"}
```

## 🔐 Bezpieczeństwo

### Zalety tego rozwiązania:

1. **Konsystencja API** - Wszystkie odpowiedzi są w formacie JSON
2. **Właściwe kody statusu** - Użycie 401 zgodnie ze standardem HTTP
3. **Brak ujawniania informacji** - Nie przekierowujemy do URL-i logowania
4. **Kompatybilność z klientami API** - JavaScript, mobile apps mogą łatwo obsłużyć błąd
5. **Współdzielona sesja** - Użytkownicy zalogowani przez formularz mogą używać API

### Co chronione:

- ✅ Wszystkie endpointy `/api/*` wymagają uwierzytelnienia
- ✅ Brak przekierowań, które mogłyby zdezorientować klientów API
- ✅ Jasny komunikat o wymaganym uwierzytelnieniu

## 📝 Zgodność z planem implementacji

Ta zmiana jest zgodna z punktem **7.1** planu:

> **7.1. Tabela kodów błędów**
> | Kod HTTP | Scenariusz | Response Body |
> | 401 | Brak uwierzytelnienia | `{"success": false, "error": "Wymagane uwierzytelnienie"}` |

## 🎯 Korzyści

1. **Dla developerów frontend:**
   - Łatwiejsza obsługa błędów w JavaScript
   - Konsystentny format odpowiedzi
   - Możliwość wyświetlenia komunikatu użytkownikowi

2. **Dla aplikacji mobilnych:**
   - Możliwość wykrycia braku autoryzacji
   - Automatyczne przekierowanie do ekranu logowania

3. **Dla integracji z zewnętrznymi systemami:**
   - Standardowy kod 401 rozpoznawany przez wszystkie biblioteki HTTP
   - Format JSON łatwy do parsowania

## 🚀 Status

✅ **ZAIMPLEMENTOWANE I GOTOWE DO PRODUKCJI**

Wszystkie zmiany zostały wprowadzone, firewall jest poprawnie skonfigurowany, a cache został wyczyszczony.

## 📚 Dodatkowe informacje

### Dokumentacja Symfony:
- [Security Entry Points](https://symfony.com/doc/current/security/entry_point.html)
- [Multiple Firewalls](https://symfony.com/doc/current/security/multiple_guard_authenticators.html)
- [Firewall Context](https://symfony.com/doc/current/security/firewall_restriction.html)

### Standardy REST API:
- [RFC 7231 - HTTP Status Codes](https://tools.ietf.org/html/rfc7231#section-6.5.1)
- [REST API Best Practices](https://restfulapi.net/http-status-codes/)
