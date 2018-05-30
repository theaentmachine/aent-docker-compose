<h1 align="center">aent-docker-compose</h1>
<p align="center">TODO</p>
<p align="center">
    <a href="https://travis-ci.org/theaentmachine/aent-docker-compose">
        <img src="https://travis-ci.org/theaentmachine/aent-docker-compose.svg?branch=master" alt="Travis CI">
    </a>
    <a href="https://scrutinizer-ci.com/g/theaentmachine/aent-docker-compose/?branch=master">
        <img src="https://scrutinizer-ci.com/g/theaentmachine/aent-docker-compose/badges/quality-score.png?b=master" alt="Scrutinizer">
    </a>
    <a href="https://codecov.io/gh/theaentmachine/aent-docker-compose/branch/master">
        <img src="https://codecov.io/gh/theaentmachine/aent-docker-compose/branch/master/graph/badge.svg" alt="Codecov">
    </a>
</p>

---

## events

### new-docker-service
`$ aent new-docker-service PAYLOAD`

Payload format (JSON) :
```
{
  "serviceName" : "foo",
  "service": {
    "image"         : "foo",
    "internalPorts" : [123],
    "dependsOn"     : ["foo"],
    "ports"         : [{"source": 80, "target": 8080}],
    "labels"        : [{"key": "foo", "value": "bar"}],
    "environments"   : [{"key": "FOO", "value": "bar"}],
    "volumes": [
      {
        "type"        : "volume|bind|tmpfs",
        "source"	  : "foo",
        "target"	  : "bar",
        "readOnly"    : true
      }
    ]
  }
}
```
It handles named volumes by adding them in the root **volumes:** level

### delete-docker-service
`$ aent delete-docker-service PAYLOAD`

Payload format (JSON) :
```
{
  "serviceName" : "foo",
  "namedVolume" : ["bar"]
}
```

