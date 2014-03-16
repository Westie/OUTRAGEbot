<?php

require "_compiler.php";

$_events = get_registered_events();
$_methods = get_registered_methods();

require "templates/index.php";
exit;