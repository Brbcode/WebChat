# Define the command prefix variable
DOCKER_COMPOSE_CMD = do-co -f .docker/docker-compose.yml

dev-env: prepare-dev-env composer-install

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

composer-install:
	@echo ""
	@echo "### Composer Install"
	$(DOCKER_COMPOSE_CMD) exec app "composer install"

php-lint:
	$(DOCKER_COMPOSE_CMD) exec app vendor/bin/phpcs --standard=PSR2  $(word 2,$(MAKECMDGOALS))

php-lint-fix:
	$(DOCKER_COMPOSE_CMD) exec app vendor/bin/phpcbf --standard=PSR2 --colors  $(word 2,$(MAKECMDGOALS))

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