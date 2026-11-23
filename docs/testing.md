## Testing

> **Note:** To prevent sending a massive amount of requests to the API, the tests are using Mockoon to mock the API responses.
> To run the tests, you need to start Mockoon first. If you are not familiar with Mockoon, please read the [Mockoon section](./mockoon.md) first.

With the Docker environment (see [Development](development.md)) the unit tests, Behat and static analysis below run with:

```bash
make docker.test
```

### Run PHPUnit tests

```bash
vendor/bin/phpunit                      # unit tests (tests/Unit)
vendor/bin/phpunit --testsuite contract # contract tests against the Saferpay test API, require Chrome (Panther)
```

The contract tests are not part of `make docker.test`. They read the API location and credentials from real
environment variables (not from `.env` files), so export them first:

```bash
export SAFERPAY_TEST_API_URL=https://test.saferpay.com/api/
export SAFERPAY_TEST_API_USER=API_xxxxx_xxxxxxxx
export SAFERPAY_TEST_API_PASSWORD=...
vendor/bin/bdi detect drivers           # installs a ChromeDriver matching your Chrome
```

### Run Behat scenarios

```bash
vendor/bin/behat --strict
```

### Run Static Analysis checks

```bash
vendor/bin/phpstan analyse
```

### Run Coding Standard check

```bash
vendor/bin/ecs check
```

---

Prev: [Development](development.md)
