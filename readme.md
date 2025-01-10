# Projekt TBO Blue Team

## Opis projektu
Projekt polega na zaproponowaniu i wdrożeniu procesu continuous integration/continuous delivery dla wybranej aplikacji internetowej ze szczególnym uwzględnieniem kwestii bezpieczeństwa.

## Realizatorzy projektu
- Mateusz Bugajak
- Igor Chmielnicki
- Mikołaj Fiedorczuk
- Marek Rospond
- Rafał Wolert

## Wybór aplikacji
W ramach projektu posiłkowano się aplikacją open source współstworzoną przez jednego z realizatorów projektu w ramach innych studiów. Decyzja ta została podjęta mając na względzie, że wspomniana aplikacja jest rozwiązaniem internetowym, napisanym w podejściu Domain-driven Design, a dodatkowo została otestowana zarówno jednostkowo, jak i integracyjnie. 

Aplikacja ta została napisana w języku PHP z wykorzystaniem frameworku Symfony, a frontend został oparty o JavaScript, TypeScipt oraz framework Angular.


Oryginalną dokumentację aplikacji pozostawiono poniżej.

## Użyte narzędzia

W ramach projektu zdecydowano się na użycie następujących narzędzi:

- Static application security testing (SATS):
    - semgrep: Narzędzie do analizy statycznej kodu, które pozwala szybko i skutecznie znajdować luki w zabezpieczeniach, błędy oraz niezgodności z dobrymi praktykami programistycznymi. Dzięki swojej elastyczności i prostemu językowi zapytań umożliwia dostosowanie reguł do konkretnych potrzeb projektu.
    - SonarQube: Popularne narzędzie do analizy statycznej, które obsługuje PHP i wiele innych języków. Wykrywa luki w zabezpieczeniach, problemy z wydajnością i błędy stylistyczne.

- Software Composition Analysis (SCA):
  - Snyk: Popularne narzędzie SCA, które analizuje zależności projektu pod kątem znanych podatności. Wspiera PHP i integruje się z popularnymi systemami kontroli wersji.
- Dynamic Analysis Security Testing (DAST):
  - Zed Attack Proxy (ZAP): Darmowe i otwarte narzędzie do dynamicznego testowania bezpieczeństwa aplikacji. Umożliwia zarówno automatyczne skanowanie, jak i ręczne testowanie. 

Dodatkowo, wybrana aplikacja korzystała już z następujących narzędzi:
- PHP CS-Fixer: Narzędzie automatyzujące poprawę stylu i formatowania kodu w PHP, zgodnie z wybranymi standardami. Pomaga utrzymać spójność w projektach, eliminując ręczne poprawki i oszczędzając czas.
- PHPStan: Statyczny analizator kodu dla PHP, który wykrywa potencjalne błędy i niezgodności jeszcze przed uruchomieniem aplikacji. Dzięki analizie na poziomie typów i zaawansowanym regułom pomaga poprawić jakość oraz niezawodność kodu.
- PHPUnit: Popularne narzędzie do testów jednostkowych w PHP, które pozwala automatycznie weryfikować poprawność działania funkcji i metod. Ułatwia wykrywanie regresji oraz zapewnia większą stabilność i niezawodność oprogramowania.

## Opis procesu

Poniżej znajduje się szczegółowy opis procesu CI/CD.

---

### 1. **Włączenie procesu na odpowiednich zdarzeniach**
Pipeline uruchamia się automatycznie przy:
- Wypchnięciu zmian do głównej gałęzi `main`.
- Utworzeniu pull requesta skierowanego na gałąź `main`.

---

### 2. **Przygotowanie środowiska**
- **Klonowanie repozytorium**:
  Użycie akcji `actions/checkout@v4` z parametrem `fetch-depth: 0`, aby sklonować pełne repozytorium (nie płytko), co jest kluczowe dla narzędzi analizy kodu, takich jak SonarQube.
- **Budowanie obrazu Docker**:  
  Polecenie `docker build` buduje obraz kontenera dla aplikacji, wykorzystując plik `./docker/fpm/Dockerfile`. Tryb BuildKit zwiększa wydajność budowy, a wynikowy obraz jest oznaczony jako `debug`.

---

### 3. **Uruchamianie aplikacji**
- **Start kontenerów**:  
  Komenda `docker compose` uruchamia aplikację i jej zależności (np. HAProxy, nginx, PostgreSQL) na podstawie pliku `docker-compose-ci.yml`. Kontenery działają w tle.

---

### 4. **Kopiowanie dodatkowych plików do kontenera**
- Kluczowe pliki konfiguracyjne (np. `.php-cs-fixer.dist.php`, `phpstan.neon`, `phpunit.xml.dist`) oraz katalogi (`tests`, `Faker`) są kopiowane do odpowiednich miejsc w kontenerze `fpm`.
- Dzieje się tak, ponieważ te pliki i katalogi zostały wykluczone z obrazu produkcyjnego przez plik `.dockerignore`, aby zmniejszyć rozmiar obrazu i uniknąć niepotrzebnych danych w środowisku produkcyjnym. Dlatego konieczne jest ich ręczne dokopiowanie do środowiska testowego.

---

### 5. **Instalacja zależności i migracja bazy danych**
- **Instalacja Composer**:  
  Podczas budowy obrazu aplikacji zależności Composer są instalowane z użyciem flagi `--no-dev`, co oznacza, że pakiety deweloperskie, takie jak PHP CS-Fixer, PHPStan czy PHPUnit, nie są instalowane w obrazie produkcyjnym.  
  W celu umożliwienia przeprowadzenia analizy kodu i testów, te zależności są instalowane ponownie w kontenerze testowym, aby nie zaśmiecać finalnego obrazu produkcyjnego.
- **Migracje baz danych**:
    - Plik `phinx` uruchamia migracje dla dwóch środowisk: `dev` i `test`.
    - Skrypt `wait-for-db.php` zapewnia, że baza danych jest gotowa do działania przed rozpoczęciem migracji.

---

### 6. **Analiza statyczna kodu**
- **Uruchamianie skryptów walidacyjnych**:  
  Wykonywany jest skrypt `app:checks` zdefiniowany w `composer.json`. Obejmuje on:
    - Walidację pliku `composer.json`.
    - Generowanie autoloadera w trybie zoptymalizowanym.
    - Analizę kodu narzędziem PHPStan.
    - Sprawdzanie poprawności formatowania kodu za pomocą PHP CS-Fixer (w trybie tylko do odczytu).

---

### 7. **Testy jednostkowe i integracyjne**
- **Uruchamianie testów PHPUnit**:  
  W kontenerze wykonywane są zarówno testy jednostkowe, jak i integracyjne, których w sumie jest ponad 300. Wyniki testów są zapisywane w pliku `phpunit-report.xml`.
- **Publikacja wyników testów**:  
  Wyniki w formacie JUnit są publikowane przy użyciu akcji `mikepenz/action-junit-report`.

---

### 8. **Analiza za pomocą Snyk**
Snyk sprawdza podatności w zależnościach aplikacji oraz generuje raport w formacie SARIF.
Raport z analizy jest przesyłany na GitHub w celu dalszej weryfikacji.

---

### 9. **Analiza za pomocą Semgrep**
Instalacja i uruchomienie Semgrep w celu automatycznej analizy kodu pod kątem potencjalnych problemów.
Raport z analizy jest przesyłany na GitHub w celu dalszej weryfikacji.

---

### 10. **Analiza za pomocą SonarQube**
- Narzędzie SonarQube przeprowadza głęboką analizę jakości kodu i testów. Uwzględnia:
    - Kod źródłowy w katalogach `src` i `packages/src`.
    - Testy w katalogach `tests` i `packages/tests`.
- Sprawdza jakość i zgodność z regułami.
- Raport z analizy jest przesyłany na GitHub w celu dalszej weryfikacji.

---

### 11. **Przygotowanie publicznych zasobów**
- Pliki w katalogu `public/` są kopiowane z kontenera `fpm` do `nginx`, aby były dostępne dla serwera.

---

### 12. **Skany bezpieczeństwa ZAP**
- Narzędzie ZAP skanuje aplikację pod kątem luk bezpieczeństwa, korzystając z adresu `http://localhost:3100`.
- Narzędzie uruchamiane jest w trybie Full Scan, przez co wykonuje zarówno skanowanie pasywne, jak i aktywne.
- Do narzędzia zostały przygotowane [pliki konfiguracyjne](resources/zap/gen.conf), określające które podatności mają skutkować niepowodzeniem analizy.
- Wyniki analizy są automatycznie przesyłane jako nowe **Issue w GitHubie**, co ułatwia śledzenie i zarządzanie problemami związanymi z bezpieczeństwem.

---

### 13. **Publikacja obrazu Docker**
- **Logowanie do GHCR**:  
  Uwierzytelnienie przy użyciu tokena GitHub.
- **Tagowanie i publikacja obrazu**:  
  Obraz aplikacji jest oznaczany odpowiednią wersją (`latest` lub debug) i przesyłany do GitHub Container Registry.

---

### 14. **Zamykanie środowiska**
- Po zakończeniu pipeline'a (bez względu na wynik) wszystkie kontenery są zatrzymywane przy użyciu `docker compose down`.

## Zabezpieczenie repozytorium
Zgodnie z instrukcją zabezpieczono repozytorium
Merge Pull Requestów może wykonywaćtylko właściciel repozytorium. Bezpośrednie pushe do gałęzi main nie są dozwolone.
Przy utworzeniu PR oraz jego merge'u uruchamiany jest proces CI/CD. Zakończenie się procesu niepowodzeniem skutkuje brakiem możliwości zmergowania brancha.
W ramach procesu uruchamiane są wyżej wymienione narzędzia.

Gdy proces zakończy się prawidłowo, utworzony obraz Dockerowy aplikacji wypychany jest do repozytorium GitHub'a. 
W zależności od operacji, tworzony obraz wypychany jest jako:
- :beta-{pull_request_data} - gdy obraz został zbudowany podczas wypchnięcia kodu do merge'a objętego Pull Requestem.
- :laters - gdy obraz został zbudowany po zmergowaniu Pull Requesta do głównej gałęzi.

## Symulacja ataków
W celu sprawdzenia działania zsymulowano dwa ataki:
- SQL Injection w komendzie CLI aplikacji,
- Wyświetlenie potencjalnie poufnych informacji o serwerze PHP w nagłówkach odpowiedzi.

Oba ataki zostały wykryte przez narzędzia, co potwierdza działanie procesu CI/CD. 
Ataki znajdują się w otwartych Pull Requestach.

## Wnioski
Projekt pokazał, jak kluczowe jest uwzględnienie bezpieczeństwa w procesie CI/CD, aby nie tylko wspierać jakość kodu, ale także aktywnie chronić aplikacje przed potencjalnymi zagrożeniami. Dzięki wdrożeniu zaawansowanego pipeline'u CI/CD, połączonego z dynamicznymi i statycznymi testami bezpieczeństwa, możliwe było wykrycie podatności, które nie zostały zauważone przez inne narzędzia używane wcześniej w projekcie.

Zastosowanie narzędzi takich jak Semgrep, Snyk i ZAP podkreśla, że analiza bezpieczeństwa musi być wielowymiarowa – obejmująca zarówno kod źródłowy, zależności, jak i działającą aplikację. Proces CI/CD pełni kluczową rolę w automatyzacji tego podejścia, integrując testy bezpieczeństwa jako nieodłączny element każdego cyklu rozwoju aplikacji. Dzięki temu zapewniono, że wszelkie zmiany wprowadzone do kodu są testowane pod kątem jakości i podatności przed ich wdrożeniem na produkcję.

Projekt udowodnił, że tradycyjne testy jednostkowe, integracyjne i statyczne, choć istotne, nie są wystarczające do pełnej ochrony aplikacji. Dynamiczne skanowanie i testowanie w kontekście działania aplikacji, jak to zrealizowano za pomocą ZAP, pozwoliło wykryć podatności, które mogłyby zostać przeoczone. To podkreśla istotność włączenia testów bezpieczeństwa w proces CI/CD, czyniąc je integralnym elementem współczesnego rozwoju oprogramowania.



# Snackbase App

## Overview
Snackbase App is a vending machine management system designed to streamline warehouse operations, track snack inventories, manage vending machines, and oversee user accounts. The application offers a comprehensive dashboard for managing all aspects of vending operations.

## Authors
- **Mateusz Bugajak**
- **Klaudiusz Mękarski**
- **Karolina Mizgała**

## Project Modules
The Snackbase App consists of the following key modules:

- **Warehouse Management** – Track inventory, receive deliveries, and monitor stock levels.
- **Snack Management** – Add, edit, and manage the snack catalog.
- **Machine Management** – Manage vending machines, stock snacks, and track sales.
- **User Management** – Manage user roles and permissions.
- **Reporting** – Generate detailed reports on inventory, sales, and machine performance.
- **API** – Provides endpoints for interacting with the system programmatically.

## Documentation
The complete project documentation can be found [here](docs/readme.md).

## Open Source and Licensing
Snackbase App is an open-source project distributed under the MIT License. Contributions are welcome, and we encourage collaboration to enhance the system further.

```
MIT License

Copyright (c) 2025 Mateusz Bugajak, Klaudiusz Mękarski, Karolina Mizgała

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

For more information, please visit the official documentation or reach out to the authors.

