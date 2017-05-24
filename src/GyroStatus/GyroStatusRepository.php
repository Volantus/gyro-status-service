<?php
namespace Volante\SkyBukkit\GyroStatusService\Src\GyroStatus;

use Symfony\Component\Console\Output\OutputInterface;
use Volantus\FlightBase\Src\General\CLI\OutputOperations;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\Network\Socket;

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
     * @var GyroStatus
     */
    private $zeroLevel;

    /**
     * GyroStatusRepository constructor.
     *
     * @param OutputInterface $output
     * @param GyroStatus      $zeroLevel
     * @param Socket          $socket
     */
    public function __construct(OutputInterface $output, GyroStatus $zeroLevel, Socket $socket = null)
    {
        $this->socket = $socket ?: new Socket('127.0.0.1', 5555);
        $this->output = $output;
        $this->zeroLevel = $zeroLevel;
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

        $yaw  = ((float) $values[0]) - $this->zeroLevel->getYaw();
        $roll = ((float) $values[2]) - $this->zeroLevel->getRoll();
        $pitch = (-(float) $values[1]) - $this->zeroLevel->getPitch();

        return new GyroStatus($yaw, $roll, $pitch);
    }
}