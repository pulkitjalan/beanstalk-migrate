<?php namespace Beanstalk\Migrate\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Beanstalk\Migrate\Pheanstalk;
use Pheanstalk\Job;
use Exception;

class AbstractCommand extends Command {

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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'force',
                null,
                InputOption::VALUE_NONE,
                'Force the operation to run'
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
        if (!$this->option('force')) $this->validate();

        $this->source = new Pheanstalk($this->argument('source'));
        $this->destination = new Pheanstalk($this->argument('destination'));

        $this->process();
    }

    /**
     * Process
     *
     * @return void
     */
    protected function process()
    {
        throw new Exception('Method not implemented!');
    }

    /**
     * Validate the input
     *
     * @return void
     */
    protected function validate()
    {
        if ($this->argument('source') == $this->argument('destination')) {
            $message = 'Looks like you have selected the same "source" and "destination". ';
            $message .= 'This will cause the process to run endlessly. ';
            $message .= 'Are you sure you want to continue?';

            // confirm if the user wants to continue, default to no
            if (!$this->confirm($message, false)) exit;
        }
    }

    /**
     * Migrate a job by getting the job stats
     * delete it from the source and push it
     * the destination beanstalk
     *
     * @param Pheanstalk\Job    $job job to process
     *
     * @return void
     */
    protected function migrateJob(Job $job)
    {
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
     * @param Pheanstalk\Job    $job job to process
     * @param array             $stats Stats about the job
     *
     * @return void
     */
    protected function addToDest(Job $job, array $stats)
    {
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