<?php

namespace TheAentMachine\AentDockerCompose\DockerService;

use TheAentMachine\AentDockerCompose\DockerService\Exception\PayloadInvalidJsonException;
use TheAentMachine\AentDockerCompose\DockerService\Exception\PayloadKeyMissingException;

class Service
{

    /*
    public const SERVICE_NAME = 'serviceName';
    public const IMAGE = 'image';
    public const INTERNAL_PORTS = 'internalPorts';
    public const DEPENDS_ON = 'dependsOn';
    public const PORTS = 'ports';
    public const LABELS = 'labels';
    public const ENVIRONMENTS = 'environments';
    public const VOLUMES = 'volumes';
    public const TYPE = 'type';
    public const SOURCE*/

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
     * @param string $payload
     * @throws PayloadInvalidJsonException
     * @throws PayloadKeyMissingException
     */
    public function parsePayload(string $payload) : void
    {
        $p = json_decode($payload, true);
        if (!$p) {
            throw new PayloadInvalidJsonException();
        }

        $this->serviceName = $p["serviceName"] ?? '';
        if (empty($serviceName)) {
            throw new PayloadKeyMissingException('serviceName');
        }

        $service = $p['service'] ?? array();
        if (empty($service)) {
            throw new PayloadKeyMissingException('service');
        }

        $this->image = $service['image'] ?? '';
        $this->dependsOn = $service['dependsOn'] ?? array();
        $this->ports = $service['ports'] ?? array();

        // TODO: WIP

        $labels = array();
        foreach ($service['labels'] ?? array() as $label) {
            $str = $label['value'] ? sprintf("%s=%s", $label['key'], $label['value']) : $label['key'];
            $labels[] = $str;
        }
        $environments = array();
        foreach ($service['environments'] ?? array() as $env) {
            $str = $env['value'] ? sprintf("%s=%s", $env['key'], $env['value']) : $env['key'];
            $environments[] = $str;
        }

        $volumes = $p['volumes'] ?? array();
        $namedVolumes = array();
        foreach ($service['volumes'] ?? array() as $volume) {
            $type = $volume['type'] ?? "";
            $source = $volume['source'] ?? "";

            $formattedVolume = array(
                "type" => $type,
                "source" => $source,
                "target" => $volume['target'] ?? "",
                "read_only" => $volume['readOnly'] ?? "",
            );
            $volumes[] = $formattedVolume;

            // case it's a named volume
            if ($type === "volume") {
                // for now we just add them without any option
                $namedVolumes[$source] = null;
            }
        }
        exit(1);
    }
}
