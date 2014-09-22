<?php

include 'SimpleFlake/SimpleFlake.php';

$s = new SimpleFlake();

$i = 0;
$storage = array();

while ($i < 500) {
    $flake = new SimpleFlake();
    if(array_key_exists('x'.$flake, $storage)){
        echo "Collision!";
        exit(1);
    }
    $storage['x'.$flake] = null;

    echo $flake . PHP_EOL;
    $i++;
}