<?php
namespace Volantus\GyroStatusService\Src\Networking;

use Volantus\FlightBase\Src\Client\ClientService;
use Volantus\FlightBase\Src\General\Role\ClientRole;

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

}