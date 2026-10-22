let events = [];

let currentPage = 1;

let eventsPerPage = 2;


// Load events from JSON file

function loadEvents() {

    fetch("./event.json")

        .then(function(response) {

            if (!response.ok) {
                throw new Error(
                    "event.json could not be found. Check the file name and location."
                );
            }

            return response.json();

        })

        .then(function(data) {

            events = data;

            currentPage = 1;

            displayEvents();

        })

        .catch(function(error) {

            document.getElementById("eventList").innerHTML =

                "<p style='color:red;'><b>Error:</b> " +
                error.message +
                "</p>";

            console.log("Error:", error);

        });
}


// Display events

function displayEvents() {

    let searchText =
        document.getElementById("search").value.toLowerCase();


    let filteredEvents = events.filter(function(event) {

        return event.name.toLowerCase().includes(searchText);

    });


    let totalPages =
        Math.ceil(filteredEvents.length / eventsPerPage);


    if (totalPages === 0) {
        totalPages = 1;
    }


    if (currentPage > totalPages) {
        currentPage = totalPages;
    }


    let start =
        (currentPage - 1) * eventsPerPage;


    let end =
        start + eventsPerPage;


    let pageEvents =
        filteredEvents.slice(start, end);


    let eventList =
        document.getElementById("eventList");


    eventList.innerHTML = "";


    if (pageEvents.length === 0) {

        eventList.innerHTML =
            "<p>No events found.</p>";

    }


    pageEvents.forEach(function(event) {

        let card =
            document.createElement("div");


        card.className = "data-card";


        card.innerHTML =

            "<h3>" + event.name + "</h3>" +

            "<p><b>Date:</b> " +
            event.date +
            "</p>" +

            "<p><b>Time:</b> " +
            event.time +
            "</p>" +

            "<p><b>Venue:</b> " +
            event.venue +
            "</p>" +

            "<p><b>Description:</b> " +
            event.description +
            "</p>";


        eventList.appendChild(card);

    });


    document.getElementById("pageNumber").innerHTML =

        "Page " +
        currentPage +
        " of " +
        totalPages;

}


// Search events

function searchEvents() {

    currentPage = 1;

    displayEvents();

}


// Sort events

function sortEvents() {

    events.sort(function(a, b) {

        return a.name.localeCompare(b.name);

    });


    currentPage = 1;

    displayEvents();

}


// Previous page

function previousPage() {

    if (currentPage > 1) {

        currentPage--;

        displayEvents();

    }

}


// Next page

function nextPage() {

    let searchText =
        document.getElementById("search").value.toLowerCase();


    let filteredEvents = events.filter(function(event) {

        return event.name.toLowerCase().includes(searchText);

    });


    let totalPages =
        Math.ceil(filteredEvents.length / eventsPerPage);


    if (currentPage < totalPages) {

        currentPage++;

        displayEvents();

    }

}


// Wait until page is loaded

document.addEventListener("DOMContentLoaded", function() {


    document
        .getElementById("search")
        .addEventListener("keyup", searchEvents);


    document
        .getElementById("sortButton")
        .addEventListener("click", sortEvents);


    document
        .getElementById("previousButton")
        .addEventListener("click", previousPage);


    document
        .getElementById("nextButton")
        .addEventListener("click", nextPage);


    loadEvents();

});



// ===============================
// FAQ SECTION
// ===============================

let faqs = [];


// Load FAQs from JSON

function loadFAQs() {

    fetch("./faq.json")

        .then(function(response) {

            if (!response.ok) {

                throw new Error(
                    "faq.json could not be found. Check the file name and location."
                );

            }

            return response.json();

        })

        .then(function(data) {

            faqs = data;

            showFAQs();

        })

        .catch(function(error) {

            document.getElementById("faqList").innerHTML =

                "<p class='error'><b>Error:</b> " +
                error.message +
                "</p>";

            console.log("FAQ Error:", error);

        });

}


// Display FAQs

function showFAQs() {

    let searchText =
        document.getElementById("faqSearch").value.toLowerCase();


    let selectedCategory =
        document.getElementById("category").value;


    let filteredFAQs = faqs.filter(function(faq) {


        let matchesSearch =
            faq.question.toLowerCase().includes(searchText) ||
            faq.answer.toLowerCase().includes(searchText);


        let matchesCategory =
            selectedCategory === "All" ||
            faq.category === selectedCategory;


        return matchesSearch && matchesCategory;

    });


    let faqList =
        document.getElementById("faqList");


    faqList.innerHTML = "";


    if (filteredFAQs.length === 0) {

        faqList.innerHTML =
            "<p>No FAQs found.</p>";

        return;

    }


    filteredFAQs.forEach(function(faq) {


        let card =
            document.createElement("div");


        card.className = "data-card";


        card.innerHTML =

            "<h3>" +
            faq.question +
            "</h3>" +

            "<p><b>Category:</b> " +
            faq.category +
            "</p>" +

            "<p class='answer'><b>Answer:</b> " +
            faq.answer +
            "</p>";


        faqList.appendChild(card);

    });

}


// FAQ search

document.addEventListener("DOMContentLoaded", function() {


    let searchBox =
        document.getElementById("faqSearch");


    let categoryBox =
        document.getElementById("category");


    if (searchBox && categoryBox) {


        searchBox.addEventListener(
            "keyup",
            showFAQs
        );


        categoryBox.addEventListener(
            "change",
            showFAQs
        );


        loadFAQs();

    }

});