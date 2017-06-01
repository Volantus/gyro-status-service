<?php
namespace Volantus\GyroStatusService\Src\Networking;

use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Volantus\FlightBase\Src\Client\MspClientService;
use Volantus\FlightBase\Src\Client\Server;
use Volantus\FlightBase\Src\General\Generic\IncomingGenericInternalMessage;
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
class MessageHandler extends MspClientService
{
    /**
     * @var int
     */
    protected $clientRole = ClientRole::GYRO_STATUS_SERVICE;

    /**
     * @var GyroStatusRepository
     */
    private $gyroStatusRepository;

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
        $this->gyroStatusRepository = $gyroStatusRepository ?: new GyroStatusRepository();
        $this->mspRepositories[] = $this->gyroStatusRepository;
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
                $gyroStatus = $this->gyroStatusRepository->onMspResponse($server, $payload);
                $this->sendToRelayServers($gyroStatus);
                $this->writeGreenLine('MessageHandler', 'Received MSP attitude response from server ' . $server->getRole());
            }
        }
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        parent::setLoop($loop);

        $this->loop->addPeriodicTimer(0.1, function () {
           $this->gyroStatusRepository->sendRequests();
            $this->writeInfoLine('MessageHandler', 'Sent MSP request for attitude');
        });
    }
}