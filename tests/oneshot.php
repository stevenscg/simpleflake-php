<?php

include 'SimpleFlake/SimpleFlake.php';

$s = new SimpleFlake();
$flake = $s->simpleflake();
echo $flake.PHP_EOL;
var_dump($s->parse_simpleflake($flake)).PHP_EOL;
return;
