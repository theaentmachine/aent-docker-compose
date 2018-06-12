<?php

namespace TheAentMachine\AentDockerCompose\Service\Enum;

class VolumeTypeEnum
{
    public const VOLUME = 'volume';
    public const BIND = 'bind';
    public const TMPFS = 'tmpfs';

    /**
     * @return string[]
     */
    public static function getVolumeTypes(): array
    {
        return array(self::VOLUME, self::BIND, self::TMPFS);
    }
}
