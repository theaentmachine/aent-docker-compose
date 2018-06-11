<?php

namespace TheAentMachine\AentDockerCompose\Service;

use TheAentMachine\AentDockerCompose\Service\Enum\VolumeTypeEnum;
use TheAentMachine\AentDockerCompose\Service\Exception\EmptyAttributeException;
use TheAentMachine\AentDockerCompose\Service\Exception\KeysMissingInArrayException;
use TheAentMachine\AentDockerCompose\Service\Exception\PayloadInvalidJsonException;
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
    protected $environments;
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
        $this->environments = array();
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
     * @param string[] $environments
     * @return Service
     */
    public function setEnvironments(array $environments): Service
    {
        $this->environments = $environments;
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
     * @param string $payload
     * @throws EmptyAttributeException
     * @throws KeysMissingInArrayException
     * @throws PayloadInvalidJsonException
     * @throws VolumeTypeException
     */
    public function parsePayload(string $payload): void
    {
        $p = json_decode($payload, true);
        if (!$p) {
            throw new PayloadInvalidJsonException();
        }
        $this->serviceName = $p["serviceName"] ?? '';
        $service = $p['service'] ?? array();
        if (!empty($service)) {
            $this->image = $service['image'] ?? '';
            $this->internalPorts = $service['internalPorts'] ?? '';
            $this->dependsOn = $service['dependsOn'] ?? array();
            $this->ports = $service['ports'] ?? array();
            $this->labels = $service['labels'] ?? array();
            $this->environments = $service['environments'] ?? array();
            $this->volumes = $service['volumes'] ?? array();
        }
        $this->checkValidity(true);
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
        foreach ($this->environments as $environment) {
            if (!array_key_exists('key', $environment) || !array_key_exists('value', $environment)) {
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
            if (!in_array($v['type'], $wantedTypes)) {
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
            return array($port['source'], $port['target']);
        };

        $keyValueMap = function ($item) {
            return $item['key'] . '=' . $item['value'];
        };

        $dockerService = array(
            "services" => Utils::arrayFilterRec(array(
                $this->serviceName => [
                    "image" => $this->image,
                    "depends_on" => $this->dependsOn,
                    "ports" => array_map($portMap, $this->ports),
                    "labels" => array_map($keyValueMap, $this->labels),
                    "environments" => array_map($keyValueMap, $this->environments),
                    "volumes" => $this->volumes,
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
