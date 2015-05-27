PROJECT_ROOT=`pwd`

help:

	@echo "COMMON"
	@echo "  cc - Clear the cache directories and set file perms+mode."
	@echo "  tail-logs - Tail all application logs."
	@echo "  install - Initially setup a vanilla checkout."
	@echo "  update - Update the working copy and vendor libs."
	@echo "  module - Create a new module"
	@echo "  action - Create a new action inside an existing module"
	@echo "  link-project-modules - Symlink custom code into the pulq submodule and update the local git/info/exclude settings."
	@echo "  create-project-skeleton - Initially creates the project directory structure."
	@echo "  link-project-config - Symlink custom configs into the pulq submodule."
	@echo "  link-project-pub - Symlink custom public files into the pub directory."
	@echo "  link-project-layout - Symlink resources (to pub directory) and templates includes, referring a specific version (e.g. 'make link-project-layout version=YYYYMMDD')"
	@echo "  create-project-config - Creates the project specific config files."
	@echo ""
	@echo "DEVELOPMENT"
	@echo "  Scafolding"
	@echo "    module - Create and integrate a new module."
	@echo "    remove-module - Remove an existing module."
	@echo "    module-code - Generate code for an existing module."
	@echo "    config - Generate includes for all modules."
	@echo "    deploy-resources - Generate and deploy script-, style- and binary-packages."
	@echo "  Php"
	@echo "    test - Run php test suites."
	@echo "    phpcs - Run the php code-sniffer and publish report."
	@echo "  Scripts:"
	@echo "    js-specs - Run vows scenarios with spec output to test the project's js code."
	@echo "    js-xunit - Run vows scenarios with xunit output to test the project's js code."
	@echo "    js-docs - Generate api doc for the project's js code."
	@echo "  Styles:"
	@echo "    no current style related targets as the ProjectResourceFilter takes care of this stuff now."
	@echo ""
	@echo "INTERNAL"
	@echo "  install-composer - install composer"
	@echo "  install-vendor - install dependencies in vendor folder."
	@echo "  update-vendor - update dependencies in vendor folder."
	@echo "  install-node-deps - install nodejs dependencies in node_modules folder."
	@echo "  update-node-deps - update nodejs dependencies in node_modules folder."
	@echo "  generate-autoloads - generate autoloads for vendors/dependencies and libs."
	@exit 0


cc:

	@if [ ! -d app/cache ]; then mkdir -p app/cache; fi
	@if [ ! -d app/log ]; then mkdir -p app/log; fi
	@if [ ! -d data/assets ]; then mkdir -p data/assets; fi
	@chmod 775 app/cache
	@chmod 775 data/assets
	@chmod 775 app/log
	@rm -rf app/cache/*
	@echo "-> ensured consistency for: app/cache(cleared), app/log and data/assets."

	@if [ ! -d pub/static/cache ]; then mkdir pub/static/cache; fi
	@chmod 775 pub/static/cache
	@rm -rf pub/static/cache/*
	@echo "-> cleared public resources cache."

	@make generate-autoloads


config:

	-@rm app/config/includes/* > /dev/null
	@php bin/include-configs.php
	@make cc


install: install-vendor install-node-deps cc

	@if [ ! -f etc/local/local.config.sh ]; then bin/configure-env --init; fi
	@make create-project-skeleton
	@make link-project-modules
	@make create-project-config
	@make deploy-resources


update: update-composer update-vendor update-node-deps link-project-modules link-project-config


tail-logs:

	@tail -f app/log/*.log


generate-autoloads:

	@php bin/composer.phar dump-autoload




deploy-resources:

	@if [ ! -d pub/static/deploy ]; then mkdir pub/static/deploy; fi
	@rm -rf pub/static/deploy/*
	@php bin/deploy-resources.php


install-composer:

	@if [ -d vendor/agavi/agavi/ ]; then svn revert -R vendor/agavi/agavi/; fi
	@if [ ! -f bin/composer.phar ]; then curl -s http://getcomposer.org/installer | php -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin; fi
	-@bin/apply-patches


update-composer:
	@bin/composer.phar self-update


install-vendor: install-composer

	@php -d allow_url_fopen=1 bin/composer.phar install


update-vendor: install-vendor

	@svn revert -R vendor/agavi/agavi/ || true
	@php -d allow_url_fopen=1 bin/composer.phar update
	-@bin/apply-patches


install-node-deps:

	@npm install


update-node-deps: install-node-deps

	@npm update


test:

	@nice bin/test --configuration testing/config/phpunit.xml


phpcs:

	@/bin/mkdir -p etc/integration/build/logs
	-@vendor/bin/phpcs --report=checkstyle --report-file=${PROJECT_ROOT}/etc/integration/build/logs/checkstyle.xml --standard=${PROJECT_ROOT}/etc/coding-standards/BerlinOnline/ruleset.xml --ignore='app/cache*,*Success.php,*Input.php,*Error.php,app/templates/*' ${PROJECT_ROOT}/app


phpdoc:

	@/bin/mkdir -p etc/integration/docs/serverside/
	@vendor/bin/phpdoc.php --config ${PROJECT_ROOT}/app/config/phpdocumentor.xml


js-specs:

	@bin/test-js --spec


js-xunit:

	@/bin/rm -rf etc/integration/build/logs/clientside.xml
	@/bin/mkdir -p etc/integration/build/logs
	@bin/test-js --xunit | cat > etc/integration/build/logs/clientside.xml


jsdoc:

	@/bin/mkdir -p etc/integration/docs/clientside
	@bin/jsdoc pub/js/pulq --output etc/integration/docs/clientside/


link-project-modules:

	@bin/link-project-modules
	@make config

link-project-config:

	@bin/link-project-config
	@make config

link-project-pub:

	@bin/link-project-pub
	@make config

link-project-layout:

	@bin/link-project-layout $(version)
	@make config

create-project-skeleton:

	@bin/create-project-skeleton
	@make create-project-config

create-project-config:

	@bin/create-project-config
	@make link-project-config
	@make config

module:

	@bin/agavi pulq-module-wizard
	@make config

action:

	@bin/agavi pulq-action-wizard
	@make config

remove-module:

	@bin/agavi module-list
	@read -p "Enter module to remove:" module; unlink app/modules/$$module; rm -rf ../project/modules/$$module
	@make link-project-modules
	@make config

module-code:

	@bin/agavi module-list
	@read -p "Enter Module Name:" module;
	@make config
	@curl -XDELETE localhost:9200/
	@echo "\n"

rebuild-index:
	@bin/cli rebuild_indices -db Default.Read -action create


.PHONY: help module module-code lessw lessc jsdoc js-xunit js-specs phpdoc phpcs test cc config install update

# vim: ts=4:sw=4:noexpandtab!:
#
