FROM thecodingmachine/php:7.2-v1-cli as builder

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install --no-dev

FROM php:7.2-cli-alpine3.7

LABEL authors="Julien Neuhart <j.neuhart@thecodingmachine.com>"

# Defines SHELL.
ENV SHELL "/bin/sh"

# Installs missing libraries.
RUN apk add --no-cache --update \
    wget \
    tar \
    python \
    python-dev \
    py-pip \
    build-base &&\
    pip install -U pip setuptools wheel &&\
    pip install ruamel.yaml

# Installs Docker client.
ENV DOCKER_VERSION "18.03.1-ce"
RUN wget -qO- https://download.docker.com/linux/static/stable/x86_64/docker-$DOCKER_VERSION.tgz | tar xvz -C . &&\
    mv ./docker/docker /usr/bin &&\
    rm -rf ./docker

# Installs docker-compose
RUN pip install docker-compose

# Installs yaml-tools. You may find all available versions in the releases page: https://github.com/thecodingmachine/yaml-tools/releases
ENV YAML_TOOLS_VERSION "0.0.3"
RUN wget -q https://raw.githubusercontent.com/thecodingmachine/yaml-tools/$YAML_TOOLS_VERSION/src/yaml_tools.py -O /usr/bin/yaml-tools &&\
    chmod +x /usr/bin/yaml-tools

# Installs Hermes. You may find all available versions in the releases page: https://github.com/aenthill/hermes/releases/.
ENV HERMES_VERSION "0.0.14"
RUN curl -sf https://raw.githubusercontent.com/aenthill/hermes/master/install.sh | BINDIR=/usr/bin sh -s $HERMES_VERSION

# Copies our aent entry point.
COPY aent.sh /usr/bin/aent

# Copies vendor directory.
COPY --from=builder /usr/src/app/vendor ./vendor

# Copies our PHP source.
COPY ./src ./src