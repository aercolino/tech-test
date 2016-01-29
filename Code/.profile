# $ . ./.profile

export PATH="$(brew --prefix homebrew/php/php70)/bin:$PATH"
alias test='APPENV=test ./vendor/phpunit/phpunit/phpunit ./tests'
