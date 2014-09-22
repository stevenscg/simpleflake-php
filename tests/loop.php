<?php

include 'SimpleFlake/SimpleFlake.php';

$s = new SimpleFlake();

$i = 0;
while ($i < 50) {
	echo number_format($s->simpleflake(), 0, '', '') . PHP_EOL;
	$i++;	
}
