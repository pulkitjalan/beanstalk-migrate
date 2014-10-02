<?php namespace Beanstalk\Migrate\Commands;

use Pheanstalk\Exception\ServerException;

class MigrateAll extends AbstractCommand {

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
     * Execute the command.
     *
     * @return void
     */
    protected function fire()
    {
        parent::fire();

        // get a list of all tubes
        $tubes = $this->source->listTubes();

        // process ready and delayed jobs only
        foreach(['Ready', 'Delayed'] as $type) {
            foreach($tubes as $tube) {
                $this->info('Processing "'.$type.'" jobs in "'.$tube.'" tube');
                try {
                    while ($job = $this->source->{"peek$type"}($tube)) {
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

}