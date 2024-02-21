/* SEND & RECEIVE SEARCH FORM VIA AJAX */

function sendSearch() {
    showLoadingIcon(true);
    hideAllErrors();

    // Getting and validating the search bar value
    let searchBarValue = document.getElementById('searchBar').value;
    if (!validateSearch(searchBarValue)) return;

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/search/search", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulSearch, handleSearchError);

    // Sending the request
    xmlHttp.send(JSON.stringify({searchData: searchBarValue}));
}

function validateSearch(searchBarValue) {
    if (searchBarValue === undefined || searchBarValue === null || searchBarValue.trim().length <= 0) {
        handleSearchError('Search bar may not be empty!');
        return false;
    }

    return true;
}

function successfulSearch(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);

    // Check if the server send a success message
    if (!jsonResponse.success)
        handleSearchError(jsonResponse.error);

    // If the server send a success message, display the results
    else {
        showSearchResults(jsonResponse.results);

        // hide the loading icon
        showLoadingIcon(false);
    }

    window.history.pushState("", "", "/search?searchBar=" + document.getElementById('searchBar').value);
    document.getElementsByTagName('title')[0].innerHTML = document.getElementById('searchBar').value + " - Search Fruckr";
}

function handleSearchError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    if (errorMsg.includes('No foodtrucks found')) {
        searchResultsList.innerHTML = '<li><p>No results found</p></li>';
        generateFilters(true);
    }
    else
        displayError("errorBox", errorMsg)

    showLoadingIcon(false);
}

function showLoadingIcon(show) {
    setVisibility(document.getElementById('searchLoading'), show);
}

const searchResultDiv = document.getElementsByClassName('searchResults')[0];
const searchResultsList = searchResultDiv.getElementsByTagName('ul')[0];

function showSearchResults(results) {
    // Empty the current search results
    removeChildren(searchResultsList);

    // Add the new search results
    for (let i = 0; i < results.length; i++) {
        let result = results[i]; // result should contain html for a search result
        searchResultsList.innerHTML += result;
    }

    // Generate the filters
    generateFilters(true);
}

// On submit of the search form, send the ajax request
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendSearch();
});

/* FILTERS */

const MAX_FILTERS = 5;

let initialCityFilters = [];
let initialTagFilters = [];
let initialRatingFilters = [];

let currentCityFilters = [];
let currentTagFilters = [];
let currentRatingFilters = [];

function generateFilters(isInitial = false) {
    // Get all the cities and tags
    let filterResults = getFilterResults();
    let cities = filterResults.cities;
    let tags = filterResults.tags;
    let ratings = filterResults.rating;

    // If this is the initial generation, save the initial filters
    if (isInitial) {
        initialCityFilters = cities;
        currentCityFilters = [];

        initialTagFilters = tags;
        currentTagFilters = [];

        initialRatingFilters = ratings;
        currentRatingFilters = [];

        // Create the filters
        createFilters(cities, tags, ratings);
    }
    else {
        // Update the filters
        updateFilters(cities, tags, ratings);
    }

    sortFiltersOnCount();
    hideAndShowFilters();
}

function filterOnCity(city) {
    if (currentCityFilters.includes(city))
        currentCityFilters.splice(currentCityFilters.indexOf(city), 1);
    else
        currentCityFilters.push(city);

    executeFilters();
}

function filterOnTag(tag) {
    if (currentTagFilters.includes(tag))
        currentTagFilters.splice(currentTagFilters.indexOf(tag), 1);
    else
        currentTagFilters.push(tag);

    executeFilters();
}

function filterOnRating(rating) {
    if (currentRatingFilters.includes(rating))
        currentRatingFilters.splice(currentRatingFilters.indexOf(rating), 1);
    else
        currentRatingFilters.push(rating);

    executeFilters();
}

function executeFilters() {
    // Get all the search results
    let results = searchResultsList.getElementsByTagName('a');

    // Loop through all the search results
    for (let i = 0; i < results.length; i++) {
        let result = results[i];

        // Hide the current search result if it doesn't match the filters
        if (!shouldBeVisible(result))
            result.style.display = 'none';
        else
            result.style.display = 'block';
    }

    // Generate the filters
    let leftoverFilterResults = getFilterResults();
    updateFilters(leftoverFilterResults.cities, leftoverFilterResults.tags,leftoverFilterResults.rating);
    sortFiltersOnZero();
    hideAndShowFilters();
}

function shouldBeVisible(searchResult) {
    // Get the city and tags of the search result
    let city = searchResult.getElementsByTagName('p')[0].innerHTML.split(' ')[0];
    let resultTags = searchResult.getElementsByTagName('h3')[0].innerHTML.split(' | ');
    let rating = searchResult.getElementsByClassName('rating')[0].innerHTML;

    // If there are city filters the city filters should contain the city of the searchResult
    if (currentCityFilters.length > 0 && !currentCityFilters.includes(city))
        return false;

    // If there are tag filters + The searchResult should contain all the tag filters
    if (currentTagFilters.length > 0)
        for (let i = 0; i < currentTagFilters.length; i++)
            if (!resultTags.includes(currentTagFilters[i]))
                return false;

    // If there are rating filters + The searchResult should contain all the rating filters
    if (currentRatingFilters.length > 0 && !currentRatingFilters.includes(rating))
        return false;

    return true;
}

function getFilterResults() {
    // Initialize the cities and tags arrays
    let cities = {};
    let tags = {};
    let ratings = {};

    // Get all the search results
    let results = searchResultsList.getElementsByTagName('a');

    // Loop through all the search results
    for (let i = 0; i < results.length; i++) {
        let result = results[i];

        // Skip if the current result already is hidden
        if (result.style.display === 'none') continue;

        // Get the city and tags of the current search result
        let city = result.getElementsByTagName('p')[0].innerHTML.split(' ')[0];
        let resultTags = result.getElementsByTagName('h3')[0].innerHTML.split(' | ');
        let rating = result.getElementsByClassName('rating')[0].innerHTML;

        // Add the city to the array if it doesn't exist yet, else increment the count
        if (cities[city] === undefined)
            cities[city] = 1;
        else
            cities[city]++;

        // Loop through all the tags of the current search result
        for (let j = 0; j < resultTags.length; j++) {
            let tag = resultTags[j];

            // Add the tag to the array if it doesn't exist yet, else increment the count
            if (tags[tag] === undefined)
                tags[tag] = 1;
            else
                tags[tag]++;
        }

        // Add the rating to the array if it doesn't exist yet, else increment the count
        if (ratings[rating] === undefined)
            ratings[rating] = 1;
        else
            ratings[rating]++;
    }

    return {cities: cities, tags: tags, rating: ratings};
}

function updateFilters(cities, tags, rating) {
    // Show the filters
    updateCityFilters(cities);
    updateTagFilters(tags);
    updateRatingFilters(rating);
}

function createFilters(cities, tags, rating) {
    // Create the filters
    createCityFilters(cities);
    createTagFilters(tags);
    createRatingFilters(rating);
}

function updateCityFilters(cities) {
    // Get the city filter div and check if it exists
    let cityFilterDiv = document.getElementsByClassName('city-filter')[0];
    if (cityFilterDiv === undefined || cityFilterDiv === null) return;

    // Get all the filters
    let filters = cityFilterDiv.getElementsByClassName('filter-item');

    // Loop through all the filters
    for (let i = 0; i < filters.length; i++) {
        let filter = filters[i];

        // Get the input of the filter
        let input = filter.getElementsByTagName('input')[0];
        let label = filter.getElementsByTagName('label')[0];

        // If the current filter is in the current filters, enable it
        input.disabled = cities[input.name.replace("city", "")] === undefined;
        label.innerHTML = label.innerHTML.split(' ')[0] + ' (' + cities[input.name.replace("city", "")] + ')';
        if (label.innerHTML.includes(' (undefined)'))
            label.innerHTML = label.innerHTML.replace('(undefined)', '(0)');
    }
}

function createCityFilters() {
    // Get the city filter div and check if it exists
    let cityFilterDiv = document.getElementsByClassName('city-filter')[0];
    if (cityFilterDiv === undefined || cityFilterDiv === null) return;

    // Clear the current filters
    let filters = cityFilterDiv.getElementsByClassName('filter-item');
    while (filters.length > 0)
        filters[0].remove();

    // Add all the filters
    for (let initialCityFilter in initialCityFilters)
        cityFilterDiv.appendChild(createFilterItem('city', initialCityFilter, initialCityFilters[initialCityFilter]));
}

function updateTagFilters(tags) {
    // Get the tag filter div and check if it exists
    let tagFilterDiv = document.getElementsByClassName('tag-filter')[0];
    if (tagFilterDiv === undefined || tagFilterDiv === null) return;

    // Get all the filters
    let filters = tagFilterDiv.getElementsByClassName('filter-item');

    // Loop through all the filters
    for (let i = 0; i < filters.length; i++) {
        let filter = filters[i];

        // Get the input of the filter
        let input = filter.getElementsByTagName('input')[0];
        let label = filter.getElementsByTagName('label')[0];

        // If the current filter is in the current filters, enable it
        input.disabled = tags[input.name.replace("tag", "")] === undefined;
        label.innerHTML = label.innerHTML.split(' ')[0] + ' (' + tags[input.name.replace("tag", "")] + ')';
        if (label.innerHTML.includes(' (undefined)'))
            label.innerHTML = label.innerHTML.replace('(undefined)', '(0)');
    }
}

function createTagFilters() {
    // Get the tag filter div and check if it exists
    let tagFilterDiv = document.getElementsByClassName('tag-filter')[0];
    if (tagFilterDiv === undefined || tagFilterDiv === null) return;

    // Clear the current filters
    let filters = tagFilterDiv.getElementsByClassName('filter-item');
    while (filters.length > 0)
        filters[0].remove();

    // Add all the filters
    for (let initialTagFilter in initialTagFilters)
        tagFilterDiv.appendChild(createFilterItem('tag', initialTagFilter, initialTagFilters[initialTagFilter]));
}

function updateRatingFilters(ratings) {
    // Get the rating filter div and check if it exists
    let ratingFilterDiv = document.getElementsByClassName('rating-filter')[0];
    if (ratingFilterDiv === undefined || ratingFilterDiv === null) return;

    // Get all the filters
    let filters = ratingFilterDiv.getElementsByClassName('filter-item');

    // Loop through all the filters
    for (let i = 0; i < filters.length; i++) {
        let filter = filters[i];

        // Get the input of the filter
        let input = filter.getElementsByTagName('input')[0];
        let label = filter.getElementsByTagName('label')[0];

        // If the current filter is in the current filters, enable it
        input.disabled = ratings[input.name.replace("rating", "")] === undefined;
        label.innerHTML = label.innerHTML.split(' ')[0] + ' (' + ratings[input.name.replace("rating", "")] + ')';
        if (label.innerHTML.includes(' (undefined)'))
            label.innerHTML = label.innerHTML.replace('(undefined)', '(0)');
    }
}

function createRatingFilters() {
    // Get the rating filter div and check if it exists
    let ratingFilterDiv = document.getElementsByClassName('rating-filter')[0];
    if (ratingFilterDiv === undefined || ratingFilterDiv === null) return;

    // Clear the current filters
    let filters = ratingFilterDiv.getElementsByClassName('filter-item');
    while (filters.length > 0)
        filters[0].remove();

    // Add all the filters
    for (let initialRatingFilter in initialRatingFilters)
        ratingFilterDiv.appendChild(createFilterItem('rating', initialRatingFilter, initialRatingFilters[initialRatingFilter]));
}

function createFilterItem(name, value, count, visible = true) {
    let container = document.createElement('div');
    container.classList.add('filter-item');

    let input = document.createElement('input');
    input.type = 'checkbox';
    input.name = name + value;
    input.id = name + value;
    if (!visible) input.style.display = 'none';

    if (name === 'city')
        input.addEventListener('change', e => filterOnCity(value));
    else if (name === 'tag')
        input.addEventListener('change', e => filterOnTag(value));
    else if (name === 'rating')
        input.addEventListener('change', e => filterOnRating(value));

    let label = document.createElement('label');
    label.htmlFor = name + value;
    label.innerHTML = value + ' (' + count + ')';

    container.appendChild(input);
    container.appendChild(label);

    return container;
}

function sortFiltersOnCount() {
    sortFilterOnCount('city-filter');
    sortFilterOnCount('tag-filter');
}

function sortFiltersOnZero() {
    sortFilterOnCount('city-filter', true);
    sortFilterOnCount('tag-filter', true);
    sortFilterOnCount('rating-filter', true);
}

function sortFilterOnCount(filterClassName, onZero = false) {
    // Get the city filter div and check if it exists
    let filterDiv = document.getElementsByClassName(filterClassName)[0];
    if (filterDiv === undefined || filterDiv === null) return;

    // Clear the current filters
    let filters = filterDiv.getElementsByClassName('filter-item');

    // Sort the filters on count
    let sortedFilters = [];
    for (let i = 0; i < filters.length; i++) {
        let filter = filters[i];
        let label = filter.getElementsByTagName('label')[0];
        let splitLabel = label.innerHTML.split(' ');
        let count = parseInt(splitLabel[splitLabel.length - 1].replace('(', '').replace(')', ''));
        if (onZero && count > 0) count = 1;

        sortedFilters.push({node: filter, count: count});
    }
    sortedFilters.sort((a, b) => b.count - a.count);

    // Remove the current filters
    while (filters.length > 0)
        filters[0].remove();

    // Add the sorted filters
    for (let i = 0; i < sortedFilters.length; i++)
        filterDiv.appendChild(sortedFilters[i].node);
}

function hideAndShowFilters() {
    hideAndShowFiltersFromDiv('city');
    hideAndShowFiltersFromDiv('tag');
    hideAndShowFiltersFromDiv('rating');
}

function hideAndShowFiltersFromDiv(category) {
    let filterDiv = document.getElementsByClassName(category + '-filter')[0];
    let filters = filterDiv.getElementsByClassName('filter-item');

    let containsHiddenElements = false;

    for (let i = 0; i < filters.length; i++) {
        let filter = filters[i];
        let input = filter.getElementsByTagName('input')[0];
        let label = filter.getElementsByTagName('label')[0];
        if (input.disabled || i > MAX_FILTERS - 1) {
            filter.style.display = 'none';
            containsHiddenElements = true;
        }
        else
            filter.style.display = 'flex';
    }

    if (containsHiddenElements)
        document.getElementById(category + '-filter-show-all').style.display = 'block';
    else
        document.getElementById(category + '-filter-show-all').style.display = 'none';
}

function toggleShowAll(category) {
    let showAllButton = document.getElementById(category + '-filter-show-all');

    // Show or hide the filters
    let filterDiv = document.getElementsByClassName(category + '-filter')[0];
    let filters = filterDiv.getElementsByClassName('filter-item');

    if (showAllButton.innerHTML === 'Show all ⇩')
        for (let i = 0; i < filters.length; i++)
            filters[i].style.display = 'flex';
    else
        hideAndShowFiltersFromDiv(category);

    // Swap the text to show less or show all
    if (showAllButton.innerHTML === 'Show all ⇩')
        showAllButton.innerHTML = 'Show less ⇧';
    else
        showAllButton.innerHTML = 'Show all ⇩';
}

// On document load, generate the filters
document.addEventListener('DOMContentLoaded', function() {
    generateFilters(true);
});


// On show all press, show all the filters of that category
document.getElementById('city-filter-show-all').addEventListener('click', function() {
    toggleShowAll('city');
});

document.getElementById('tag-filter-show-all').addEventListener('click', function() {
    toggleShowAll('tag');
});

document.getElementById('rating-filter-show-all').addEventListener('click', function() {
    toggleShowAll('rating');
});