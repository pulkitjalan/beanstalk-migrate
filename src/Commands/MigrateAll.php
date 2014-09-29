<?php namespace Beanstalk\Migrate\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Beanstalk\Migrate\Pheanstalk;
use Pheanstalk\Exception\ServerException;

class MigrateAll extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all jobs from one server to another';

    /**
     * Source Pheanstalk instance
     *
     * @var Beanstalk\Migrate\Pheanstalk
     */
    protected $source;

    /**
     * Destination Pheanstalk instance
     *
     * @var Beanstalk\Migrate\Pheanstalk
     */
    protected $destination;

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'source',
                InputArgument::REQUIRED,
                'The source beanstalk server with or without port <beanstalk:port>'
            ],
            [
                'destination',
                InputArgument::REQUIRED,
                'The destination beanstalk server with or without port <beanstalk:port>'
            ]
        ];
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    protected function fire()
    {
        $this->source = new Pheanstalk($this->argument('source'));
        $this->destination = new Pheanstalk($this->argument('destination'));

        // get a list of all tubes
        $tubes = $this->source->listTubes();

        // process ready and delayed jobs only
        foreach(['Ready', 'Delayed'] as $peek) {
            foreach($tubes as $tube) {
                try {
                    while ($job = $this->source->{"peek$peek"}($tube)) {
                        $this->process($job);
                    }
                } catch (ServerException $e) {
                    // catch and ignore ServerException
                    // thrown when no more items are
                    // found in the queue
                }
            }
        }
    }

    /**
     * Process a job by getting the job stats
     * delete it from the source and push it
     * the destination beanstalk
     *
     * @param Pheanstalk\Job $job job to process
     *
     * @return void
     */
    protected function process($job) {
        // get stats from the job to migrate
        $stats = $this->source->statsJob($job);
        // delete the job from the source server
        $this->source->delete($job);
        // add to destination server
        $this->addToDest($job, $stats);
    }

    /**
     * Take a job, its stats and add it to the
     * destination beanstalk instance
     *
     * @param Pheanstalk\Job $job job to process
     * @param array $jobStats Stats about the job
     */
    protected function addToDest($job, $stats) {
        // add the job to the destination server
        $this->destination->putInTube(
            $stats['tube'],
            $job->getData(),
            $stats['pri'],
            $stats['time-left'], // use time left as delay
            $stats['ttr']
        );
    }

}