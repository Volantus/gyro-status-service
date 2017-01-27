<?php
namespace Volante\SkyBukkit\GyroStatusService\Src\GyroStatus;

use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Volante\SkyBukkit\Common\Src\Server\Messaging\MessageServerService;
use Volante\SkyBukkit\Common\Src\Server\Messaging\MessageService;
use Volante\SkyBukkit\Common\Src\Server\Network\ClientFactory;

/**
 * Class GyroStatusBufferingService
 *
 * @package Volante\SkyBukkit\GyroStatusService\Src\GyroStatus
 */
class GyroStatusBufferingService extends MessageServerService
{
    /**
     * @var GyroStatusRepository
     */
    private $gyroStatusRepository;

    /**
     * GeoPositionBufferingService constructor.
     *
     * @param OutputInterface      $output
     * @param LoopInterface        $loop
     * @param MessageService|null  $messageService
     * @param ClientFactory|null   $clientFactory
     * @param GyroStatusRepository $gyroStatusRepository
     */
    public function __construct(OutputInterface $output, LoopInterface $loop, MessageService $messageService = null, ClientFactory $clientFactory = null, GyroStatusRepository $gyroStatusRepository = null)
    {
        parent::__construct($output, $messageService, $clientFactory);

        $this->gyroStatusRepository = $gyroStatusRepository ?: new GyroStatusRepository($output);
        $loop->addPeriodicTimer(0.0001, function () {
            $this->sendStatus();
        });
    }

    public function sendStatus()
    {
        $gyroStatus = $this->gyroStatusRepository->getLatestStatus();
        $this->writeInfoLine('GyroStatusBufferingService', json_encode($gyroStatus));
    }
}