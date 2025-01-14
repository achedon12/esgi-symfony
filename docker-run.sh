#!/bin/bash

# load env file

if [ -f .env ]
then
  set -o allexport
  source <(sed -e "s/\r//" -e '/^#/d;/^\s*$/d' -e "s/'/'\\\''/g" -e "s/=\(.*\)/=\"\1\"/g" .env)
  set +o allexport
  chmod 777 .env
fi

set -o allexport # enable all variable definitions to be exported
source <(sed -e "s/\r//" -e '/^#/d;/^\s*$/d' -e "s/'/'\\\''/g" -e "s/=\(.*\)/=\"\1\"/g" .env)
set +o allexport

# create network
if docker network ls|grep $(printenv DOCKER_NETWORK) > /dev/null
then
  docker network ls|grep $(printenv DOCKER_NETWORK)
else
  docker network create $(printenv DOCKER_NETWORK)
fi