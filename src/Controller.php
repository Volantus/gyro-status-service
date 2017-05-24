<?php
namespace Volante\SkyBukkit\GyroStatusService\Src;

use Symfony\Component\Console\Output\OutputInterface;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatusBufferingService;

/**
 * Class Controller
 * @package Volante\SkyBukkit\GeoPositionService\Src
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