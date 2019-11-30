package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"log"
	"net/http"

	"github.com/google/uuid"
	"github.com/gorilla/mux"
)

// Cat represent a cat
type Cat struct {
	ID   string `json:"id,omitempty"`
	Name string `json:"name"`
	Age  int    `json:"age"`
}

var cats []Cat

func preMiddleware(handler http.HandlerFunc) http.HandlerFunc {
	return func(response http.ResponseWriter, request *http.Request) {
		fmt.Printf("%v => %v\n", request.Method, request.URL)
		response.Header().Set("Content-Type", "application/json")
		handler.ServeHTTP(response, request)
	}
}

func onStart(router *mux.Router) *mux.Router {
	fmt.Println("Starting API server..")

	return router
}

func listCats(response http.ResponseWriter, request *http.Request) {
	json.NewEncoder(response).Encode(&cats)
}

func getCat(response http.ResponseWriter, request *http.Request) {
	params := mux.Vars(request)

	for _, cat := range cats {
		if params["id"] == cat.ID {
			json.NewEncoder(response).Encode(cat)
			return
		}
	}

	response.WriteHeader(http.StatusNotFound)
}

func createCat(response http.ResponseWriter, request *http.Request) {
	var cat Cat

	cat.ID = uuid.Must(uuid.NewRandom()).String()

	body, _ := ioutil.ReadAll(request.Body)
	err := json.Unmarshal(body, &cat)
	if err != nil {
		http.Error(response, "{ 'message': 'Invalid json' }", http.StatusUnprocessableEntity)
		return
	}

	cats = append(cats, cat)

	response.WriteHeader(http.StatusCreated)
	json.NewEncoder(response).Encode(cat)
}

func updateCat(response http.ResponseWriter, request *http.Request) {
	params := mux.Vars(request)
	var requestedCat Cat

	body, _ := ioutil.ReadAll(request.Body)
	err := json.Unmarshal(body, &requestedCat)
	if err != nil {
		http.Error(response, "{ 'message': 'Invalid json' }", http.StatusUnprocessableEntity)
		return
	}

	requestedCat.ID = params["id"]

	for index, cat := range cats {
		if requestedCat.ID == cat.ID {
			pCat := &cats[index]
			*pCat = requestedCat
			response.WriteHeader(http.StatusCreated)
			json.NewEncoder(response).Encode(*pCat)
			return
		}
	}

	http.Error(response, "{ 'message': 'Cat not found' }", http.StatusUnprocessableEntity)
}

func deleteCat(response http.ResponseWriter, request *http.Request) {
	params := mux.Vars(request)
	var requestedCat Cat

	body, _ := ioutil.ReadAll(request.Body)
	err := json.Unmarshal(body, &requestedCat)
	if err != nil {
		http.Error(response, "{ 'message': 'Invalid json' }", http.StatusUnprocessableEntity)
		return
	}

	requestedCat.ID = params["id"]

	for index, cat := range cats {
		if requestedCat.ID == cat.ID {
			cats = append(cats[:index], cats[index+1:]...)
			response.WriteHeader(http.StatusCreated)
			json.NewEncoder(response).Encode(cats)
			return
		}
	}

	http.Error(response, "{ 'message': 'Cat not found' }", http.StatusUnprocessableEntity)
}

func main() {
	router := mux.NewRouter()

	cats = append(cats, Cat{ID: "3dbac162-2ef9-400e-b168-e63cf0cde3f6", Name: "Miao", Age: 1})
	cats = append(cats, Cat{ID: "0b8c0ae9-8a4a-4a73-90b7-df68769cd417", Name: "Garfield", Age: 2})
	cats = append(cats, Cat{ID: "1f1510e4-b9a4-483e-964a-30f1c2a47b8a", Name: "Oreo", Age: 3})
	cats = append(cats, Cat{ID: "ff2e968f-1b3e-48d8-99b0-da04e32fdd72", Name: "Hunter", Age: 4})

	router.HandleFunc("/api/cats", preMiddleware(listCats)).Methods("GET")
	router.HandleFunc("/api/cats/{id}", preMiddleware(getCat)).Methods("GET")
	router.HandleFunc("/api/cats", preMiddleware(createCat)).Methods("POST")
	router.HandleFunc("/api/cats/{id}", preMiddleware(updateCat)).Methods("PUT")
	router.HandleFunc("/api/cats/{id}", preMiddleware(deleteCat)).Methods("DELETE")

	log.Fatal(http.ListenAndServe(":8080", onStart(router)))
}
