cmake_minimum_required(VERSION 3.14 FATAL_ERROR)

project(api)

set(CMAKE_CXX_STANDARD 17)
set(CMAKE_CXX_FLAGS_RELEASE "-O3")
set(CMAKE_EXE_LINKER_FLAGS "-static-libgcc -static-libstdc++")
set(EXTERNAL_INSTALL_LOCATION ${CMAKE_BINARY_DIR}/external)

include(ExternalProject)
ExternalProject_Add(httplib
    PREFIX httplib
    GIT_REPOSITORY https://github.com/yhirose/cpp-httplib
    GIT_TAG v0.3.0
    CMAKE_ARGS -DCMAKE_INSTALL_PREFIX=${EXTERNAL_INSTALL_LOCATION}
)

find_package(Threads REQUIRED)
find_package(Boost 1.67.0 REQUIRED COMPONENTS system)

include_directories(${EXTERNAL_INSTALL_LOCATION}/include/httplib)

add_executable(${CMAKE_PROJECT_NAME} main.cpp)
target_link_libraries(${CMAKE_PROJECT_NAME} Threads::Threads Boost::system)