<?php
namespace Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus;
use Volante\SkyBukkit\Common\Src\General\Network\BaseRawMessage;
use Volante\SkyBukkit\GyroStatusService\Src\GyroStatus\GyroStatus;

/**
 * Class GyroStatusTest
 *
 * @package Volante\SkyBukkit\GyroStatusService\Tests\GyroStatus
 */
class GyroStatusTest extends \PHPUnit_Framework_TestCase
{
    public function test_toRawMessage_correct()
    {
        $expected = [
            'yaw'   => 1.11,
            'pitch' => 2.22,
            'roll'  => 3.33
        ];
        $gyroStatus = new GyroStatus(1.11, 3.33, 2.22);
        $result = $gyroStatus->toRawMessage();

        self::assertInstanceOf(BaseRawMessage::class, $result);
        self::assertEquals('gyroStatus', $result->getType());
        self::assertEquals('Gyro Status', $result->getTitle());
        self::assertEquals($expected, $result->getData());
    }
}