FROM golang:latest AS build

WORKDIR /app

ADD . .

RUN go get -u github.com/google/uuid
RUN go get -u github.com/gorilla/mux

RUN CGO_ENABLED=0 GOOS=linux go build -a -ldflags '-w -extldflags "-static"' -o api main.go

FROM alpine:latest

WORKDIR /app

COPY --from=build /app/api api

CMD ["/app/api"]

EXPOSE 80