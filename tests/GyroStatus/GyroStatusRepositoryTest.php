<?php
namespace Volantus\GyroStatusService\Tests\GyroStatus;

use Ratchet\Client\WebSocket;
use Volantus\FlightBase\Src\Client\Server;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MSPRequestMessage;
use Volantus\FlightBase\Src\General\MSP\MSPResponseMessage;
use Volantus\GyroStatusService\Src\GyroStatus\GyroStatusRepository;
use Volantus\MSPProtocol\Src\Protocol\Request\Attitude;
use Volantus\MSPProtocol\Src\Protocol\Response\Attitude as AttitudeResponse;

/**
 * Class GyroStatusRepositoryTest
 *
 * @package Volantus\GyroStatusService\Tests\GyroStatus
 */
class GyroStatusRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebSocket|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionA;

    /**
     * @var Server
     */
    private $serverA;

    /**
     * @var WebSocket|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionB;

    /**
     * @var Server
     */
    private $serverB;

    /**
     * @var GyroStatusRepository
     */
    private $repository;

    protected function setUp()
    {
        $this->connectionA = $this->getMockBuilder(WebSocket::class)->disableOriginalConstructor()->getMock();
        $this->connectionB = $this->getMockBuilder(WebSocket::class)->disableOriginalConstructor()->getMock();
        $this->serverA = new Server($this->connectionA, Server::ROLE_RELAY_SERVER_A);
        $this->serverB = new Server($this->connectionB, Server::ROLE_RELAY_SERVER_B);

        $this->repository = new GyroStatusRepository([$this->serverA, $this->serverB]);
    }

    public function test_fetchGyroStatus_mspRequestCorrect()
    {
        $callback = function (string $data) {
            $data = json_decode($data, true);
            self::assertArrayHasKey('data', $data);
            self::assertNotEmpty($data['data']);

            /** @var MSPRequestMessage $mspMessage */
            $mspMessage = unserialize($data['data'][0]);
            self::assertInstanceOf(MSPRequestMessage::class, $mspMessage);
            self::assertEquals(new Attitude(), $mspMessage->getMspRequest());
            self::assertEquals(3, $mspMessage->getPriority());
        };

        $this->connectionA->expects(self::once())
            ->method('send')
            ->willReturnCallback($callback);

        $this->connectionB->expects(self::once())
            ->method('send')
            ->willReturnCallback($callback);

        $this->repository->fetchGyroStatus();
    }

    public function test_fetchGyroStatus_noConnection()
    {
        $this->repository->removeServer($this->serverA);

        $this->connectionA->expects(self::never())->method('send');
        $this->repository->fetchGyroStatus();
    }

    public function test_fetchGyroStatus_requestInProgress_bothConnections()
    {
        $this->repository->fetchGyroStatus();
        $this->connectionA->expects(self::never())->method('send');
        $this->connectionB->expects(self::never())->method('send');
        $this->repository->fetchGyroStatus();
    }

    public function test_fetchGyroStatus_requestInProgress_oneConnections()
    {
        /** @var AttitudeResponse|\PHPUnit_Framework_MockObject_MockObject $attitudeResponse */
        $attitudeResponse = $this->getMockBuilder(AttitudeResponse::class)->disableOriginalConstructor()->getMock();
        $message = new MSPResponseMessage('test', $attitudeResponse);

        $this->repository->fetchGyroStatus();
        $this->repository->onMspResponse($this->serverB, $message);

        $this->connectionA->expects(self::never())->method('send');
        $this->connectionB->expects(self::once())->method('send');
        $this->repository->fetchGyroStatus();
    }

    public function test_onMspResponse_decodedCorrectly()
    {
        /** @var AttitudeResponse|\PHPUnit_Framework_MockObject_MockObject $attitudeResponse */
        $attitudeResponse = $this->getMockBuilder(AttitudeResponse::class)->disableOriginalConstructor()->getMock();
        $attitudeResponse->method('getXAngle')->willReturn(-1525);
        $attitudeResponse->method('getYAngle')->willReturn(700);
        $attitudeResponse->method('getHeading')->willReturn(120);

        $message = new MSPResponseMessage('test', $attitudeResponse);
        $result = $this->repository->onMspResponse($this->serverA, $message);

        self::assertInstanceOf(GyroStatus::class, $result);
        self::assertEquals(-152.5, $result->getPitch());
        self::assertEquals(70, $result->getRoll());
        self::assertEquals(120, $result->getYaw());
    }

    public function test_onMspResponse_requestLockFreed()
    {
        $this->repository->fetchGyroStatus();

        /** @var AttitudeResponse|\PHPUnit_Framework_MockObject_MockObject $attitudeResponse */
        $attitudeResponse = $this->getMockBuilder(AttitudeResponse::class)->disableOriginalConstructor()->getMock();
        $message = new MSPResponseMessage('test', $attitudeResponse);
        $this->repository->onMspResponse($this->serverA, $message);

        $this->connectionA->expects(self::once())->method('send');

        $this->repository->fetchGyroStatus();
    }
}