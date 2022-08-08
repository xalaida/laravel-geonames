# Run command from the app container
alias app='docker-compose run --rm app'

# Run the testsuite with a filter option
alias tf='docker-compose run --rm app vendor/bin/phpunit --filter'

# Run the testsuite with a filter option and coverage enabled
alias tfc='docker-compose run --rm app vendor/bin/phpunit --coverage-text --filter'
