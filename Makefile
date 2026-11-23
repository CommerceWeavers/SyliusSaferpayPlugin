phpunit:
	vendor/bin/phpunit

phpspec:
	vendor/bin/phpspec run --ansi --no-interaction -f dot

phpstan:
	vendor/bin/phpstan analyse

behat-js:
	APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

install:
	composer install --no-interaction --no-scripts

backend:
	tests/Application/bin/console sylius:install --no-interaction
	tests/Application/bin/console sylius:fixtures:load default --no-interaction

frontend:
	(cd tests/Application && yarn install)
	(cd tests/Application && yarn build)

behat:
	APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

init: install backend frontend

ci: init phpstan phpunit phpspec behat

integration: init phpunit behat

static: install phpspec phpstan

### Docker ###
DOCKER_APP = docker compose exec app
DOCKER_CONSOLE = docker compose exec -w /app/tests/Application app

docker.up:
	docker compose up -d --build --wait

docker.down:
	docker compose down

docker.logs:
	docker compose logs -f app

docker.shell:
	$(DOCKER_APP) bash

docker.fixtures:
	$(DOCKER_CONSOLE) bin/console sylius:fixtures:load -n

docker.frontend:
	$(DOCKER_CONSOLE) sh -c 'yarn install && yarn build'

docker.test:
	$(DOCKER_APP) vendor/bin/ecs check
	$(DOCKER_APP) vendor/bin/phpstan analyse --no-progress
	$(DOCKER_APP) vendor/bin/phpspec run --ansi --no-interaction -f dot
	$(DOCKER_APP) vendor/bin/phpunit
	$(DOCKER_APP) sh -c 'APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -f progress'
