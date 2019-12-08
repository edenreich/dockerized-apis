FROM rust:1.39.0-alpine3.10 AS build

WORKDIR /app

COPY . .

RUN cargo install --path .

FROM alpine:latest

WORKDIR /app

COPY --from=build /app/target/release/api api

CMD [ "./api" ]