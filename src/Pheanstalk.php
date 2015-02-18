<?php namespace Beanstalk\Migrate;

use Pheanstalk\Pheanstalk as PdaPheanstalk;
use Pheanstalk\PheanstalkInterface;

class Pheanstalk extends PdaPheanstalk
{
    /**
     * The beanstalk host.
     *
     * @var string
     */
    protected $host;

    /**
     * The beanstalk host port.
     *
     * @var string
     */
    protected $port;

    /**
     * @param string $hostPort host:port
     */
    public function __construct($hostPort)
    {
        $this->parseInput($hostPort);

        parent::__construct($this->host, $this->port);
    }

    /**
     * Parse the host:port or host input.
     *
     * @return void
     */
    protected function parseInput($hostPort)
    {
        $hostPort = explode(':', $hostPort);

        $this->host = $hostPort[0];
        $this->port = (isset($hostPort[1])) ? $hostPort[1] : PheanstalkInterface::DEFAULT_PORT;
    }
}
