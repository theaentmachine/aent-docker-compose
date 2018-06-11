<?php

namespace TheAentMachine\AentDockerCompose\Service\Exception;

use TheAentMachine\AentDockerCompose\Service\Enum\VolumeTypeEnum;

class VolumeTypeException extends ServiceException
{
    /** @var string */
    private $invalidVolumeType;

    /**
     * VolumeTypeException constructor.
     * @param string $invalidVolumeType
     */
    public function __construct(string $invalidVolumeType)
    {
        $this->invalidVolumeType = $invalidVolumeType;
        parent::__construct($this->invalidVolumeType . " is not a valid volume type. Are accepted : " . VolumeTypeEnum::getVolumeTypes());
    }
}
