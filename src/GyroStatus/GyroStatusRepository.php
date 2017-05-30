<?php
namespace Volantus\GyroStatusService\Src\GyroStatus;

use Volantus\FlightBase\Src\Client\Server;
use Volantus\FlightBase\Src\General\Generic\GenericInternalMessage;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MSPRequestMessage;
use Volantus\FlightBase\Src\General\MSP\MSPResponseMessage;
use Volantus\MSPProtocol\Src\Protocol\Request\Attitude as AttitudeRequest;
use Volantus\MSPProtocol\Src\Protocol\Response\Attitude as AttitudeResponse;

/**
 * Class GyroStatusRepository
 *
 * @package Volantus\GyroStatusService\Src\GyroStatus
 */
class GyroStatusRepository
{
    /**
     * Connections ready for MSP requests
     *
     * @var Server[]
     */
    private $freeConnections = [];

    /**
     * @var GyroStatus[]
     */
    private $currentGyroStatus = [];

    /**
     * GyroStatusRepository constructor.
     *
     * @param Server[] $connections
     */
    public function __construct(array $connections = [])
    {
        foreach ($connections as $connection) {
            $this->addServer($connection);
        }
    }

    public function fetchGyroStatus()
    {
        if (!empty($this->freeConnections)) {
            $request = new MSPRequestMessage(3, new AttitudeRequest());
            $request = new GenericInternalMessage($request);
            $request = $request->toRawMessage();
            $request = json_encode($request);

            foreach ($this->freeConnections as $objHash => $connection) {
                $connection->send($request);
                unset($this->freeConnections[$objHash]);
            }
        }
    }

    /**
     * @param Server             $server
     * @param MSPResponseMessage $message
     *
     * @return GyroStatus
     */
    public function onMspResponse(Server $server, MSPResponseMessage $message): GyroStatus
    {
        $objHash = spl_object_hash($server);
        $this->freeConnections[$objHash] = $server;

        /** @var AttitudeResponse $attitudeResponse */
        $attitudeResponse = $message->getMspResponse();
        $this->currentGyroStatus[$objHash] = new GyroStatus(
            $attitudeResponse->getHeading(),
            $attitudeResponse->getYAngle() / 10,
            $attitudeResponse->getXAngle() / 10
        );

        return $this->currentGyroStatus[$objHash];
    }

    /**
     * @param Server $server
     */
    public function addServer(Server $server)
    {
        $this->freeConnections[spl_object_hash($server)] = $server;
    }

    /**
     * @param Server $server
     */
    public function removeServer(Server $server)
    {
        unset($this->freeConnections[spl_object_hash($server)]);
    }

    /**
     * @return GyroStatus[]
     */
    public function getCurrentGyroStatus(): array
    {
        return array_values($this->currentGyroStatus);
    }
}