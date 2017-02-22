#!/bin/bash

cd docker/

case $1 in
  "up") args="up -d" ;;
  "down") args="down" ;;
  "build") args="build" ;;
  "exec") args="exec php bash" ;;
   *) exit -1 ;;
esac

docker-compose $args
