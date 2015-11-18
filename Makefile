cs:
	php-cs-fixer fix --verbose

test:
	phpunit -c phpunit.xml.dist
