# Dokument wymagań produktu (PRD) - System Zarządzania Oględzinami Pojazdów Powypadkowych

## 1. Przegląd produktu

System Zarządzania Oględzinami Pojazdów Powypadkowych to aplikacja webowa służąca do efektywnego zarządzania harmonogramem oględzin pojazdów powypadkowych. System umożliwia konsultantom rezerwowanie terminów oględzin dla klientów, a inspektorowi przeglądanie swojego harmonogramu, eliminując problem nakładających się wizyt.

### 1.1. Cel biznesowy
Celem projektu jest wyeliminowanie kolizji terminów oględzin pojazdów poprzez centralizację zarządzania kalendarzem i synchronizację dostępności inspektora między wszystkimi konsultantami.

### 1.2. Główni interesariusze
- Konsultanci - pracownicy serwisu przyjmujący zgłoszenia od klientów
- Inspektor - osoba przeprowadzająca oględziny pojazdów
- Klienci - osoby umawiające się na oględziny pojazdów powypadkowych

### 1.3. Architektura systemu
- Frontend: Bootstrap + JavaScript (FullCalendar.js)
- Backend: Symfony 7.3 + MySQL
- Hosting: chmura
- Deployment: Git + CI/CD z ręcznym triggerem
- Mobile first design

## 2. Problem użytkownika

### 2.1. Obecna sytuacja
Obecnie klienci serwisu samochodowego umawiają się na oględziny pojazdów powypadkowych poprzez różne kanały komunikacji (telefon, email), kontaktując się z różnymi konsultantami. W serwisie jest tylko jedna osoba (inspektor), która może przeprowadzać oględziny. Konsultanci nie mają możliwości sprawdzenia, czy dany termin jest już zajęty przez innego klienta, co prowadzi do licznych kolizji terminów.

### 2.2. Skala problemu
Problem dotyczy około 10 kolizji terminów tygodniowo. W konsekwencji inspektor często ma zaplanowane dwie lub więcej oględzin w tym samym czasie, co jest niemożliwe do zrealizowania.

### 2.3. Wpływ na biznes
- Niezadowoleni klienci zmuszeni do czekania lub przekładania terminów
- Stres i frustracja inspektora i konsultantów
- Nieefektywne wykorzystanie czasu pracy inspektora
- Uszczerbek na reputacji serwisu

### 2.4. Potrzeby użytkowników
- Konsultanci potrzebują szybkiego i niezawodnego sposobu sprawdzania dostępności terminów
- Inspektor potrzebuje przejrzystego harmonogramu pracy bez nakładających się terminów
- Wszyscy pracownicy potrzebują wspólnego, scentralizowanego systemu do zarządzania terminami oględzin

## 3. Wymagania funkcjonalne

### 3.1. Zarządzanie kalendarzem

#### 3.1.1. Widok kalendarza
- Domyślny widok tygodniowy (poniedziałek-niedziela)
- Widok dzienny na urządzeniach mobilnych (< 768px)
- Widoczne tylko godziny pracy (07:00-16:00)
- Sloty czasowe co 15 minut
- Wyraźnie oznaczone i zablokowane weekendy (soboty i niedziele)
- Przycisk "Dzisiaj" do szybkiego przejścia do bieżącego tygodnia
- Nawigacja do poprzedniego/następnego tygodnia
- Automatyczne odświeżanie danych co minutę w tle

#### 3.1.2. Tworzenie oględzin
- Tworzenie eventu przez kliknięcie w pusty slot kalendarza
- Otwarcie formularza z automatycznie wypełnioną datą i godziną
- Wymagane pola: marka pojazdu, model pojazdu, numer rejestracyjny, nazwa klienta, numer telefonu
- Automatyczne zapisanie informacji o konsultancie tworzącym event
- Walidacja terminu (godziny pracy, nie weekend, przyszła data, max 2 tygodnie do przodu)
- Sprawdzanie kolizji z istniejącymi terminami (uwzględniając 15-minutowe przerwy)

#### 3.1.3. Edycja oględzin
- Edycja przez kliknięcie na istniejący event
- Możliwość zmiany wszystkich danych (termin, dane pojazdu, dane klienta)
- Ta sama walidacja jak przy tworzeniu
- Blokada edycji eventów z przeszłości (tryb tylko do odczytu)

#### 3.1.4. Usuwanie oględzin
- Możliwość usunięcia przyszłych eventów
- Blokada usuwania eventów z przeszłości
- Brak ograniczeń dla konsultantów (mogą usuwać wszystkie eventy, nie tylko swoje)

### 3.2. System użytkowników

#### 3.2.1. Role użytkowników
- Konsultant: pełne uprawnienia do zarządzania eventami i użytkownikami
- Inspektor: tylko odczyt kalendarza

#### 3.2.2. Zarządzanie użytkownikami
- Osobny moduł dostępny dla konsultantów
- Lista wszystkich użytkowników (aktywni + nieaktywni)
- Tworzenie nowego użytkownika (login, hasło, nazwa, rola)
- Dezaktywacja/aktywacja istniejących użytkowników
- Oznaczenie nieaktywnych użytkowników na liście

#### 3.2.3. Autentykacja
- Logowanie przez login i hasło
- Sesja trwająca do wylogowania (domyślne ustawienia Symfony)
- Po wylogowaniu przekierowanie na stronę logowania

### 3.3. Interfejs użytkownika

#### 3.3.1. Nawigacja
- Menu górne na desktop, hamburger na mobile
- Pozycje menu: Kalendarz, Użytkownicy (tylko dla konsultantów)
- Informacje o zalogowanym użytkowniku i przycisk wylogowania w nagłówku

#### 3.3.2. Responsywność
- Mobile first design
- Widok dzienny na telefonach (< 768px)
- Widok tygodniowy na tabletach i desktopach (≥ 768px)

#### 3.3.3. Komunikaty i powiadomienia
- Toast z potwierdzeniem po zapisie eventu
- Komunikaty walidacyjne przy błędach
- Komunikaty przy próbie edycji usuniętego eventu lub eventu z przeszłości

## 4. Granice produktu

### 4.1. Co wchodzi w zakres MVP
- System zarządzania eventami oględzin (tworzenie, edycja, usuwanie, przeglądanie)
- Prosty system kont użytkowników z dwiema rolami (konsultant, inspektor)
- Graficzna prezentacja kalendarza z zajętymi terminami
- Walidacja terminów zgodnie z regułami biznesowymi
- Responsywny interfejs użytkownika

### 4.2. Co NIE wchodzi w zakres MVP
- Powiadomienia mailowe lub SMS dla inspektora o zaplanowaniu oględzin
- Dołączanie do eventu raportu lub zdjęć z oględzin
- Oznaczanie eventu jako wykonanego
- Obsługa więcej niż jednego inspektora
- Autentykacja dwuskładnikowa
- Możliwość zmiany hasła przez użytkownika
- Automatyczne proponowanie wolnych terminów
- Equal conflicts resolution przy jednoczesnej edycji

### 4.3. Założenia techniczne
- Czas trwania oględzin: 30 minut
- Minimalny czas między oględzinami na przygotowanie dokumentacji: 15 minut
- Godziny pracy serwisu: od 07:00 do 16:00
- Możliwe godziny rozpoczęcia oględzin: co 15 minut od pełnej godziny
- Brak możliwości umówienia oględzin na sobotę i niedzielę
- Możliwość rezerwacji terminów maksymalnie 2 tygodnie do przodu

### 4.4. Limity MVP
- Maksymalny czas wdrożenia: 2 tygodnie
- Brak monitoringu i logowania błędów na etapie MVP
- Walidacja numerów telefonu: minimum 8 znaków
- Wszystkie pola tekstowe: maksimum 64 znaki

## 5. Historyjki użytkowników

### US-001: Logowanie do systemu

**Jako** użytkownik systemu (konsultant lub inspektor)  
**Chcę** zalogować się do systemu  
**Aby** uzyskać dostęp do funkcjonalności zgodnych z moją rolą

**Kryteria akceptacji:**
1. System wyświetla stronę logowania z polami: login, hasło oraz przyciskiem "Zaloguj"
2. Po wprowadzeniu poprawnych danych użytkownik zostaje zalogowany i przekierowany do widoku kalendarza
3. Po wprowadzeniu niepoprawnych danych system wyświetla generyczny komunikat o błędzie
4. Nieaktywni użytkownicy nie mogą się zalogować
5. Sesja użytkownika trwa do momentu wylogowania

### US-002: Wylogowanie z systemu

**Jako** zalogowany użytkownik  
**Chcę** wylogować się z systemu  
**Aby** zakończyć sesję i zabezpieczyć dostęp do moich danych

**Kryteria akceptacji:**
1. W nagłówku aplikacji widoczny jest przycisk "Wyloguj"
2. Po kliknięciu przycisku użytkownik zostaje wylogowany
3. Po wylogowaniu użytkownik jest przekierowywany na stronę logowania
4. Po wylogowaniu nie ma możliwości powrotu do poprzednich widoków bez ponownego logowania

### US-003: Przeglądanie kalendarza oględzin

**Jako** zalogowany użytkownik (konsultant lub inspektor)  
**Chcę** przeglądać kalendarz oględzin  
**Aby** zobaczyć zaplanowane terminy

**Kryteria akceptacji:**
1. Po zalogowaniu użytkownik widzi kalendarz z bieżącym tygodniem
2. Kalendarz pokazuje godziny pracy od 07:00 do 16:00
3. Widoczne są wszystkie zaplanowane oględziny w danym tygodniu
4. Każdy event na kalendarzu pokazuje: markę i model pojazdu, numer rejestracyjny, nazwę klienta i numer telefonu
5. Użytkownik może nawigować do poprzedniego/następnego tygodnia
6. Dostępny jest przycisk "Dzisiaj" do powrotu do bieżącego tygodnia
7. Na urządzeniach mobilnych (<768px) wyświetlany jest widok dzienny
8. Na tabletach i desktopach (≥768px) wyświetlany jest widok tygodniowy
9. Weekendy (sobota, niedziela) są wyraźnie oznaczone i zablokowane
10. Kalendarz automatycznie odświeża się co minutę bez resetowania widoku

### US-004: Tworzenie nowego terminu oględzin

**Jako** konsultant  
**Chcę** utworzyć nowy termin oględzin  
**Aby** umówić klienta na oględziny pojazdu

**Kryteria akceptacji:**
1. Konsultant może kliknąć na pusty slot w kalendarzu
2. Po kliknięciu otwiera się modal z formularzem
3. Data i godzina są automatycznie wypełnione na podstawie klikniętego slotu
4. Formularz zawiera pola: marka pojazdu, model pojazdu, numer rejestracyjny, nazwa klienta, numer telefonu
5. Wszystkie pola formularza są obowiązkowe
6. Po kliknięciu "Utwórz oględziny" system waliduje:
    - Czy termin jest w przyszłości
    - Czy termin jest w godzinach pracy (07:00-16:00)
    - Czy termin nie jest w weekend
    - Czy termin zaczyna się o pełnej godzinie lub 15, 30, 45 minut po pełnej godzinie
    - Czy nie koliduje z innymi terminami (uwzględniając 15-minutowe przerwy)
    - Czy nie jest dalej niż 2 tygodnie w przyszłość
7. Po pomyślnej walidacji termin zostaje zapisany
8. System wyświetla komunikat potwierdzający zapisanie terminu
9. Kalendarz odświeża się, pokazując nowo utworzony termin
10. System zapisuje informację o konsultancie, który utworzył termin

### US-005: Edycja terminu oględzin

**Jako** konsultant  
**Chcę** edytować istniejący termin oględzin  
**Aby** zaktualizować dane lub zmienić termin

**Kryteria akceptacji:**
1. Konsultant może kliknąć na istniejący termin w kalendarzu
2. Po kliknięciu otwiera się modal z formularzem wypełnionym danymi eventu
3. Konsultant może zmienić wszystkie pola: datę, godzinę, markę i model pojazdu, numer rejestracyjny, nazwę klienta, numer telefonu
4. Walidacja jest taka sama jak przy tworzeniu terminu
5. Po kliknięciu "Zapisz zmiany" i pomyślnej walidacji zmiany zostają zapisane
6. System wyświetla komunikat potwierdzający zapisanie zmian
7. Kalendarz odświeża się, pokazując zaktualizowany termin
8. Jeśli termin jest z przeszłości, formularz jest w trybie tylko do odczytu i nie można zapisać zmian

### US-006: Usuwanie terminu oględzin

**Jako** konsultant  
**Chcę** usunąć istniejący termin oględzin  
**Aby** anulować umówione oględziny

**Kryteria akceptacji:**
1. Konsultant może kliknąć na istniejący przyszły termin w kalendarzu
2. W modalu edycji dostępny jest przycisk "Usuń"
3. Po kliknięciu "Usuń" termin zostaje usunięty
4. System wyświetla komunikat potwierdzający usunięcie terminu
5. Kalendarz odświeża się, usunięty termin znika
6. Dla terminów z przeszłości opcja usunięcia nie jest dostępna

### US-007: Przeglądanie szczegółów terminu

**Jako** zalogowany użytkownik (konsultant lub inspektor)  
**Chcę** zobaczyć szczegóły terminu oględzin  
**Aby** poznać wszystkie informacje o umówionych oględzinach

**Kryteria akceptacji:**
1. Użytkownik może kliknąć na termin w kalendarzu
2. Po kliknięciu otwiera się modal z wszystkimi danymi terminu
3. Dla konsultanta:
    - Przyszłe terminy otwierają się w trybie edycji
    - Terminy z przeszłości otwierają się w trybie tylko do odczytu
4. Dla inspektora wszystkie terminy otwierają się w trybie tylko do odczytu
5. W trybie tylko do odczytu dostępny jest tylko przycisk "Zamknij"

### US-008: Zarządzanie użytkownikami

**Jako** konsultant  
**Chcę** zarządzać kontami użytkowników  
**Aby** kontrolować dostęp do systemu

**Kryteria akceptacji:**
1. W menu dostępna jest opcja "Użytkownicy" (tylko dla konsultantów)
2. Po kliknięciu użytkownik widzi listę wszystkich użytkowników
3. Lista pokazuje: login, nazwę, rolę i status (aktywny/nieaktywny)
4. Nieaktywni użytkownicy są wizualnie oznaczeni
5. Dostępny jest przycisk "Dodaj użytkownika"
6. Formularz dodawania zawiera pola: login, hasło, nazwa, rola (konsultant/inspektor)
7. Można dezaktywować użytkownika (przycisk "Dezaktywuj")
8. Można aktywować nieaktywnego użytkownika (przycisk "Aktywuj")
9. Nie można zalogować się na konto nieaktywnego użytkownika

### US-009: Tworzenie nowego użytkownika

**Jako** konsultant  
**Chcę** utworzyć nowego użytkownika  
**Aby** dać dostęp do systemu nowemu pracownikowi

**Kryteria akceptacji:**
1. Na stronie zarządzania użytkownikami dostępny jest przycisk "Dodaj użytkownika"
2. Po kliknięciu otwiera się formularz z polami: login, hasło, nazwa, rola (konsultant/inspektor)
3. Wszystkie pola są obowiązkowe
4. Login musi być unikalny
5. Po kliknięciu "Utwórz użytkownika" i pomyślnej walidacji użytkownik zostaje utworzony
6. System wyświetla komunikat potwierdzający utworzenie użytkownika
7. Nowy użytkownik pojawia się na liście jako aktywny

### US-010: Dezaktywacja użytkownika

**Jako** konsultant  
**Chcę** dezaktywować istniejącego użytkownika  
**Aby** odebrać mu dostęp do systemu

**Kryteria akceptacji:**
1. Na liście użytkowników przy każdym aktywnym użytkowniku widoczny jest przycisk "Dezaktywuj"
2. Po kliknięciu użytkownik zostaje natychmiast dezaktywowany (bez potwierdzenia)
3. System wyświetla komunikat potwierdzający dezaktywację
4. Użytkownik nadal widoczny jest na liście, ale oznaczony jako nieaktywny
5. Zdezaktywowany użytkownik nie może się zalogować
6. Eventy utworzone przez zdezaktywowanego użytkownika pozostają w systemie

### US-011: Aktywacja użytkownika

**Jako** konsultant  
**Chcę** aktywować nieaktywnego użytkownika  
**Aby** przywrócić mu dostęp do systemu

**Kryteria akceptacji:**
1. Na liście użytkowników przy każdym nieaktywnym użytkowniku widoczny jest przycisk "Aktywuj"
2. Po kliknięciu użytkownik zostaje natychmiast aktywowany
3. System wyświetla komunikat potwierdzający aktywację
4. Użytkownik widoczny jest na liście jako aktywny
5. Aktywowany użytkownik może się zalogować do systemu

### US-012: Sprawdzanie dostępności terminów

**Jako** konsultant  
**Chcę** szybko sprawdzić dostępne terminy w kalendarzu  
**Aby** zaproponować klientowi wolny termin

**Kryteria akceptacji:**
1. Kalendarz wyraźnie pokazuje zajęte i wolne terminy
2. Konsultant może nawigować między tygodniami, aby sprawdzić przyszłe terminy
3. Zajęte terminy są wyraźnie oznaczone na kalendarzu
4. Widoczne są wszystkie informacje o zajętych terminach bezpośrednio na kalendarzu
5. Kalendarz pokazuje tylko godziny pracy (07:00-16:00)
6. Weekendy są wyraźnie oznaczone jako niedostępne

## 6. Metryki sukcesu

### 6.1. Eliminacja kolizji terminów

**Cel:** 100% redukcja kolizji terminów oględzin

**Baseline:** Około 10 kolizji tygodniowo przed wdrożeniem systemu

**Mierzenie:**
- Ręczne sprawdzanie liczby kolizji terminów po wdrożeniu systemu
- Sprawdzenie, czy kiedykolwiek zdarzają się sytuacje, gdy inspektor ma więcej niż jeden termin oględzin w tym samym czasie, 
  co świadczyłoby o wadliwym działaniu walidatora

### 6.2. Efektywność systemu

**Cel:** Zwiększenie efektywności zarządzania terminami oględzin

**Mierzenie:**
- Czas potrzebny na umówienie oględzin
- Liczba anulowanych lub przełożonych terminów z powodów organizacyjnych
- Poziom satysfakcji inspektora i konsultantów z nowego systemu

### 6.3. Adaptowalność systemu

**Cel:** System jest intuicyjny i łatwy w obsłudze

**Mierzenie:**
- Czas potrzebny nowym konsultantom na naukę korzystania z systemu
- Liczba błędów popełnianych przez użytkowników podczas korzystania z systemu
- Zbieranie opinii od użytkowników systemu
