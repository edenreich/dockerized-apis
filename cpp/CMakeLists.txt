cmake_minimum_required(VERSION 3.14 FATAL_ERROR)

project(api)

set(CMAKE_CXX_STANDARD 17)
set(CMAKE_CXX_FLAGS_RELEASE "-O3")
set(CMAKE_EXE_LINKER_FLAGS "-static-libgcc -static-libstdc++")
set(VENDOR_DEPENDENCIES_DIR ${CMAKE_BINARY_DIR}/_deps)

if(NOT EXISTS "${CMAKE_HOME_DIRECTORY}/cmake/CPM.cmake")
  file(DOWNLOAD https://raw.githubusercontent.com/TheLartians/CPM.cmake/master/cmake/CPM.cmake "${CMAKE_BINARY_DIR}/cmake/CPM.cmake")
endif()

include(${CMAKE_BINARY_DIR}/cmake/CPM.cmake)

CPMAddPackage(
    NAME httplib
    GITHUB_REPOSITORY yhirose/cpp-httplib
    VERSION 0.3.0
    DOWNLOAD_ONLY True
)

CPMAddPackage(
    NAME nlohmann_json
    GITHUB_REPOSITORY nlohmann/json
    VERSION 3.2.0
    OPTIONS
        "JSON_BuildTests OFF"
)

include_directories(
    ${VENDOR_DEPENDENCIES_DIR}/httplib-src
    ${VENDOR_DEPENDENCIES_DIR}/nlohmann_json-src/include
)

find_package(Threads REQUIRED)
find_package(Boost 1.67.0 REQUIRED COMPONENTS system)
find_package(nlohmann_json QUIET)

add_executable(${CMAKE_PROJECT_NAME} main.cpp)
target_link_libraries(${CMAKE_PROJECT_NAME} Threads::Threads Boost::system nlohmann_json::nlohmann_json)