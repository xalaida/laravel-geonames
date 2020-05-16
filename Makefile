# Install the app
install: build deps test

# Build app container
build:
	docker build -t app .

# Install app dependencies
deps:
	docker run --rm -it -v ${PWD}:/app app composer install

# Update app dependencies
update:
	docker run --rm -it -v ${PWD}:/app app composer update

# Show outdated dependencies
outdated:
	docker run --rm -it -v ${PWD}:/app app composer outdated

# Run the testsuite
test:
	docker run --rm -it -v ${PWD}:/app app vendor/bin/phpunit

# Generate a coverage report
coverage:
	docker run --rm -it -v ${PWD}:/app app vendor/bin/phpunit --coverage-html tests/report

# Fix code style
fix:
	docker run --rm -it -v ${PWD}:/app app vendor/bin/php-cs-fixer fix
