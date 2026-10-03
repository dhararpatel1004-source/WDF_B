let events = [];
let page = 1;
let perPage = 2;


function loadEvents() {

    fetch("event.json")

        .then(function(response) {

            return response.json();

        })

        .then(function(data) {

            events = data;

            showEvents();

        })

        .catch(function(error) {

            console.log("Event Error:", error);

            document.getElementById("eventList").innerHTML =
                "<p>Events could not be loaded.</p>";

        });

}

function showEvents() {

    let search =
        document.getElementById("search").value.toLowerCase();


    let result = events.filter(function(event) {

        return event.name.toLowerCase().includes(search);

    });


    let start = (page - 1) * perPage;

    let data = result.slice(start, start + perPage);


    let output = "";


    data.forEach(function(event) {

        output += `
            <div class="data-card">

                <h3>${event.name}</h3>

                <p>Date: ${event.date}</p>

                <p>Venue: ${event.venue}</p>

            </div>
        `;

    });


    if (data.length == 0) {

        output = "<p>No event found.</p>";

    }


    document.getElementById("eventList").innerHTML = output;


    let totalPages = Math.ceil(result.length / perPage);


    document.getElementById("pageNumber").innerHTML =
        "Page " + page + " of " + totalPages;

}


function searchEvents() {

    page = 1;

    showEvents();

}

function sortEvents() {

    events.sort(function(a, b) {

        return a.name.localeCompare(b.name);

    });

    page = 1;

    showEvents();

}

function nextPage() {

    let totalPages =
        Math.ceil(events.length / perPage);


    if (page < totalPages) {

        page++;

        showEvents();

    }

}

function previousPage() {

    if (page > 1) {

        page--;

        showEvents();

    }

}


let faqs = [];


function loadFAQs() {

    fetch("faq.json")

        .then(function(response) {

            return response.json();

        })

        .then(function(data) {

            faqs = data;

            showFAQs();

        })

        .catch(function(error) {

            console.log("FAQ Error:", error);

            document.getElementById("faqList").innerHTML =
                "<p>FAQs could not be loaded.</p>";

        });

}


function showFAQs() {

    let search =
        document.getElementById("faqSearch").value.toLowerCase();


    let category =
        document.getElementById("category").value;


    let result = faqs.filter(function(faq) {

        let searchMatch =
            faq.question.toLowerCase().includes(search);


        let categoryMatch =
            category == "All" ||
            faq.category == category;


        return searchMatch && categoryMatch;

    });


    let output = "";


    result.forEach(function(faq) {

        output += `
            <div class="data-card">

                <h3>${faq.question}</h3>

                <p>${faq.answer}</p>

                <small>Category: ${faq.category}</small>

            </div>
        `;

    });


    if (result.length == 0) {

        output = "<p>No question found.</p>";

    }


    document.getElementById("faqList").innerHTML = output;

}