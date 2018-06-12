<?php

namespace TheAentMachine\AentDockerCompose\Service;

use TheAentMachine\AentDockerCompose\Service\Enum\VolumeTypeEnum;
use TheAentMachine\AentDockerCompose\Service\Exception\EmptyAttributeException;
use TheAentMachine\AentDockerCompose\Service\Exception\KeysMissingInArrayException;
use TheAentMachine\AentDockerCompose\Service\Exception\VolumeTypeException;

class Service
{

    /** @var string */
    protected $serviceName;
    /** @var string */
    protected $image;
    /** @var array */
    protected $internalPorts;
    /** @var array */
    protected $dependsOn;
    /** @var array */
    protected $ports;
    /** @var array */
    protected $labels;
    /** @var array */
    protected $environment;
    /** @var array */
    protected $volumes;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->serviceName = '';
        $this->image = '';
        $this->internalPorts = array();
        $this->dependsOn = array();
        $this->ports = array();
        $this->labels = array();
        $this->environment = array();
        $this->volumes = array();
    }


    /**
     * @param string $serviceName
     * @return Service
     */
    public function setServiceName(string $serviceName): Service
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * @param string $image
     * @return Service
     */
    public function setImage(string $image): Service
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param int[]|string[] $internalPorts
     * @return Service
     */
    public function setInternalPorts(array $internalPorts): Service
    {
        $this->internalPorts = $internalPorts;
        return $this;
    }

    /**
     * @param string[] $dependsOn
     * @return Service
     */
    public function setDependsOn(array $dependsOn): Service
    {
        $this->dependsOn = $dependsOn;
        return $this;
    }

    /**
     * @param string[] $ports
     * @return Service
     */
    public function setPorts(array $ports): Service
    {
        $this->ports = $ports;
        return $this;
    }

    /**
     * @param string[] $labels
     * @return Service
     */
    public function setLabels(array $labels): Service
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * @param string[] $environment
     * @return Service
     */
    public function setEnvironment(array $environment): Service
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param string[] $volumes
     * @return Service
     */
    public function setVolumes(array $volumes): Service
    {
        $this->volumes = $volumes;
        return $this;
    }

    /**
     * @param mixed[] $payload
     * @return Service
     * @throws EmptyAttributeException
     * @throws KeysMissingInArrayException
     * @throws VolumeTypeException
     */
    public static function parsePayload(array $payload): Service
    {
        $service = new Service();
        $service->serviceName = $payload['serviceName'] ?? '';
        $s = $payload['service'] ?? array();
        if (!empty($s)) {
            $service->image = $s['image'] ?? '';
            $service->internalPorts = $s['internalPorts'] ?? array();
            $service->dependsOn = $s['dependsOn'] ?? array();
            $service->ports = $s['ports'] ?? array();
            $service->labels = $s['labels'] ?? array();
            $service->environment = $s['environment'] ?? array();
            $service->volumes = $s['volumes'] ?? array();
        }
        $service->checkValidity(true);
        return $service;
    }

    /**
     * @param bool $throwException
     * @return bool
     * @throws EmptyAttributeException
     * @throws KeysMissingInArrayException
     * @throws VolumeTypeException
     */
    public function checkValidity(bool $throwException = false): bool
    {
        if (empty($this->serviceName)) {
            if ($throwException) {
                throw new EmptyAttributeException('serviceName');
            }
            return false;
        }

        $wantedKeys = array('source', 'target');
        foreach ($this->ports as $arr) {
            if (!array_key_exists('source', $arr) || !array_key_exists('target', $arr)) {
                if ($throwException) {
                    throw new KeysMissingInArrayException($arr, $wantedKeys);
                }
                return false;
            }
        }

        $wantedKeys = array('key', 'value');
        foreach ($this->labels as $label) {
            if (!array_key_exists('key', $label) || !array_key_exists('value', $label)) {
                if ($throwException) {
                    throw new KeysMissingInArrayException($label, $wantedKeys);
                }
                return false;
            }
        }

        $wantedKeys = array('key');
        foreach ($this->environment as $environment) {
            if (!array_key_exists('key', $environment)) {
                if ($throwException) {
                    throw new KeysMissingInArrayException($environment, $wantedKeys);
                }
                return false;
            }
        }

        $wantedKeys = array('type', 'source', 'target');
        $wantedTypes = VolumeTypeEnum::getVolumeTypes();
        foreach ($this->volumes as $v) {
            if (!array_key_exists('type', $v) || !array_key_exists('source', $v) || !array_key_exists('target', $v)) {
                if ($throwException) {
                    throw new KeysMissingInArrayException($v, $wantedKeys);
                }
                return false;
            }
            if (!\in_array($v['type'], $wantedTypes, true)) {
                if ($throwException) {
                    throw new VolumeTypeException($v['type']);
                }
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool $checkValidity
     * @return mixed[]
     * @throws EmptyAttributeException
     * @throws KeysMissingInArrayException
     * @throws VolumeTypeException
     */
    public function serializeToDockerComposeService(bool $checkValidity = false): array
    {
        if ($checkValidity) {
            $this->checkValidity(true);
        }

        $portMap = function ($port) {
            return $port['source'] . ':' . $port['target'];
        };

        $keyValueMap = function ($item) {
            return $item['key'] . '=' . $item['value'];
        };

        $dockerService = array(
            'services' => Utils::arrayFilterRec(array(
                $this->serviceName => [
                    'image' => $this->image,
                    'depends_on' => $this->dependsOn,
                    'ports' => array_map($portMap, $this->ports),
                    'labels' => array_map($keyValueMap, $this->labels),
                    'environment' => array_map($keyValueMap, $this->environment),
                    'volumes' => $this->volumes,
                ],
            )),
        );

        $namedVolumes = array();
        foreach ($this->volumes as $volume) {
            // case it's a named volume
            if ($volume['type'] === VolumeTypeEnum::VOLUME) {
                // for now we just add them without any option
                $namedVolumes[$volume['source']] = null;
            }
        }

        if (!empty($namedVolumes)) {
            $dockerService['volumes'] = $namedVolumes;
        }
        return $dockerService;
    }
}
