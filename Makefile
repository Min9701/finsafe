COMPOSE := HOST_UID=$(shell id -u) HOST_GID=$(shell id -g) docker compose

.PHONY: init up down restart rebuild logs ps artisan composer npm migrate

init:
	@test -f .env || cp .env.example .env
	$(COMPOSE) up -d --build
	$(COMPOSE) exec --user root app sh -c 'mkdir -p storage/framework/cache storage/framework/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache && chown -R $(shell id -u):$(shell id -g) storage bootstrap/cache'
	@if ! grep -Eq '^APP_KEY=.+' .env; then $(COMPOSE) exec app php artisan key:generate --force; fi
	$(COMPOSE) exec app php artisan migrate --force
	$(COMPOSE) exec app php artisan storage:link --force
	$(COMPOSE) exec app php artisan optimize:clear

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) down
	$(COMPOSE) up -d

rebuild:
	$(COMPOSE) up -d --build

logs:
	$(COMPOSE) logs -f

ps:
	$(COMPOSE) ps

artisan:
	$(COMPOSE) exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

composer:
	$(COMPOSE) exec app composer $(filter-out $@,$(MAKECMDGOALS))

npm:
	$(COMPOSE) exec node npm $(filter-out $@,$(MAKECMDGOALS))

migrate:
	$(COMPOSE) exec app php artisan migrate --force

%:
	@:
