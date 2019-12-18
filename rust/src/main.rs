#![feature(proc_macro_hygiene, decl_macro)]

extern crate serde;
extern crate serde_json;
#[macro_use] extern crate serde_derive;
#[macro_use] extern crate rocket;
#[macro_use] extern crate rocket_contrib;

use std::vec::Vec;

use rocket::{State};
use rocket::response::content;
// use serde::{Serialize, Deserialize};
// use std::sync::Mutex;
use serde_json::Result;

#[derive(Serialize, Deserialize, Debug)]
pub struct NotImplementedMessage {
    message: String
}

#[derive(Serialize, Deserialize, Debug)]
pub struct NotFoundMessage {
    message: String
}

#[derive(Serialize, Deserialize, Debug)]
pub struct Cat {
    id: &'static str,
    name: &'static str,
    age: i32
}

pub type Cats = Vec<Cat>;

pub struct InMemoryDatabase {
    cats: Vec<Cat>
}

#[get("/cats", format = "json")]
pub fn list_cats(database: State<InMemoryDatabase>) -> Result<content::Json<String>> {
    let cats: &Cats = &database.cats;
    let json: String = serde_json::to_string(cats).unwrap();
    
    Ok(content::Json(json))
}

#[get("/cats/<id>", format = "json")]
pub fn get_cat(id: String) -> Result<content::Json<String>> {
    let json: String = serde_json::to_string(&NotImplementedMessage{message: format!("Pending Implemention! get cat {}", id)}).unwrap();
    
    Ok(content::Json(json))
}

#[post("/cats", format = "json")]
pub fn create_cat() -> Result<content::Json<String>> {
    let json: String = serde_json::to_string(&NotImplementedMessage{message: String::from("Pending Implemention! create cat")}).unwrap();
    
    Ok(content::Json(json))
}

#[put("/cats/<id>", format = "json")]
pub fn update_cat(id: String) -> Result<content::Json<String>> {
    let json: String = serde_json::to_string(&NotImplementedMessage{message: format!("Pending Implemention! update cat {}", id)}).unwrap();
    
    Ok(content::Json(json))
}

#[delete("/cats/<id>", format = "json")]
pub fn delete_cat(id: String) -> Result<content::Json<String>> {
    let json: String = serde_json::to_string(&NotImplementedMessage{message: format!("Pending Implemention! delete cat {}", id)}).unwrap();
    
    Ok(content::Json(json))
}

#[catch(404)]
pub fn not_found() -> Result<content::Json<String>>  {
    let json: String = serde_json::to_string(&NotFoundMessage{message: String::from("Resource not found!")}).unwrap();
    
    Ok(content::Json(json))
}

fn main() {
    let in_memory_database = InMemoryDatabase {
        cats: vec![
            Cat{id: "3dbac162-2ef9-400e-b168-e63cf0cde3f6", name: "Garfield", age: 2},
            Cat{id: "0b8c0ae9-8a4a-4a73-90b7-df68769cd417", name: "Oreo", age: 3},
            Cat{id: "ff2e968f-1b3e-48d8-99b0-da04e32fdd72", name: "Hunter", age: 4}
        ]
    };

    rocket::ignite()
        .mount("/api", routes![list_cats, get_cat, create_cat, update_cat, delete_cat])
        .register(catchers![not_found])
        .manage(in_memory_database)
        .launch();
}