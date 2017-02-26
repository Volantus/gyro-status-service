<?php
namespace Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus;

use Symfony\Component\Console\Output\OutputInterface;
use Volante\SkyBukkit\Common\Src\General\GyroStatus\GyroStatus;
use Volante\SkyBukkit\Common\Src\General\Network\Socket;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatusRepository;

/**
 * Class GyroStatusRepositoryTest
 *
 * @package Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus
 */
class GyroStatusRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GyroStatusRepository
     */
    private $repository;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputInterface;

    /**
     * @var Socket|\PHPUnit_Framework_MockObject_MockObject
     */
    private $socket;

    protected function setUp()
    {
        $this->socket = $this->getMockBuilder(Socket::class)->disableOriginalConstructor()->getMock();
        $this->outputInterface = $this->getMockBuilder(OutputInterface::class)->disableOriginalConstructor()->getMock();

        $this->repository = new GyroStatusRepository($this->outputInterface, $this->socket);
    }

    public function test_getLatestStatus_singleSegment_correct()
    {
        $this->outputInterface->expects(self::never())->method('writeln');
        $this->outputInterface->expects(self::never())->method('write');
        $this->socket->expects(self::once())->method('listen')
            ->willReturn('1.1 2.2 3.3,abc');
        $result = $this->repository->getLatestStatus();

        self::assertInstanceOf(GyroStatus::class, $result);
        self::assertEquals(1.1, $result->getYaw());
        self::assertEquals(2.2, $result->getPitch());
        self::assertEquals(3.3, $result->getRoll());
    }

    public function test_getLatestStatus_multipleSegments_latestSegmentOnlyAndLogMessage()
    {
        $this->outputInterface->expects(self::once())->method('writeln')
            ->will(self::returnCallback(function ($message) {
                self::assertStringEndsWith('<error>Hanging back! Received 2 segments from the socket buffer. Will only return the latest status.</error>', $message);
            }));

        $this->socket->expects(self::once())->method('listen')
            ->willReturn('0 0 0,1.1 2.2 3.3,abc');

        $result = $this->repository->getLatestStatus();
        self::assertInstanceOf(GyroStatus::class, $result);
        self::assertEquals(1.1, $result->getYaw());
        self::assertEquals(2.2, $result->getPitch());
        self::assertEquals(3.3, $result->getRoll());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid low level gyro message: Missing message separator
     */
    public function test_getLatestStatus_invalidData_missingSeparator()
    {
        $this->socket->expects(self::once())->method('listen')
            ->willReturn('');

        $this->repository->getLatestStatus();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid low level gyro message: Value Count is not correct!
     */
    public function test_getLatestStatus_invalidData_valueMissing()
    {
        $this->socket->expects(self::once())->method('listen')
            ->willReturn('1.1 2.2,abc');

        $this->repository->getLatestStatus();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid low level gyro message: Value 0 is not numeric!
     */
    public function test_getLatestStatus_invalidData_firstValueNotFloat()
    {
        $this->socket->expects(self::once())->method('listen')
            ->willReturn('abc 1 2,');

        $this->repository->getLatestStatus();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid low level gyro message: Value 1 is not numeric!
     */
    public function test_getLatestStatus_invalidData_secondValueNotFloat()
    {
        $this->socket->expects(self::once())->method('listen')
            ->willReturn('1 abc 2,');

        $this->repository->getLatestStatus();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid low level gyro message: Value 2 is not numeric!
     */
    public function test_getLatestStatus_invalidData_thirdValueNotFloat()
    {
        $this->socket->expects(self::once())->method('listen')
            ->willReturn('1 2 abc,');

        $this->repository->getLatestStatus();
    }
}