<?php
namespace Volantus\GyroStatusService\Src;

use Symfony\Component\Console\Output\OutputInterface;
use Volantus\GyroStatusService\Src\GyroStatus\GyroStatusBufferingService;

/**
 * Class Controller
 * @package Volantus\GeoPositionService\Src
 */
class Controller extends \Volantus\FlightBase\Src\Server\Controller
{
    /**
     * Controller constructor.
     *
     * @param OutputInterface            $output
     * @param GyroStatusBufferingService $gyroStatusBufferingService
     */
    public function __construct(OutputInterface $output, GyroStatusBufferingService $gyroStatusBufferingService)
    {
        parent::__construct($output, $gyroStatusBufferingService);
    }
}