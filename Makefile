## Clear cache
.PHONY: rmcache
rmcache:
	@echo -n "Clearing cache... " ;
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		rm -rf var/cache/*; \
 	else \
		sudo rm -rf yes var/cache/*; \
	fi; \
	echo "done";

# Start docker container
.PHONY: up
up:
	make rmcache
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		echo 'You are already in docker container'; \
	else \
		docker compose up -d; \
	fi; \


# Shutdown docker container
.PHONY: down
down:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		echo 'You must first leave the docker container'; \
	else \
		docker compose down; \
	fi; \

## Restart docker container
.PHONY: restart
restart: down up

## Enter to container
.PHONY: exec
exec:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		echo 'You are already in docker container'; \
	else \
		docker compose exec app /bin/bash; \
	fi; \

## Install - start containers, install dev dependencies and build assets (development only)
.PHONY: install
install:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		composer install; \
		bin/console doctrine:migrations:migrate --no-interaction; \
		npm install; \
		npm run encore production --no-watch; \
	else \
		docker compose up -d; \
		docker compose exec app composer install; \
		docker compose exec app bin/console doctrine:migrations:migrate --no-interaction; \
		docker compose exec app npm install; \
		docker compose exec app npm run encore production --watch=false; \
	fi; \

## Prod-install - install production dependencies only (no dev packages, optimised autoloader)
## Run this on the production server or inside the production container.
.PHONY: prod-install
prod-install:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		composer install --no-dev --optimize-autoloader --classmap-authoritative; \
		bin/console doctrine:migrations:migrate --no-interaction; \
		npm install; \
		npm run encore production --no-watch; \
	else \
		docker compose up -d; \
		docker compose exec app composer install --no-dev --optimize-autoloader --classmap-authoritative; \
		docker compose exec app bin/console doctrine:migrations:migrate --no-interaction; \
		docker compose exec app npm install; \
		docker compose exec app npm run encore production --watch=false; \
	fi; \

## Generate a new Doctrine migration
.PHONY: migration
migration:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		bin/console doctrine:migrations:diff; \
	else \
		mkdir -p migrations/$$(date +%Y)/$$(date +%m); \
		docker compose exec app bin/console doctrine:migrations:diff; \
	fi; \

## Create an empty Doctrine migration
.PHONY: migration-empty
migration-empty:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		bin/console doctrine:migrations:generate; \
	else \
		mkdir -p migrations/$$(date +%Y)/$$(date +%m); \
		docker compose exec app bin/console doctrine:migrations:generate; \
	fi; \

## Run Doctrine migrations
.PHONY: db-migrate
db-migrate:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		bin/console doctrine:migrations:migrate --no-interaction; \
	else \
		docker compose exec app bin/console doctrine:migrations:migrate --no-interaction; \
	fi; \

## CI Stack
.PHONY: ci
ci: cs phpstan test-entity

## CodeSniffer - checks codestyle and typehints
.PHONY: cs
cs:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		vendor/bin/phpcs --cache=var/phpcs.cache --standard=dev/ruleset.xml --extensions=php --encoding=utf-8 --colors --tab-width=4 -sp --colors src -s; \
	else \
		docker compose exec app vendor/bin/phpcs --cache=var/phpcs.cache --standard=dev/ruleset.xml --extensions=php --encoding=utf-8 --colors --tab-width=4 -sp --colors src -s; \
	fi; \

## PhpStan - PHP Static Analysis
.PHONY: phpstan
phpstan:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		vendor/bin/phpstan analyse --memory-limit=1024M -c dev/phpstan.neon; \
	else \
		docker compose exec app vendor/bin/phpstan analyse --memory-limit=1024M -c dev/phpstan.neon; \
	fi; \

## Entity mapping test
.PHONY: test-entity
test-entity:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		php bin/console doctrine:schema:validate --skip-sync --ansi; \
	else \
		docker compose exec app php bin/console doctrine:schema:validate --skip-sync --ansi; \
	fi; \

## NPM - compile assets production mode and automatically recompile when files change
.PHONY: hot-reload
hot-reload:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		npm run encore production --watch; \
	else \
		docker compose exec app npm run encore production --watch; \
	fi; \

## NPM - compile assets production mode
.PHONY: production
production:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		npm run encore production --watch=false; \
	else \
		docker compose exec app npm run encore production --watch=false; \
	fi; \