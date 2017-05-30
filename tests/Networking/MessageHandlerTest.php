<?php
namespace Volantus\GyroStatusService\Src\Networking;

use Ratchet\Client\WebSocket;
use Volantus\FlightBase\Src\Client\ClientService;
use Volantus\FlightBase\Src\Client\Server;
use Volantus\FlightBase\Src\General\Generic\IncomingGenericInternalMessage;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MSPResponseMessage;
use Volantus\FlightBase\Src\General\Role\ClientRole;
use Volantus\FlightBase\Tests\Client\ClientServiceTest;
use Volantus\GyroStatusService\Src\GyroStatus\GyroStatusRepository;
use Volantus\MSPProtocol\Src\Protocol\Response\Attitude;

/**
 * Class MessageHandlerTest
 *
 * @package Volantus\GyroStatusService\Src\Networking
 */
class MessageHandlerTest extends ClientServiceTest
{
    /**
     * @var GyroStatusRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = $this->getMockBuilder(GyroStatusRepository::class)->disableOriginalConstructor()->getMock();
        parent::setUp();
    }

    /**
     * @return ClientService
     */
    protected function createService(): ClientService
    {
        return new MessageHandler($this->dummyOutput, $this->messageService, $this->repository);
    }

    /**
     * @return int
     */
    protected function getExpectedClientRole(): int
    {
        return ClientRole::GYRO_STATUS_SERVICE;
    }

    public function test_newMessage_mspResponseHandledCorrectly()
    {
        /** @var Attitude|\PHPUnit_Framework_MockObject_MockObject $attitude */
        $attitude = $this->getMockBuilder(Attitude::class)->disableOriginalConstructor()->getMock();
        $mspResponse = new MSPResponseMessage('test', $attitude);
        $message = new IncomingGenericInternalMessage($this->server, $mspResponse);

        $this->messageService->expects(self::once())
            ->method('handle')
            ->with($this->server, 'correct')->willReturn($message);

        $this->repository->expects(self::once())
            ->method('onMspResponse')
            ->with(self::equalTo($this->server), self::equalTo($mspResponse))
            ->willReturn(new GyroStatus(1, 2, 3));

        $this->service->addServer($this->server);
        $this->service->newMessage($this->connection, 'correct');
    }

    public function test_newMessage_gyroStatusSentToRelayServers()
    {
        /** @var WebSocket|\PHPUnit_Framework_MockObject_MockObject $relayServerConnection */
        $relayServerConnection = $this->getMockBuilder(WebSocket::class)->disableOriginalConstructor()->getMock();
        /** @var WebSocket|\PHPUnit_Framework_MockObject_MockObject $mspServerConnection */
        $mspServerConnection = $this->getMockBuilder(WebSocket::class)->disableOriginalConstructor()->getMock();

        $mspServer = new Server($mspServerConnection, Server::ROLE_MSP_BROKER_A);
        $relayServer = new Server($relayServerConnection, Server::ROLE_RELAY_SERVER_A);

        $this->service->addServer($mspServer);
        $this->service->addServer($relayServer);

        /** @var Attitude|\PHPUnit_Framework_MockObject_MockObject $attitude */
        $attitude = $this->getMockBuilder(Attitude::class)->disableOriginalConstructor()->getMock();
        $mspResponse = new MSPResponseMessage('test', $attitude);
        $message = new IncomingGenericInternalMessage($mspServer, $mspResponse);

        $this->messageService->expects(self::once())
            ->method('handle')
            ->willReturn($message);

        $this->repository->method('onMspResponse')->willReturn(new GyroStatus(1, 2, 3));

        $relayServerConnection->expects(self::once())
            ->method('send')
            ->with(self::equalTo('{"type":"gyroStatus","title":"Gyro Status","data":{"yaw":1,"pitch":3,"roll":2}}'));

        $mspServerConnection->expects(self::never())
            ->method('send');

        $this->service->newMessage($mspServerConnection, 'correct');
    }

    public function test_addServer_mspServer_addedToRepository()
    {
        $server = new Server($this->connection, Server::ROLE_MSP_BROKER_A);

        $this->repository->expects(self::once())
            ->method('addServer')
            ->with(self::equalTo($server));

        $this->service->addServer($server);
    }

    public function test_addServer_relayServer_notAddedToRepository()
    {
        $server = new Server($this->connection, Server::ROLE_RELAY_SERVER_A);

        $this->repository->expects(self::never())
            ->method('addServer');

        $this->service->addServer($server);
    }

    public function test_removeServer_mspServer_removedFromRepository()
    {
        $server = new Server($this->connection, Server::ROLE_MSP_BROKER_A);

        $this->repository->expects(self::once())
            ->method('removeServer')
            ->with(self::equalTo($server));

        $this->service->removeServer($server);
    }

    public function test_removeServer_relayServer_repositoryNotCalled()
    {
        $server = new Server($this->connection, Server::ROLE_RELAY_SERVER_A);

        $this->repository->expects(self::never())
            ->method('removeServer');

        $this->service->removeServer($server);
    }
}