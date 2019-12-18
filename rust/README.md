# Dockerized Cats API Written in Rust

For simplicity purposes this is an in memory Rust CRUD API for cats.

## Build

From the current directory run `docker build -t rust-api .` (10.9MB)

## Start

Run `docker run --rm -p 80:8080 rust-api`

## Endpoints

| Verb    | Endpoint        | Description       |
| ------- | --------------- | ----------------- |
| GET     | /api/cats       | List all cats     |
| GET     | /api/cats/{id}  | Get a cat         |
| CREATE  | /api/cats       | Create a cat      |
| UPDATE  | /api/cats/{id}  | Update a cat      |
| DELETE  | /api/cats/{id}  | Delete a cat      |