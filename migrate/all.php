<?php

use Pheanstalk\Pheanstalk;

require __DIR__ . '/../vendor/autoload.php';

$input = parseInput($argv);

$pheanstalkSource = new Pheanstalk($input['source']['host'], $input['source']['port']);
$pheanstalkDestination = new Pheanstalk($input['destination']['host'], $input['destination']['port']);

$tubes = $pheanstalkSource->listTubes();

foreach($tubes as $tube) {
	try {
		processReady($pheanstalkSource, $pheanstalkDestination, $tube);
	} catch (Exception $e) {
		// ignore
	}

	try {
		processDelayed($pheanstalkSource, $pheanstalkDestination, $tube);
	} catch (Exception $e) {
		// ignore
	}
}