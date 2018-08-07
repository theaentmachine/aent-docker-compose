<?php

namespace TheAentMachine\AentDockerCompose\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentDockerCompose\DockerCompose\DockerComposeService;
use TheAentMachine\Service\Service;
use TheAentMachine\YamlTools\YamlTools;

class DockerComposeServiceSerializeTest extends TestCase
{
    private const VALID_PAYLOAD = <<< 'JSON'
{
  "serviceName" : "foo",
  "service": {
    "image"         : "foo/bar:baz",
    "command"       : ["foo", "-bar", "-baz", "--qux"],
    "internalPorts" : [1, 2, 3],
    "dependsOn"     : ["foo", "bar"],
    "ports"         : [{"source": 80, "target": 8080}],
    "environment"   : {
                        "FOO": {"value": "foo", "type": "sharedEnvVariable"},
                        "BAR": {"value": "bar", "type": "sharedSecret"},
                        "BAZ": {"value": "baz", "type": "imageEnvVariable"},
                        "QUX": {"value": "qux", "type": "containerEnvVariable", "comment": "foobar"}
                      },
    "labels"        : {
                        "foo": {"value": "fooo"},
                        "bar": {"value": "baar"}
                      },               
    "volumes"       : [
                        {"type": "volume", "source": "foo", "target": "/foo", "readOnly": true},
                        {"type": "bind", "source": "/bar", "target": "/bar", "readOnly": false},
                        {"type": "tmpfs", "source": "baz"}
                      ]
  }
}
JSON;

    private const PAYLOAD_AFTER_DOCKER_COMPOSE_SERVICE_SERIALIZE = <<< 'YAML'
version: '3.3'
services:
  foo:
    image: 'foo/bar:baz'
    command:
      - foo
      - '-bar'
      - '-baz'
      - '--qux'
    depends_on:
      - foo
      - bar
    ports:
      - '80:8080'
    labels:
      foo: fooo
      bar: baar
    environment:
      BAZ: baz
      # foobar
      QUX: qux
    volumes:
      -
        type: volume
        source: foo
        target: /foo
        read_only: true
      -
        type: bind
        source: /bar
        target: /bar
        read_only: false
      -
        type: tmpfs
        source: baz
    env_file:
      - .env.foobar
volumes:
  foo: null

YAML;

    public function testValidPayload(): void
    {
        $payload = json_decode(self::VALID_PAYLOAD, true);
        $service = Service::parsePayload($payload);

        $out = DockerComposeService::dockerComposeServiceSerialize($service, '.env.foobar', DockerComposeService::VERSION);
        $yaml = YamlTools::dump($out);
        self::assertEquals(self::PAYLOAD_AFTER_DOCKER_COMPOSE_SERVICE_SERIALIZE, $yaml);
    }
}
