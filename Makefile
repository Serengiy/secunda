# Makefile

# Загрузка переменных окружения
include .env

# Установка цели по умолчанию
.DEFAULT_GOAL := help

start: # развернуть приложение с нуля
	composer install
	php artisan sail:install
	docker compose up -d --remove-orphans
	./vendor/bin/sail artisan key:generate
	./vendor/bin/sail artisan migrate
	./vendor/bin/sail artisan app:set-up
	./vendor/bin/sail artisan db:seed
	./vendor/bin/sail artisan test

up: # Поднятие контейнеров
	docker compose up --remove-orphans

up-d: # Поднятие контейнеров в фоне
	docker compose up -d --remove-orphans

sh: # Вход в контейнер
	docker compose exec -it app bash

down: # Удаление контейнеров
	docker compose down
