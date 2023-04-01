# Define the command prefix variable
DOCKER_COMPOSE_CMD = do-co -f .docker/docker-compose.yml

dev-env: prepare-dev-env composer-install db

prepare-dev-env:
	$(DOCKER_COMPOSE_CMD) exec app "./.prepare/dev-env"

up:
	$(DOCKER_COMPOSE_CMD) up -d

down:
	$(DOCKER_COMPOSE_CMD) stop

rebuild:
	$(DOCKER_COMPOSE_CMD) down -v --remove-orphans
	$(DOCKER_COMPOSE_CMD) rm -vsf
	$(DOCKER_COMPOSE_CMD) up -d --build

bash:
	$(DOCKER_COMPOSE_CMD) exec app bash

bash-sqlserver:
	$(DOCKER_COMPOSE_CMD) exec sqlserver bash

composer-install:
	@echo ""
	@echo "### Composer Install"
	$(DOCKER_COMPOSE_CMD) exec app "composer install"

php-lint:
	$(DOCKER_COMPOSE_CMD) exec app vendor/bin/phpcs --standard=PSR2  $(word 2,$(MAKECMDGOALS))

php-lint-fix:
	$(DOCKER_COMPOSE_CMD) exec app vendor/bin/phpcbf --standard=PSR2 --colors  $(word 2,$(MAKECMDGOALS))

db:
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console doctrine:database:drop --if-exists --force"
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console doctrine:database:create"
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console doctrine:migrations:migrate -n"
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console hau:fix:load -n"

db-test:
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console doctrine:database:drop --if-exists --force --env=test"
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console doctrine:database:create --env=test"
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console doctrine:migrations:migrate -n --env=test"
	$(DOCKER_COMPOSE_CMD) exec app "php bin/console hau:fix:load -n --env=test"

query-console:
	$(DOCKER_COMPOSE_CMD) exec sqlserver mysql -u $(SQL_USER) -p

behat:
	$(DOCKER_COMPOSE_CMD) exec app php vendor/bin/behat

behat-full: db-test behat

version:
	@echo -n "\033[32mPHP\033[0m version \033[33m"
	@echo -n "$(shell $(DOCKER_COMPOSE_CMD) exec app php -v | awk '/^PHP/ {print substr($$2, 1, 3)}')"
	@echo "\033[0m"
	@echo "$(shell $(DOCKER_COMPOSE_CMD) exec app composer -V)"
	@echo -n "\033[32mSymfony\033[0m version \033[33m"
	@echo -n "$(shell $(DOCKER_COMPOSE_CMD) exec app php bin/console --version | grep -oP '\d+\.\d+\.\d+')"
	@echo "\033[0m"
	@echo -n "\033[32mNode\033[0m version \033[33m"
	@echo -n "$(shell $(DOCKER_COMPOSE_CMD) exec app node -v)"
	@echo "\033[0m"
	@echo -n "\033[32mYarn\033[0m version \033[33m"
	@echo -n "$(shell $(DOCKER_COMPOSE_CMD) exec app yarn -v)"
	@echo "\033[0m"

-include Makefile.local