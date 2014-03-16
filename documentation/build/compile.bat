@echo off

php ./script.php > ./sources/compiled/script.json
php ./events.php > ./sources/compiled/events.json
php ./methods.php > ./sources/compiled/methods.json

php ./compile-html.php > ../documentation.html