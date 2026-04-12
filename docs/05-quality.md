# Quality

## Meta

This document tracks system quality.

It contains:
- bugs (bugXX)
- test runs (testXX)

Use it for:
- reproducible issues
- verification

Do NOT include:
- ideas
- tasks

---

## 🐞 Bugs

### bug01 Closure-Syntax in db/hooks.php und db/events.php
Status: fixed
Feature: feat06
Severity: blocker

Symptom: "Serialization of 'Closure' is not allowed" beim Laden jeder Moodle-Seite.
Ursache: PHP First-Class-Callable-Syntax `ClassName::method(...)` erzeugt ein Closure-Objekt. Moodle serialisiert Callbacks für den Cache — Closures sind nicht serialisierbar.
Fix: String-Format verwenden: `'fully\qualified\ClassName::methodName'`
Präventionsregel: In db/hooks.php und db/events.php IMMER String-Callbacks, niemals `...`-Syntax.

---

### bug02 js_call_amd mit zu großen Init-Argumenten
Status: fixed
Feature: feat08 (live preview)
Severity: blocker

Symptom: `Uncaught Error: Too much data passed as arguments to js_call_amd` (Moodle-Limit: 1 024 Zeichen serialisiertes JSON).
Ursache: Sample-Variablen für alle Notification-Typen direkt als AMD-Init-Argument übergeben.
Fix: `js_call_amd` ohne Argumente. JavaScript holt Daten on demand per AJAX (`get_type_variables`-Webservice).
Regel: Niemals große Datensätze als js_call_amd-Argumente. AJAX bevorzugen.

### bug03 AMD build/ leer — Modul lädt nie
Status: fixed
Feature: feat08 (live preview)
Severity: blocker

Symptom: `js_call_amd` ruft das Modul auf, aber es passiert nichts ("Loading…" hängt für immer). Kein JS-Fehler.
Ursache: `amd/build/` existierte, enthielt aber keine `.min.js`. Moodle ignoriert den Aufruf stillschweigend.
Fix: `amd/build/preview.min.js` manuell als AMD `define()`-Format angelegt (kein Grunt nötig).
Regel: Nach `amd/src/` anlegen sofort prüfen ob `amd/build/` das kompilierte File enthält.

### bug04 Test-Assertion prüft Variable die nicht im Template-Platzhalter steht
Status: fixed
Feature: tests (message_interceptor_test.php)
Severity: test-failure

Symptom: `assertStringContainsString('Anna', $message->subject)` schlägt fehl, weil das Subject-Template `'Reset your password on {{site_name}}'` keinen `{{recipient_firstname}}`-Platzhalter enthält.
Ursache: Test-Template und Test-Assertion stimmten nicht überein.
Fix: Subject-Template auf `'Hello {{recipient_firstname}}, reset your password'` geändert.
Regel: Vor dem Schreiben von `assertStringContains`-Assertions prüfen, welche Variablen wirklich im Template-String stehen.

---

## 🧪 Tests

### test01 PHPUnit Suite — Session 2026-04-12
Ergebnis: 52/53 Tests bestanden (1 Fehler → bug04)
Nach Fix: 53/53 Tests bestanden
Suite: `tool_eledia_mailtemplates_testsuite`
Dateien: `tests/variable_resolver_test.php`, `tests/notification_type_test.php`, `tests/template_manager_test.php`, `tests/message_interceptor_test.php`, `tests/external/get_type_variables_test.php`
Umgebung: Docker (Bitnami Moodle), Moodle 4.5, PHP 8.2, MariaDB
PHPUnit-Init-Pfad: `/var/www/site/moodle/public/admin/tool/phpunit/cli/init.php`

### test02 GitHub Actions CI — erster Push
Status: failed → fixed, neu gepusht am 2026-04-12
Workflow: `.github/workflows/moodle-ci.yml`
Matrix: Moodle 4.5/5.0/main × PHP 8.1/8.3 × pgsql/mariadb (4 Zellen)
Runner: self-hosted (Hetzner)

Fehler im ersten Run (April 8):
- `FATAL: role "root" does not exist` → self-hosted Runner läuft als root, PostgreSQL kennt die Role nicht
- `Not enough arguments (missing: "plugin")` → Folge-Fehler weil Install gescheitert war
- Trigger war nur `workflow_dispatch` → kein automatischer Lauf bei Push

Fix (April 12):
- `--db-user=postgres` zur Install-Step hinzugefügt
- `on: push/pull_request` als Trigger gesetzt
- MariaDB-Service-Container ergänzt
- Git-Locks: `.git/HEAD.lock`, `.git/config.lock` → `rm -f` auf dem Mac nötig
- Erster Push auf neuen Branch: `git push --set-upstream origin master`

Stand 2026-04-12: Run #7 hängt seit Push im Status „queued".
Self-hosted Runner (Hetzner) ist vermutlich offline oder nicht mehr registriert.
→ Runner-Status auf Hetzner prüfen, ggf. neu starten oder GitHub-hosted Runner als Fallback konfigurieren.
