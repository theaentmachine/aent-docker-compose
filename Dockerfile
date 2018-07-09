FROM thecodingmachine/php:7.2-v1-cli as builder

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install --no-dev

FROM theaentmachine/base-php-aent:0.0.14

RUN apk add --no-cache \
    python \
    python-dev \
    py-pip \
    build-base &&\
    pip install -U pip setuptools wheel &&\
    pip install ruamel.yaml

# Installs docker-compose
RUN pip install docker-compose

# Installs yaml-tools. You may find all available versions in the releases page: https://github.com/thecodingmachine/yaml-tools/releases
ENV YAML_TOOLS_VERSION "0.0.6"
RUN wget -q https://raw.githubusercontent.com/thecodingmachine/yaml-tools/$YAML_TOOLS_VERSION/src/yaml_tools.py -O /usr/bin/yaml-tools &&\
    chmod +x /usr/bin/yaml-tools

# Copies our aent entry point.
COPY aent.sh /usr/bin/aent

# Copies vendor directory.
COPY --from=builder /usr/src/app/vendor ./vendor

# Copies our PHP source.
COPY ./src ./src