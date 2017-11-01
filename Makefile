dependencies:
	composer install \
		--no-interaction \
		--no-plugins \
		--no-scripts

tests: dependencies
	vendor/bin/phpunit --verbose tests

coverage: dependencies
	rm -rf ./coverage
	vendor/bin/phpunit --coverage-html ./coverage tests

server:
	@echo Running on http://127.0.0.1:5678
	php -t ../../.. -S 127.0.0.1:5678 php-server.php
