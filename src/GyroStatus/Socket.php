<?php
namespace Volante\SkyBukkit\GyroStatusService\Src\GyroStatus;

/**
 * Class Socket
 *
 * @package Volante\SkyBukkit\GyroStatusService\Src\GyroStatus
 */
class Socket
{
    /**
     * @var resource
     */
    private $connection;

    /**
     * Socket constructor.
     *
     * @param string $address
     * @param int    $port
     */
    public function __construct(string $address, int $port)
    {
        $this->connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->connection === false) {
            throw new SocketException('socket_create() failed: ' . socket_strerror(socket_last_error()));
        }

        $result = socket_connect($this->connection, $address, $port);
        if ($result === false) {
            throw new SocketException("socket_connect() failed ($result):" . socket_strerror(socket_last_error($this->connection)));
        }
    }

    /**
     * @return string
     */
    public function listen() : string
    {
        $fullData = socket_read($this->connection, 64);

        // Checking for more data
        socket_set_nonblock($this->connection);
        while ($moreData = socket_read($this->connection, 64)) {
            $fullData .= $moreData;
        }
        socket_set_block($this->connection);

        return $fullData;
    }

    public function __destruct()
    {
        socket_close($this->connection);
    }
}