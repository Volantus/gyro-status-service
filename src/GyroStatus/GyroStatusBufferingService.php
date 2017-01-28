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
        $this->connect($gyroStatusRepository);

        $loop->addPeriodicTimer(0.0001, function () {
            $this->sendStatus();
        });
    }

    public function sendStatus()
    {
        $this->sandbox(function () {
            $gyroStatus = $this->gyroStatusRepository->getLatestStatus();
            $this->broadcastMessage($gyroStatus->toRawMessage());
        });
    }

    /**
     * @param callable $function
     */
    protected function sandbox(callable $function)
    {
        parent::sandbox(function () use ($function) {
            try {
                call_user_func($function);
            } catch (SocketException $e) {
                $this->writeErrorLine('GyroStatusBufferingService', $e->getMessage());
                $this->gyroStatusRepository = null;
                $this->connect();
            }
        });
    }

    /**
     * @param GyroStatusRepository|null $gyroStatusRepository
     */
    private function connect(GyroStatusRepository $gyroStatusRepository = null)
    {
        while ($this->gyroStatusRepository == null) {
            try {
                $this->gyroStatusRepository = $gyroStatusRepository ?: new GyroStatusRepository($this->output);
                $this->writeGreenLine('GyroStatusBufferingService', 'Connected successfully to low level gyro daemon!');
            } catch (\RuntimeException $e) {
                $this->writeErrorLine('GyroStatusRepository', $e->getMessage());
                sleep(1);
            }
        }
    }
}