<?php
namespace Volante\SkyBukkit\GyroStatusService\Src\Commands;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Volante\SkyBukkit\GyroStatusService\Src\Controller;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatusBufferingService;

/**
 * Class ServerCommand
 * @package Volante\SkyBukkit\GeoPositionService\Src\Commands
 */
class ServerCommand extends Command
{
    protected function configure()
    {
        $this->setName('server');
        $this->setDescription('Runs the gyro status service');

        $this->addOption('port', 'p', InputArgument::OPTIONAL, 'Port of the webSocket', 5002);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop   = LoopFactory::create();
        $socket = new Reactor($loop);
        $socket->listen($input->getOption('port'), '127.0.0.1');

        $service = new GyroStatusBufferingService($output, $loop);
        $controller = new Controller($output, $service);

        $server = new IoServer(new HttpServer(new WsServer($controller)), $socket, $loop);
        $server->run();
    }
}