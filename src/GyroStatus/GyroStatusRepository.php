<?php
namespace Volante\SkyBukkit\GyroStatusService\Src\GyroStatus;

use Symfony\Component\Console\Output\OutputInterface;
use Volante\SkyBukkit\Common\Src\General\CLI\OutputOperations;
use Volante\SkyBukkit\Common\Src\General\GyroStatus\GyroStatus;
use Volante\SkyBukkit\Common\Src\General\Network\Socket;

/**
 * Class GyroStatusRepository
 *
 * @package Volante\SkyBukkit\GyroStatusService\Src\GyroStatus
 */
class GyroStatusRepository
{
    use OutputOperations;

    /**
     * @var Socket
     */
    private $socket;

    /**
     * GyroStatusRepository constructor.
     *
     * @param OutputInterface $output
     * @param Socket          $socket
     */
    public function __construct(OutputInterface $output, Socket $socket = null)
    {
        $this->socket = $socket ?: new Socket('127.0.0.1', 5555);
        $this->output = $output;
    }

    /**
     * @return GyroStatus
     */
    public function getLatestStatus() : GyroStatus
    {
        $data = $this->socket->listen();

        $segments = explode(',', $data);
        if (count($segments) < 2) {
            throw new \InvalidArgumentException('Invalid low level gyro message: Missing message separator');
        }
        array_pop($segments);

        if (count($segments) > 1) {
            $this->writeErrorLine('GyroStatusRepository', 'Hanging back! Received ' . count($segments) . ' segments from the socket buffer. Will only return the latest status.');
        }

        $values = explode(' ', end($segments));

        if (count($values) != 3) {
            throw new \InvalidArgumentException('Invalid low level gyro message: Value Count is not correct!');
        }

        foreach ($values as $i => $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('Invalid low level gyro message: Value ' . $i . ' is not numeric!');
            }
        }

        return new GyroStatus((float) $values[0], (float) $values[2], -((float) $values[1]));
    }
}