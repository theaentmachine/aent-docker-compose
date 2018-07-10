FROM thecodingmachine/php:7.2-v1-cli as builder

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install --no-dev

FROM theaentmachine/base-php-aent:0.0.14

# Installs Python3, pip, ruamel.yaml and docker-compose.
RUN apk add --no-cache python3 &&\
    pip3 install --upgrade --no-cache-dir pip ruamel.yaml docker-compose

# Installs yaml-tools. You may find all available versions in the releases page: https://github.com/thecodingmachine/yaml-tools/releases
ENV YAML_TOOLS_VERSION "0.0.7"
# RUN wget -q https://raw.githubusercontent.com/thecodingmachine/yaml-tools/$YAML_TOOLS_VERSION/src/yaml_tools.py -O /usr/bin/yaml-tools &&\
RUN wget -q https://raw.githubusercontent.com/thecodingmachine/yaml-tools/$YAML_TOOLS_VERSION/src/yaml_tools.py -O /usr/bin/yaml-tools &&\
    chmod +x /usr/bin/yaml-tools

# Copies our aent entry point.
COPY aent.sh /usr/bin/aent

# Copies vendor directory.
COPY --from=builder /usr/src/app/vendor ./vendor

# Copies our PHP source.
COPY ./src ./src