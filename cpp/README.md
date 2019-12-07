# Dockerized Cats API Written in C++

For simplicity purposes this is an in memory C++ CRUD API for cats.

## Build

From go directory run `docker build -t cpp-api .` (12.8MB)

## Start

Run `docker run --rm -p 80:8080 cpp-api`

## Endpoints

| Verb    | Endpoint        | Description       |
| ------- | --------------- | ----------------- |
| GET     | /api/cats       | List all cats     |
| GET     | /api/cats/{id}  | Get a cat         |
| CREATE  | /api/cats       | Create a cat      |
| UPDATE  | /api/cats/{id}  | Update a cat      |
| DELETE  | /api/cats/{id}  | Delete a cat      |
