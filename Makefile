PROJECT_ROOT=`pwd`


help:
	@bin/cli util.help --emergency

cc:
	@if [ ! -d app/cache ]; then mkdir -p app/cache; fi
	@if [ ! -d app/log ]; then mkdir -p app/log; fi
	@chmod 775 app/cache
	@chmod 775 app/log
	@rm -rf app/cache/*

tail-logs:
	@tail -f app/log/*.log

new: cc
	@make install-dependencies
	@make environment
	@make skeleton
	@make symlinks
	@make config
	@make js
	@make css
	@make cc

install: cc
	@make install-dependencies
	@make environment
	@make symlinks
	@make config
	@make js
	@make css
	@make cc

update: cc
	@make update-dependencies
	@make symlinks
	@make config
	@make js
	@make css
	@make cc

static: cc
	@make js
	@make css

install-composer:
	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi

install-dependencies: install-composer
	@if [ -d vendor/agavi/agavi ]; then svn revert -R vendor/agavi/agavi/ || true ; fi
	@if [ ! -f bin/composer.phar ]; then make install-composer; fi
	@if [ -f vendor ]; then rm -rf vendor; fi
	@php -d allow_url_fopen=1 bin/composer.phar install
	-@bin/apply-patches
	@npm update

update-dependencies: install-composer
	@if [ -d vendor/agavi/agavi ]; then svn revert -R vendor/agavi/agavi/ || true ; fi
	@php -d allow_url_fopen=1 bin/composer.phar update
	-@bin/apply-patches
	@npm update

environment:
	@mkdir -p etc/local
	@vendor/graste/environaut/bin/environaut.phar check --autoload-dir="app/lib"

skeleton:
	@bin/cli util.build_project --emergency
	@make cc

symlinks:
	@bin/cli util.build_links --emergency

config:
	-@rm app/config/includes/* > /dev/null
	@bin/cli util.build_config --emergency
	@make cc

db:
	@bin/cli util.create_db

reindex:
	@bin/cli util.rebuild_idx

fixtures:
	@bin/cli util.load_fixtures

module:
	@bin/cli util.build_module

action:
	@bin/cli util.build_action

js:
	@bin/cli util.requirejs

css:
	@bin/cli util.scss

.PHONY: help new install update skeleton symlinks db module action environment js css cc

# vim: set ts=4 sw=4 noexpandtab:
#
