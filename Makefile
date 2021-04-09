all: build test

setup:
	composer install

build:
	./vendor/bin/phplemon src/SQLParser/Parser.y || exit 0
	php build.php
	./vendor/bin/php-cs-fixer fix src
test:
	phpunit --coverage-html coverage
