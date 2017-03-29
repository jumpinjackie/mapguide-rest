#!/bin/sh
docker build -t jumpinjackie/mapguide-rest-demo -f demo/Dockerfile .
docker run -p 8008:8008 -t jumpinjackie/mapguide-rest-demo