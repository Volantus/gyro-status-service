<?php
namespace Volantus\GyroStatusService\Tests\GyroStatus;

use Volantus\FlightBase\Src\General\Generic\GenericInternalMessage;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MSPRequestMessage;
use Volantus\FlightBase\Src\General\MSP\MSPResponseMessage;
use Volantus\FlightBase\Src\Server\Messaging\Sender;
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
     * @var Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var GyroStatusRepository
     */
    private $repository;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(Sender::class)->disableOriginalConstructor()->getMock();
        $this->repository = new GyroStatusRepository($this->connection);
    }

    public function test_fetchGyroStatus_mspRequestCorrect()
    {
        $this->connection->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (string $data) {
                $data = json_decode($data, true);
                self::assertArrayHasKey('data', $data);
                self::assertNotEmpty($data['data']);

                /** @var MSPRequestMessage $mspMessage */
                $mspMessage = unserialize($data['data'][0]);
                self::assertInstanceOf(MSPRequestMessage::class, $mspMessage);
                self::assertEquals(new Attitude(), $mspMessage->getMspRequest());
                self::assertEquals(3, $mspMessage->getPriority());
            });

        $this->repository->fetchGyroStatus();
    }

    public function test_fetchGyroStatus_noConnection()
    {
        $this->repository->setConnection(null);

        $this->connection->expects(self::never())->method('send');
        $this->repository->fetchGyroStatus();
    }

    public function test_fetchGyroStatus_requestInProgress()
    {
        $this->repository->fetchGyroStatus();
        $this->connection->expects(self::never())->method('send');
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
        $result = $this->repository->onMspResponse($message);

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
        $this->repository->onMspResponse($message);

        $this->connection->expects(self::once())->method('send');

        $this->repository->fetchGyroStatus();
    }
}