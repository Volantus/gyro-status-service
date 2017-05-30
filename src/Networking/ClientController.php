<?php
namespace Volantus\GyroStatusService\Src\Networking;

use Symfony\Component\Console\Output\OutputInterface;
use Volantus\FlightBase\Src\Client\ClientService;
use Volantus\FlightBase\Src\Client\Server;
use Volantus\FlightBase\Src\Server\Messaging\MessageService;

/**
 * Class ClientController
 *
 * @package Volantus\GyroStatusService\Src\Networking
 */
class ClientController extends \Volantus\FlightBase\Src\Client\ClientController
{
    /**
     * ClientController constructor.
     *
     * @param OutputInterface    $output
     * @param ClientService|null $service
     */
    public function __construct(OutputInterface $output, ClientService $service = null)
    {
        parent::__construct($output, $service ?: new MessageHandler($output, new MessageService()));

        $this->connectRelayServer();
        $this->connectMspServer();
    }

    private function connectRelayServer()
    {
        $connectionCountBefore = count($this->connections);

        if (getenv('RELAY_SERVER_A') !== false) {
            $this->registerConnection(Server::ROLE_RELAY_SERVER_A, getenv('RELAY_SERVER_A'));
        }

        if (getenv('RELAY_SERVER_B') !== false) {
            $this->registerConnection(Server::ROLE_RELAY_SERVER_B, getenv('RELAY_SERVER_B'));
        }

        if ($connectionCountBefore == count($this->connections)) {
            throw new \RuntimeException('Atleast one relay server needs to be configured');
        }
    }

    private function connectMspServer()
    {
        $connectionCountBefore = count($this->connections);

        if (getenv('MSP_SERVER_A') !== false) {
            $this->registerConnection(Server::ROLE_MSP_BROKER_A, getenv('MSP_SERVER_A'));
        }

        if (getenv('MSP_SERVER_B') !== false) {
            $this->registerConnection(Server::ROLE_MSP_BROKER_B, getenv('MSP_SERVER_B'));
        }

        if ($connectionCountBefore == count($this->connections)) {
            throw new \RuntimeException('Atleast one MSP server needs to be configured');
        }
    }
}