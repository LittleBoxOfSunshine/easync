<?php
require 'node.php';

$eric = array(
	array('startTime' => 0, 'endTime' => 840),
	array('startTime' =>9910, 'endTime' => 3660),
	array('startTime' => 3750, 'endTime' => 4380)
);

$chris = array(
	array('startTime' => 0, 'endTime' => 840),
	array('startTime' =>9910, 'endTime' => 3660),
	array('startTime' => 3750, 'endTime' => 4380)
);

$events['smitheric95@gmail.com'] = $eric;
$events['cahenk95@gmail.com'] = $chris;

var_dump($events);

?>