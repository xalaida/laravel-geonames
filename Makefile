# Start docker containers
up:
	docker-compose up -d

# Stop docker containers
down:
	docker-compose down

# Build docker containers
install: build composer.install

# Build docker containers
build:
	docker-compose build

# Restart docker containers
restart:
	docker-compose restart

# Show status of docker containers
ps:
	docker-compose ps

# Run the testsuite
test:
	docker-compose run --rm app vendor/bin/phpunit

# Fix the code style
fix:
	docker-compose run --rm app vendor/bin/php-cs-fixer fix

# Check the code style
check:
	docker-compose run --rm app vendor/bin/php-cs-fixer fix --dry-run --diff-format udiff

# Install app dependencies
composer.install:
	docker-compose run --rm app composer install

# Update app dependencies
composer.update:
	docker-compose run --rm app composer update

# Show outdated dependencies
composer.outdated:
	docker-compose run --rm app composer outdated

# Dump composer autoload
autoload:
	docker-compose run --rm app composer dump-autoload

# Generate a coverage report as html
coverage.html:
	docker-compose run --rm app vendor/bin/phpunit --coverage-html tests/report

# Generate a coverage report as text
coverage.text:
	docker-compose run --rm app vendor/bin/phpunit --coverage-text

# Coverage text alias
coverage: coverage.text

# Set up ownership for the current user
own:
	sudo chown -R "$(shell id -u):$(shell id -g)" .

# Run the testing server
server:
	docker-compose up -d server
