FROM alpine:latest AS build

WORKDIR /app

RUN apk --update add build-base git cmake boost boost-dev boost-static

COPY CMakeLists.txt CMakeLists.txt
COPY main.cpp main.cpp

RUN mkdir build && cd build
RUN cd build && cmake .. -DCMAKE_BUILD_TYPE=Release && make

FROM alpine:latest

WORKDIR /app

COPY --from=build /app/build/api api

CMD [ "/app/api" ]

EXPOSE 8080