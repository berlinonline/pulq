PROJECT_ROOT=`pwd`


help:
	@exit 0

cc:
	@if [ ! -d app/cache ]; then mkdir -p app/cache; fi
	@if [ ! -d app/log ]; then mkdir -p app/log; fi
	@if [ ! -d data/assets ]; then mkdir -p data/assets; fi
	@chmod 775 app/cache
	@chmod 775 data/assets
	@chmod 775 app/log
	@rm -rf app/cache/*

	@if [ ! -d pub/static/cache ]; then mkdir pub/static/cache; fi
	@chmod 775 pub/static/cache
	@rm -rf pub/static/cache/*

	@make composer-autoload


config:
	-@rm app/config/includes/* > /dev/null
	@bin/cli setup.buildconfigs
	@make cc

environment:
	@vendor/graste/environaut/bin/environaut.phar check

install: install-vendor node-deps cc
	@make environment
	@make create-project-skeleton
	@make link-project-modules
	@make create-project-config
	@make deploy-resources

update: update-composer update-vendor node-deps link-project-modules link-project-config cc

tail-logs:
	@tail -f app/log/*.log


install-composer:
	@if [ -d vendor/agavi/agavi/ ]; then svn revert -R vendor/agavi/agavi/; fi
	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi
	-@bin/apply-patches


update-composer:
	@bin/composer.phar self-update

composer-autoload:
	@bin/composer.phar dump-autoload

install-vendor: install-composer
	@php -d allow_url_fopen=1 bin/composer.phar install


update-vendor: install-vendor
	@svn revert -R vendor/agavi/agavi/ || true
	@php -d allow_url_fopen=1 bin/composer.phar update
	-@bin/apply-patches

node-deps:
	@npm update


rebuild-index:
	@bin/cli rebuild_indices -db Default.Read -action create


.PHONY: help module cc config install update environment

# vim: set ts=4 sw=4 noexpandtab:
#
