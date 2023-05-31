## Testing

> **Note:** To prevent sending a massive amount of requests to the API, the tests are using Mockoon to mock the API responses.
> To run the tests, you need to start Mockoon first. If you are not familiar with Mockoon, please read the [Mockoon section](./mockoon.md) first.

### Run PHPSpec tests

```bash
vendor/bin/phpspec run
```

### Run PHPUnit tests

```bash
vendor/bin/phpunit
```

### Run Behat scenarios

```bash
vendor/bin/behat --strict
```

### Run Static Analysis checks

```bash
vendor/bin/psalm
vendor/bin/phpstan analyse
```

### Run Coding Standard check

```bash
vendor/bin/ecs check
```

---

Prev: [Development](development.md)
