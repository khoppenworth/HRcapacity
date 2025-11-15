# HRassess Backend (Offline Edition)

This backend is a lightweight PHP application that runs entirely in this repository without any external Composer dependencies. It uses an in-memory database and a custom test harness so it works even when no package mirrors are available.

## Structure
- `app/` – source code for the in-memory API.
- `tests/` – feature tests that exercise the API through the custom request runner.
- `vendor/` – local autoloader and lightweight phpunit runner script.

## Running the tests
Use the bundled runner:

```
php vendor/bin/phpunit
```

The command reports each test case and exits with a non-zero code if any assertion fails.
