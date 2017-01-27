<?php
namespace Volante\SkyBukkit\GyroStatusService\Src\GyroStatus;

use Volante\SkyBukkit\Common\Src\Client\OutgoingMessage;

/**
 * Class GyroStatus
 *
 * @package Volante\SkyBukkit\GyroStatusService\Src\GyroStatus
 */
class GyroStatus extends OutgoingMessage
{
    const TYPE = 'gyroStatus';

    /***
     * @var string
     */
    protected $type = self::TYPE;

    /**
     * @var string
     */
    protected $messageTitle = 'Gyro Status';

    /**
     * @var float
     */
    private $yaw;

    /**
     * @var float
     */
    private $pitch;

    /**
     * @var float
     */
    private $roll;

    /**
     * GyroStatus constructor.
     *
     * @param float $yaw
     * @param float $pitch
     * @param float $roll
     */
    public function __construct(float $yaw, float $pitch, float $roll)
    {
        $this->yaw = $yaw;
        $this->pitch = $pitch;
        $this->roll = $roll;
    }

    /**
     * @return float
     */
    public function getYaw(): float
    {
        return $this->yaw;
    }

    /**
     * @return float
     */
    public function getPitch(): float
    {
        return $this->pitch;
    }

    /**
     * @return float
     */
    public function getRoll(): float
    {
        return $this->roll;
    }


    /**
     * @return array
     */
    public function getRawData(): array
    {
        return [
            'yaw'   => $this->yaw,
            'pitch' => $this->pitch,
            'roll'  => $this->roll
        ];
    }
}