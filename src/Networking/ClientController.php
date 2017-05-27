<?php
namespace Volantus\GyroStatusService\Src\Networking;

use Symfony\Component\Console\Output\OutputInterface;
use Volantus\FlightBase\Src\Client\ClientService;
use Volantus\FlightBase\Src\Client\Server;

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
        parent::__construct($output, $service);

        $this->registerConnection(Server::ROLE_LOCAL_RELAY_SERVER, getenv('LOCAL_RELAY_SERVER'));
        $this->registerConnection(Server::ROLE_REMOTE_RELAY_SERVER, getenv('REMOTE_RELAY_SERVER'));

    }
}