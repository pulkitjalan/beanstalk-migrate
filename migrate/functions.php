<?php

use Pheanstalk\PheanstalkInterface;

function parseInput($argv) {
	$source = $argv[1];
	$destination = $argv[2];

	$source = explode(':', $source);
	$destination = explode(':', $destination);

	$return = ['source', 'destination'];
	$return['source']['host'] = $source[0];
	$return['source']['port'] = (isset($source[1])) ? $source[1] : PheanstalkInterface::DEFAULT_PORT;

	$return['destination']['host'] = $destination[0];
	$return['destination']['port'] = (isset($destination[1])) ? $destination[1] : PheanstalkInterface::DEFAULT_PORT;

	return $return;
}

function addToDest($pheanstalk, $job, $jobStats) {
	$tube = $jobStats['tube'];
	$data = $job->getData();
	$priority = $jobStats['pri'];
	$delay = $jobStats['time-left'];
	$ttr = $jobStats['ttr'];

	$pheanstalk->putInTube(
		$tube,
        $data,
        $priority,
        $delay,
        $ttr
    );
}

function process($pheanstalkSource, $pheanstalkDestination, $job) {
	$jobStats = $pheanstalkSource->statsJob($job);
	$pheanstalkSource->delete($job);
	addToDest($pheanstalkDestination, $job, $jobStats);
}

function processReady($pheanstalkSource, $pheanstalkDestination, $tube) {
	while ($ready = $pheanstalkSource->peekReady($tube)) {
		process($pheanstalkSource, $pheanstalkDestination, $ready);
	}
}

function processDelayed($pheanstalkSource, $pheanstalkDestination, $tube) {
	while ($delayed = $pheanstalkSource->peekDelayed($tube)) {
		process($pheanstalkSource, $pheanstalkDestination, $delayed);
	}
}