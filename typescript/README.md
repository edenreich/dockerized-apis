# Dockerized Cats API Written in Typescript

For simplicity purposes this is an in memory typescript CRUD API for cats.

## Build

From go directory run `docker build -t typescript-api .` (108MB)

## Start

Run `docker run --rm -p 80:3000 typescript-api`

## Endpoints

| Verb    | Endpoint        | Description       |
| ------- | --------------- | ----------------- |
| GET     | /api/cats       | List all cats     |
| GET     | /api/cats/{id}  | Get a cat         |
| CREATE  | /api/cats       | Create a cat      |
| UPDATE  | /api/cats/{id}  | Update a cat      |
| DELETE  | /api/cats/{id}  | Delete a cat      |