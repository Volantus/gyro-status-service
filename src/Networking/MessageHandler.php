<?php
namespace Volantus\GyroStatusService\Src\Networking;

use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Volantus\FlightBase\Src\Client\ClientService;
use Volantus\FlightBase\Src\Client\Server;
use Volantus\FlightBase\Src\General\Generic\IncomingGenericInternalMessage;
use Volantus\FlightBase\Src\General\GyroStatus\GyroStatus;
use Volantus\FlightBase\Src\General\MSP\MSPResponseMessage;
use Volantus\FlightBase\Src\General\Role\ClientRole;
use Volantus\FlightBase\Src\Server\Messaging\IncomingMessage;
use Volantus\FlightBase\Src\Server\Messaging\MessageService;
use Volantus\GyroStatusService\Src\GyroStatus\GyroStatusRepository;
use Volantus\MSPProtocol\Src\Protocol\Response\Attitude;

/**
 * Class MessageHandler
 *
 * @package Volantus\GyroStatusService\Src\GyroStatus
 */
class MessageHandler extends ClientService
{
    /**
     * @var int
     */
    protected $clientRole = ClientRole::GYRO_STATUS_SERVICE;

    /**
     * @var GyroStatusRepository
     */
    private $repository;

    /**
     * MessageHandler constructor.
     *
     * @param OutputInterface           $output
     * @param MessageService            $messageService
     * @param GyroStatusRepository|null $gyroStatusRepository
     */
    public function __construct(OutputInterface $output, MessageService $messageService, GyroStatusRepository $gyroStatusRepository = null)
    {
        parent::__construct($output, $messageService);
        $this->repository = $gyroStatusRepository ?: new GyroStatusRepository();
    }

    /**
     * @param IncomingMessage $incomingMessage
     */
    public function handleMessage(IncomingMessage $incomingMessage)
    {
        /** @var Server $server */
        $server = $incomingMessage->getSender();

        if ($incomingMessage instanceof IncomingGenericInternalMessage && $incomingMessage->getPayload() instanceof MSPResponseMessage) {
            /** @var MSPResponseMessage $payload */
            $payload = $incomingMessage->getPayload();

            if ($payload->getMspResponse() instanceof Attitude) {
                $gyroStatus = $this->repository->onMspResponse($server, $payload);
                $this->sendGyroStatus($gyroStatus);
                $this->writeGreenLine('MessageHandler', 'Received MSP attitude response from server ' . $server->getRole());
            }
        }
    }

    /**
     * @param GyroStatus $gyroStatus
     */
    private function sendGyroStatus(GyroStatus $gyroStatus)
    {
        $message = json_encode($gyroStatus->toRawMessage());

        if (isset($this->servers[Server::ROLE_RELAY_SERVER_A])) {
            $this->servers[Server::ROLE_RELAY_SERVER_A]->send($message);
        }

        if (isset($this->servers[Server::ROLE_RELAY_SERVER_B])) {
            $this->servers[Server::ROLE_RELAY_SERVER_B]->send($message);
        }
    }

    /**
     * @param Server $server
     */
    public function addServer(Server $server)
    {
        parent::addServer($server);

        if ($server->getRole() == Server::ROLE_MSP_BROKER_A || $server->getRole() == Server::ROLE_MSP_BROKER_B) {
            $this->repository->addServer($server);
        }
    }

    /**
     * @param Server $server
     */
    public function removeServer(Server $server)
    {
        parent::removeServer($server);

        if ($server->getRole() == Server::ROLE_MSP_BROKER_A || $server->getRole() == Server::ROLE_MSP_BROKER_B) {
            $this->repository->removeServer($server);
        }
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        parent::setLoop($loop);

        $this->loop->addPeriodicTimer(0.1, function () {
           $this->repository->fetchGyroStatus();
            $this->writeInfoLine('MessageHandler', 'Sent MSP request for attitude');
        });
    }
}