<?php
namespace Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus;

use React\EventLoop\LoopInterface;
use Ratchet\ConnectionInterface;
use Volante\SkyBukkit\Common\Src\General\Role\ClientRole;
use Volante\SkyBukkit\Common\Src\Server\Messaging\MessageServerService;
use Volante\SkyBukkit\Common\Src\Server\Network\Client;
use Volante\SkyBukkit\Common\Tests\Server\General\DummyConnection;
use Volante\SkyBukkit\Common\Tests\Server\Messaging\MessageServerServiceTest;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatus;
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
     * @var GyroStatusBufferingService
     */
    protected $messageServerService;

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

    public function test_sendStatus_broadcastedCorrectly()
    {
        $connection = $this->getMockBuilder(ConnectionInterface::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::once())->method('send')->with('{"type":"gyroStatus","title":"Gyro Status","data":{"yaw":1,"pitch":3,"roll":2}}');
        /** @var ConnectionInterface $connection */
        $this->clientFactory->method('get')->willReturn(new Client(1, $connection, ClientRole::STATUS_BROKER));

        $this->repository->expects(self::once())->method('getLatestStatus')->willReturn(new GyroStatus(1, 2, 3));

        $this->messageServerService->newClient(new DummyConnection());
        $this->messageServerService->sendStatus();
    }
}