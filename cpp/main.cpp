
#include <httplib.h>

#define CPPHTTPLIB_THREAD_POOL_COUNT 8

int main(int argc, char const *argv[])
{
    using namespace httplib;

    Server server;

    server.Get("/cats", [](const Request& request, Response& response) {
        response.set_content("TEST!", "application/json");
    });

    server.listen("0.0.0.0", 8080);

    return 0;
}
