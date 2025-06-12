#!/bin/bash

# Garante que o .env existe
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Roda os containers
docker-compose up -d --build
