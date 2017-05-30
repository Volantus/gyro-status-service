<?php
namespace Volantus\GyroStatusService\Src\GyroStatus;

use Volantus\FlightBase\Src\General\Generic\GenericInternalMessage;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MSPRequestMessage;
use Volantus\FlightBase\Src\General\MSP\MSPResponseMessage;
use Volantus\FlightBase\Src\Server\Messaging\Sender;
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
     * @var Sender
     */
    private $connection;

    /**
     * @var bool
     */
    private $requestInProgress = false;

    /**
     * @var GyroStatus
     */
    private $currentGyroStatus;

    /**
     * GyroStatusRepository constructor.
     *
     * @param Sender|null $connection
     */
    public function __construct(Sender $connection = null)
    {
        $this->connection = $connection;
    }

    public function fetchGyroStatus()
    {
        if ($this->connection && !$this->requestInProgress) {
            $this->requestInProgress = true;

            $request = new MSPRequestMessage(3, new AttitudeRequest());
            $request = new GenericInternalMessage($request);
            $request = $request->toRawMessage();
            $this->connection->send(json_encode($request));
        }
    }

    /**
     * @param MSPResponseMessage $message
     *
     * @return GyroStatus
     */
    public function onMspResponse(MSPResponseMessage $message): GyroStatus
    {
        $this->requestInProgress = false;

        /** @var AttitudeResponse $attitudeResponse */
        $attitudeResponse = $message->getMspResponse();
        $this->currentGyroStatus = new GyroStatus(
            $attitudeResponse->getHeading(),
            $attitudeResponse->getYAngle() / 10,
            $attitudeResponse->getXAngle() / 10
        );

        return $this->currentGyroStatus;
    }

    /**
     * @param Sender $connection
     */
    public function setConnection(Sender $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * @return GyroStatus
     */
    public function getCurrentGyroStatus(): GyroStatus
    {
        return $this->currentGyroStatus;
    }
}