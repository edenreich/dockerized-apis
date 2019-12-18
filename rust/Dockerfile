FROM rustlang/rust:nightly-stretch AS build

WORKDIR /app

ENV PKG_CONFIG_ALLOW_CROSS=1

RUN apt-get update && apt-get -y install build-essential musl-tools
RUN rustup target add x86_64-unknown-linux-musl

COPY . .

RUN RUSTFLAGS="$RUSTFLAGS -C target-feature=+crt-static" cargo build --target=x86_64-unknown-linux-musl --release

FROM alpine:latest

WORKDIR /app

COPY --from=build /app/target/x86_64-unknown-linux-musl/release/api api

CMD [ "./api" ]