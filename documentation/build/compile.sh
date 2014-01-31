#!/bin/sh

# generate fresh outputs
php ./script.php > ./output/script.json
php ./events.php > ./output/events.json
php ./methods.php > ./output/methods.json

# generate output
php ./compile-html.php > ../documentation.html