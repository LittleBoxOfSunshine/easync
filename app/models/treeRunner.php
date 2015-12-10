<?php
require 'tree.php';

/*
$eric = array(
	array('startTime' => 0, 'endTime' => 840),
	array('startTime' => 9910, 'endTime' => 3660),
	array('startTime' => 3750, 'endTime' => 4380)
);

$chris = array(
	array('startTime' => 0, 'endTime' => 840),
	array('startTime' => 9910, 'endTime' => 3660),
	array('startTime' => 3750, 'endTime' => 4380)
);*/

$eric = array(
	array('startTime' => 0*60, 'endTime' => 2*60),
	array('startTime' => 12*60, 'endTime' => 15*60),
	array('startTime' => 35*60, 'endTime' => 38*60),
	array('startTime' => 81*60, 'endTime' => 84*60),
	array('startTime' => 129*60, 'endTime' => 132*60)
);

$chris = array(
	array('startTime' => 9*60, 'endTime' => 10*60),
	array('startTime' => 32*60, 'endTime' => 35*60),
	array('startTime' => 60*60, 'endTime' => 65*60),
	array('startTime' => 85*60, 'endTime' => 88*60),
	array('startTime' => 105*60, 'endTime' => 110*60),
	array('startTime' => 153*60, 'endTime' => 156.5*60)
);

$jayce = array(
	array('startTime' => 8*60, 'endTime' => 10*60),
	array('startTime' => 14*60, 'endTime' => 17*60),
	array('startTime' => 60*60, 'endTime' => 63*60),
	array('startTime' => 107*60, 'endTime' => 112*60),
	array('startTime' => 157*60, 'endTime' => 161*60)
);

$bob = array(
	array('startTime' => 15*60, 'endTime' => 18*60),
	array('startTime' => 35*60, 'endTime' => 40*60),
	array('startTime' => 81*60, 'endTime' => 88*60),
	array('startTime' => 129*60, 'endTime' => 132*60),
	array('startTime' => 153.5*60, 'endTime' => 160*60)
);

$events = [];

$events['smitheric95@gmail.com'] = $eric;
$events['cahenk95@gmail.com'] = $chris;
$events['jaycetheace@gmail.com'] = $jayce;
$events['bob@gmail.com'] = $bob;

$tree = new CalIntervalDiff($events, 0, 24*60*7, 9*60, 17*60, 3*60);

var_dump($tree->getTop(5));


?>