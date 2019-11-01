#test:
	#./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/shipsTest.php
	#./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/fieldTest.php
start:
	php -S localhost:5000
open:
	gnome-open http://localhost:5000
install:
	bash bin/installation

