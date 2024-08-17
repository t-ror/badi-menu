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
		docker-compose -f docker-compose.yml up -d; \
	fi; \


# Shutdown docker container
.PHONY: down
down:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		echo 'You must first leave the docker container'; \
	else \
		docker-compose -f docker-compose.yml down; \
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
		docker-compose -f docker-compose.yml exec app /bin/bash; \
	fi; \

## Create diff.sql file with database differences
.PHONY: db-diff
db-diff:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		bin/console doctrine:schema:update --dump-sql > diff.sql --complete; \
	else \
		docker-compose -f docker-compose.yml exec app doctrine:schema:update --dump-sql > diff.sql --complete; \
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
		docker-compose -f docker-compose.yml exec app vendor/bin/phpcs --cache=var/phpcs.cache --standard=dev/ruleset.xml --extensions=php --encoding=utf-8 --colors --tab-width=4 -sp --colors src -s; \
	fi; \

## PhpStan - PHP Static Analysis
.PHONY: phpstan
phpstan:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		vendor/bin/phpstan analyse --memory-limit=1024M -c dev/phpstan.neon; \
	else \
		docker-compose -f docker-compose.yml exec app vendor/bin/phpstan analyse --memory-limit=1024M -c dev/phpstan.neon; \
	fi; \

## Entity mapping test
.PHONY: test-entity
test-entity:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		php bin/console doctrine:schema:validate --skip-sync --ansi; \
	else \
		docker-compose -f docker-compose.yml exec app php bin/console doctrine:schema:validate --skip-sync --ansi; \
	fi; \

## NPM - compile assets production mode and automatically recompile when files change
.PHONY: hot-reload
hot-reload:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		npm run encore production --watch; \
	else \
		docker-compose -f docker-compose.yml exec app npm run encore production --watch; \
	fi; \

## NPM - compile assets production mode
.PHONY: production
production:
	@if [ -f /.dockerenv ] || [ "$(RAW)" = "1" ] ; then \
		npm run build; \
	else \
		docker-compose -f docker-compose.yml exec app npm run build; \
	fi; \
