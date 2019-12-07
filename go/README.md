# Dockerized Cats API Written in Go

For simplicity purposes this is an in memory go CRUD API for cats.

## Build

From go directory run `docker build -t go-api .` (12MB)

## Start

Run `docker run --rm -p 80:8080 go-api`

## Endpoints

| Verb    | Endpoint        | Description       |
| ------- | --------------- | ----------------- |
| GET     | /api/cats       | List all cats     |
| GET     | /api/cats/{id}  | Get a cat         |
| CREATE  | /api/cats       | Create a cat      |
| UPDATE  | /api/cats/{id}  | Update a cat      |
| DELETE  | /api/cats/{id}  | Delete a cat      |