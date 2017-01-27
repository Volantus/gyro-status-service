<?php
namespace Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus;

use React\EventLoop\LoopInterface;
use Volante\SkyBukkit\Common\Src\Server\Messaging\MessageServerService;
use Volante\SkyBukkit\Common\Tests\Server\Messaging\MessageServerServiceTest;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatusBufferingService;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatusRepository;

/**
 * Class GyroStatusBufferingServiceTest
 *
 * @package Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus
 */
class GyroStatusBufferingServiceTest extends MessageServerServiceTest
{
    /**
     * @var GyroStatusRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loopInterface;

    protected function setUp()
    {
        /** @var LoopInterface $loopInterface */
        $this->loopInterface = $this->getMockBuilder(LoopInterface::class)->getMock();
        $this->repository = $this->getMockBuilder(GyroStatusRepository::class)->disableOriginalConstructor()->getMock();
        parent::setUp();
    }

    protected function createService(): MessageServerService
    {
        return new GyroStatusBufferingService($this->dummyOutput, $this->loopInterface, $this->messageService, $this->clientFactory, $this->repository);
    }

//    public function test_sendPosition_positionRefreshedAndSend()
//    {
//        $connection = $this->getMockBuilder(ConnectionInterface::class)->disableOriginalConstructor()->getMock();
//        $connection->expects(self::once())->method('send')->with('dsfdsfdsfsdf');
//        /** @var ConnectionInterface $connection */
//        $this->clientFactory->method('get')->willReturn(new Client(1, $connection, ClientRole::STATUS_BROKER));
//
//        $this->geoPositionRepository->expects(self::once())->method('refresh');
//        $this->geoPositionRepository->expects(self::once())->method('getCurrentPosition')->willReturn(new GeoPosition(1, 2, 3));
//
//        $this->service->newClient(new DummyConnection());
//        $this->service->sendPosition();
//    }
}