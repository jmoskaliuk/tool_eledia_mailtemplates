# Ergänzungen für moodle-framework Skill

Diese Einträge sollten in die entsprechenden Referenzdateien des
`moodle-framework`-Skills übernommen werden.

---

## → references/05-errors.md

### Abschnitt "JavaScript & AMD" — neue Einträge nach ERR-061

#### ERR-062: AMD Build komplett leer — Modul lädt nie

**Symptom:** `js_call_amd` wird aufgerufen, aber das Modul gibt keinen Fehler
und zeigt nur den initialen Ladezustand ("Loading…" für immer).
**Ursache:** `amd/build/` existiert, ist aber leer (kein `.min.js`). Moodle
kann das Modul nicht finden und ignoriert den Aufruf stillschweigend.
**Fix:** Falls kein grunt verfügbar, manuell `amd/build/<modul>.min.js` anlegen
im AMD-`define()`-Format:
```js
define("pluginname/modulname", ["core/ajax", "core/notification"], function(Ajax, Notification) {
    return { init: function() { /* ... */ } };
});
```
**Prävention:** Nach `amd/src/` immer prüfen ob ein entsprechendes Build-File
existiert. Im CI schlägt `grunt` fehl und zeigt das Problem.

#### ERR-063: js_call_amd mit zu großen Daten als Argumente

**Symptom:** `Uncaught Error: Too much data passed as arguments to js_call_amd`
(Moodle-Limit: ~1 024 Zeichen im serialisierten JSON).
**Ursache:** Große Datensätze direkt als Init-Argument übergeben.
**Fix:** `js_call_amd` ohne Argumente aufrufen, Daten per AJAX nachladen:
```php
// Falsch:
$PAGE->requires->js_call_amd('plugin/module', 'init', [$largeData]);
// Richtig:
$PAGE->requires->js_call_amd('plugin/module', 'init');
// JS holt Daten via Ajax.call([{ methodname: 'plugin_get_data', ... }])
```

---

### Abschnitt "Hooks & Events" — neuer Eintrag (oder als ERR-085 in Deployment & CI)

#### ERR-085: Hook/Event-Callback als Closure statt String

**Symptom:** "Hook callback definition contains invalid 'callback' string" oder
"Serialization of 'Closure' is not allowed" beim Laden jeder Seite.
**Ursache:** PHP First-Class-Callable-Syntax `ClassName::method(...)` erzeugt
ein Closure-Objekt. Moodle serialisiert Callbacks für den Cache — Closures
sind nicht serialisierbar.
**Fix:** In `db/hooks.php` und `db/events.php` immer String-Format:
```php
// Falsch:
'callback' => \myplugin\hooks::before_sent(...),

// Richtig:
'callback' => 'myplugin\hooks::before_sent',
```
**Prävention:** db/-Dateien sind Datendateien — kein PHP-Code, keine Callables.

---

### Abschnitt "Deployment & CI" — neue Einträge

#### ERR-083: Falscher PHPUnit-CLI-Pfad bei Bitnami Moodle

**Symptom:** `Could not open input file: /var/www/site/moodle/admin/tool/phpunit/cli/init.php`
**Ursache:** Bitnami-Moodle legt Dateien unter `public/` ab, nicht direkt im Webroot.
**Fix:** Korrekter Pfad: `/var/www/site/moodle/public/admin/tool/phpunit/cli/init.php`
**Prävention:** Bei Bitnami immer `public/` im Pfad berücksichtigen.

#### ERR-084: Stale .git/index.lock auf gemountem Filesystem

**Symptom:** `fatal: Unable to create '.git/index.lock': File exists` beim `git add`,
obwohl kein git-Prozess läuft.
**Ursache:** Der Repo-Ordner ist via Cowork/NFS in die Sandbox gemountet. Ein
früherer abgebrochener git-Prozess auf dem Mac hat die Lock-Datei hinterlassen.
Die Sandbox kann sie nicht löschen (keine Write-Permission auf den Mount-Owner).
**Fix:** Im Mac-Terminal im Repo-Verzeichnis: `rm .git/index.lock`
**Prävention:** Nach Unterbrechungen oder Absturz auf dem Mac prüfen ob die Lock-Datei
noch da ist: `ls .git/index.lock`

---

## → references/03-testing.md

### Abschnitt "PHPUnit ausführen" — Ergänzung

#### PHPUnit-Init-Pfad bei Bitnami Moodle

```bash
# Standard-Moodle:
php admin/tool/phpunit/cli/init.php

# Bitnami-Moodle (Dateien unter public/):
php /var/www/site/moodle/public/admin/tool/phpunit/cli/init.php

# Im Docker-Container via Orb:
orb -m <vm> docker exec -i <container> bash -c \
  "cd /var/www/site/moodle && php public/admin/tool/phpunit/cli/init.php"
```

### Neue Regel: Test-Templates und Assertions müssen übereinstimmen

**Problem:** Test schreibt ein Template mit `subject: 'Hallo {{site_name}}'`
und prüft dann `assertStringContainsString('Anna', $message->subject)` — schlägt
immer fehl weil `{{recipient_firstname}}` gar nicht im Subject-Template steht.

**Regel:** Vor jeder `assertStringContains`-Assertion prüfen:
Steht die gesuchte Variable wirklich als `{{variable}}` im Template des Tests?
Wenn nein: entweder Template anpassen oder Assertion anpassen.

---

## → references/01-workflow.md

### Abschnitt "GitHub Actions" — vollständiger moodle-ci.yml

Ersetze das Skeleton-YAML durch dieses bewährte Template
(getestet mit tool_eledia_mailtemplates, April 2026):

```yaml
name: Moodle CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: self-hosted
    strategy:
      fail-fast: false
      matrix:
        include:
          - moodle-branch: MOODLE_405_STABLE
            php: '8.1'
            db: pgsql
          - moodle-branch: MOODLE_405_STABLE
            php: '8.3'
            db: mariadb
          - moodle-branch: MOODLE_500_STABLE
            php: '8.3'
            db: pgsql
          - moodle-branch: main
            php: '8.3'
            db: mariadb

    env:
      IGNORE_PATHS: amd/build

    steps:
      - name: Check out plugin repository
        uses: actions/checkout@v4
        with:
          path: plugin

      - name: Set up PHP ${{ matrix.php }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: mbstring, pgsql, mysqli, zip, gd, intl, xmlrpc, soap, xdebug
          tools: composer
          coverage: none

      - name: Set up Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install moodle-plugin-ci
        run: |
          composer create-project -n --no-dev --prefer-dist \
            moodlehq/moodle-plugin-ci ci ^4
          echo "$(cd ci && pwd)/bin" >> $GITHUB_PATH
          echo "$(cd ci && pwd)/vendor/bin" >> $GITHUB_PATH
        env:
          COMPOSER_NO_INTERACTION: 1

      - name: Install Moodle
        run: |
          moodle-plugin-ci install \
            --plugin ./plugin \
            --db-host 127.0.0.1 \
            --db-name moodle \
            --db-user moodle \
            --db-pass moodle \
            --db-type ${{ matrix.db }} \
            --moodle-branch ${{ matrix.moodle-branch }} \
            --no-behat
        env:
          DB: ${{ matrix.db }}

      - name: PHP Lint
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci phplint ./plugin

      - name: PHP Copy/Paste Detector
        continue-on-error: true
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci phpcpd ./plugin

      - name: PHP Mess Detector
        continue-on-error: true
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci phpmd ./plugin

      - name: Moodle Code Checker (phpcs)
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci phpcs --max-warnings 0 ./plugin

      - name: Moodle PHPDoc Checker
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci phpdoc --max-warnings 0 ./plugin

      - name: Validate
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci validate ./plugin

      - name: Check upgrade savepoints
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci savepoints ./plugin

      - name: Mustache Lint
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci mustache ./plugin

      - name: Grunt
        continue-on-error: true
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci grunt --max-lint-warnings 0 ./plugin

      - name: PHPUnit
        if: ${{ !cancelled() }}
        run: moodle-plugin-ci phpunit --fail-on-warning ./plugin
```

**Hinweise:**
- `IGNORE_PATHS: amd/build` verhindert dass grunt über manuell kompilierte Builds stolpert
- `continue-on-error: true` für phpmd/phpcpd/grunt → Warnungen blockieren nicht
- `--no-behat` bei Install da Behat-Setup eigene Selenium-Infrastruktur braucht
- `if: ${{ !cancelled() }}` stellt sicher dass alle Checks laufen auch wenn einer fehlschlägt
