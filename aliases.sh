# Run command from app
alias app='docker run --rm -it -v ${PWD}:/app app'

# Test by filter alias
alias tf='docker run --rm -it -v ${PWD}:/app app vendor/bin/phpunit --filter'

# Test by filter with coverage report
alias tfc='docker run --rm -it -v ${PWD}:/app app vendor/bin/phpunit --coverage-text --filter'
