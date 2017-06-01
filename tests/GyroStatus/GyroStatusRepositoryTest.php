<?php
namespace Volantus\GyroStatusService\Tests\GyroStatus;

use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MspRepository;
use Volantus\FlightBase\Tests\General\MSP\MspRepositoryTest;
use Volantus\GyroStatusService\Src\GyroStatus\GyroStatusRepository;
use Volantus\MSPProtocol\Src\Protocol\Request\Attitude;
use Volantus\MSPProtocol\Src\Protocol\Request\Request;
use Volantus\MSPProtocol\Src\Protocol\Response\Attitude as AttitudeResponse;
use Volantus\MSPProtocol\Src\Protocol\Response\Response;

/**
 * Class GyroStatusRepositoryTest
 *
 * @package Volantus\GyroStatusService\Tests\GyroStatus
 */
class GyroStatusRepositoryTest extends MspRepositoryTest
{
    /**
     * @return MspRepository
     */
    protected function createRepository(): MspRepository
    {
        return new GyroStatusRepository([$this->serverA, $this->serverB]);
    }

    /**
     * @return int
     */
    protected function getExpectedPriority(): int
    {
        return 3;
    }

    /**
     * @return Request
     */
    protected function getExpectedMspRequest(): Request
    {
        return new Attitude();
    }

    /**
     * @return Response
     */
    protected function getCorrectMspResponse(): Response
    {
        /** @var AttitudeResponse|\PHPUnit_Framework_MockObject_MockObject $attitudeResponse */
        $attitudeResponse = $this->getMockBuilder(AttitudeResponse::class)->disableOriginalConstructor()->getMock();
        $attitudeResponse->method('getXAngle')->willReturn(-1525);
        $attitudeResponse->method('getYAngle')->willReturn(700);
        $attitudeResponse->method('getHeading')->willReturn(120);

        return $attitudeResponse;
    }

    /**
     * @return mixed
     */
    protected function getExpectedDecodedResult()
    {
        return new GyroStatus(120, -152.5, 70);
    }
}