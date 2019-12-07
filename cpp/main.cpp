
#include <httplib.h>

#include <nlohmann/json.hpp>
#include <iostream>
#include <string>
#include <boost/uuid/uuid.hpp>
#include <boost/uuid/uuid_generators.hpp>
#include <boost/uuid/uuid_io.hpp>

using json = nlohmann::json;
namespace Http = httplib;

#define CPPHTTPLIB_THREAD_POOL_COUNT 8

struct Cat
{
    std::string id;
    std::string name;
    int age;
};

json cats = json::array();

void to_json(json & j, const Cat & cat) {
    j = json{{"id", cat.id}, {"name", cat.name}, {"age", cat.age}};
}

int main(int argc, char const *argv[])
{
    Cat garfield{"3dbac162-2ef9-400e-b168-e63cf0cde3f6", "Garfield", 2};
    Cat oreo{"0b8c0ae9-8a4a-4a73-90b7-df68769cd417", "Oreo", 3};
    Cat hunter{"ff2e968f-1b3e-48d8-99b0-da04e32fdd72", "Hunter", 4};

    cats.push_back(garfield);
    cats.push_back(oreo);
    cats.push_back(hunter);

    Http::Server server;

    server.Get(R"(/api/cats)", [](const Http::Request & request, Http::Response & response) {
        response.set_content(cats.dump(), "application/json");
    });

    server.Get(R"(/api/cats/([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}))", [](const Http::Request & request, Http::Response & response) {
        std::string uuid = request.matches[1].str();

        for (const json & cat : cats)
        {
            if (cat["id"] == uuid)
            {
                response.set_content(cat.dump(), "application/json");
                return;
            }
        }

        response.set_content("{}", "application/json");
    });

    server.Post(R"(/api/cats)", [](const Http::Request & request, Http::Response & response) {
        
        boost::uuids::uuid uuid = boost::uuids::random_generator()();
        json body = json::parse(request.body);
        std::stringstream ss;
        std::string uuidString;
        ss << uuid;
        ss >> uuidString;
        Cat cat{uuidString, body["name"], body["age"]};

        cats.push_back(cat);

        response.set_content(cats.dump(), "application/json");
    });

    server.Put(R"(/api/cats/([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}))", [](const Http::Request & request, Http::Response & response) {
        json body = json::parse(request.body);
        std::string uuid = request.matches[1].str();
        
        if (uuid.empty()) {
            uuid = body["id"];
        }

        for (json & cat : cats)
        {
            if (cat["id"] == uuid)
            {
                cat["name"] = body["name"]; 
                cat["age"] = body["age"];
                response.set_content(cats.dump(), "application/json");
                return;
            }
        }

        response.status = 404;
        response.set_content({}, "application/json");
    });

    server.Delete(R"(/api/cats/([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}))", [](const Http::Request & request, Http::Response & response) {
        std::string uuid = request.matches[1];

        for (unsigned int cat = 0; cat < cats.size(); ++cat)
        {
            if (cats[cat]["id"] == uuid)
            {
                cats.erase(cat);
                response.set_content(cats.dump(), "application/json");
                return;
            }
        }

        response.status = 404;
        response.set_content("{}", "application/json");
    });

    server.listen("0.0.0.0", 8080);

    return 0;
}
