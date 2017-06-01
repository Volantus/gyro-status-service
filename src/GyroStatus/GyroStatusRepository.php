<?php
namespace Volantus\GyroStatusService\Src\GyroStatus;

use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MspRepository;
use Volantus\MSPProtocol\Src\Protocol\Request\Attitude as AttitudeRequest;
use Volantus\MSPProtocol\Src\Protocol\Request\Attitude;
use Volantus\MSPProtocol\Src\Protocol\Request\Request;
use Volantus\MSPProtocol\Src\Protocol\Response\Attitude as AttitudeResponse;
use Volantus\MSPProtocol\Src\Protocol\Response\Response;

/**
 * Class GyroStatusRepository
 *
 * @package Volantus\GyroStatusService\Src\GyroStatus
 */
class GyroStatusRepository extends MspRepository
{
    /**
     * @var int
     */
    protected $priority = 3;

    /**
     * @return Request
     */
    protected function createMspRequest(): Request
    {
        return new AttitudeRequest();
    }

    /**
     * @param Response|AttitudeResponse $response
     *
     * @return mixed
     */
    protected function decodeResponse(Response $response)
    {
        return new GyroStatus(
            $response->getHeading(),
            $response->getXAngle() / 10,
            $response->getYAngle() / 10
        );
    }
}