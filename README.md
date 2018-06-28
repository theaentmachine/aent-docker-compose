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

### ADD
`$ aent handle ADD`

todo

### REMOVE
`$ aent handle REMOVE`

todo


### NEW_DOCKER_SERVICE_INFO
`$ aent handle NEW_DOCKER_SERVICE_INFO {payload}|null`

Payload format (JSON) :
```
{
  "serviceName" : "foo",                                        //required  
  "service": {
    "image"         : "foo/bar:baz",
    "command"       : ["foo", "-bar", "-baz", "--qux"],
    "internalPorts" : [1, 2, 3],
    "dependsOn"     : ["foo", "bar"],
    "ports"         : [{"source": 80, "target": 8080}],
    "environment"   : {
                        "FOO": {
                          "value": "fooo",                      //required
                          "type": "sharedEnvVariable|sharedSecret|imageEnvVariable|containerEnvVariable"    //required
                        },
                        "BAR": {...}
                      },
    "labels"        : {
                        "foo": {"value": "fooo"},
                        "bar": {"value": "baar"}
                      },               
    "volumes"       : [
                        {
                          "type"      : "volume|bind|tmpfs",    //required
                          "source"    : "foo",                  //required
                          "target"    : "bar",
                          "readOnly"  : true|false
                        }
                      ]
  },
  "dockerfileCommands": [
      "FROM foo",
      "COPY bar",
      "RUN baz"
  ]
}
```
It handles named volumes by adding them inside **volumes** (at root level) in docker-compose.json

TODO: handle case when the service already exists


### DELETE-DOCKER-SERVICE
`$ aent DELETE-DOCKER-SERVICE {payload}`

Payload format (JSON) :
```
{
  "serviceName" : "foo",
  "namedVolumes" : ["bar"]
}
```

