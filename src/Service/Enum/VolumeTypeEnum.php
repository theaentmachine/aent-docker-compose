<?php

namespace TheAentMachine\AentDockerCompose\Service\Enum;

class VolumeTypeEnum
{
    const VOLUME = 'volume';
    const BIND = 'bind';
    const TMPFS = 'tmpfs';

    /**
     * @return string[]
     */
    public static function getVolumeTypes() : array
    {
        return array(VolumeTypeEnum::VOLUME, VolumeTypeEnum::BIND, VolumeTypeEnum::TMPFS);
    }
}
