const foodtruckReviewsLoadingIcon = document.getElementById('foodtruckReviewsLoading');
const fooditemReviewsLoadingIcon = document.getElementById('fooditemReviewsLoading');
const foodtruckReviewList = document.getElementById('foodtruckReviews').getElementsByClassName('review-list')[0];
const fooditemReviewList = document.getElementById('foodItemReviewsList');

// Automatically load the reviews
getCurrentFoodtruckReviews();

/* CURRENT FOODTRUCK REVIEWS */
function getCurrentFoodtruckReviews() {
    // Show the loading icon && hide the error box
    showFoodtruckReviewsLoadingIcon();
    hideError('foodtruckReviewsErrorBox');

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/reviews/foodtruck", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfulFoodtruckReviewsLoading, handleFoodtruckReviewsError);

    // Sending the request
    xmlHttp.send();
}

function showFoodtruckReviewsLoadingIcon() {
    foodtruckReviewsLoadingIcon.style.display = 'block';
}

function hideFoodtruckReviewsLoadingIcon() {
    foodtruckReviewsLoadingIcon.style.display = 'none';
}

function handleFoodtruckReviewsError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('foodtruckReviewsErrorBox', errorMsg);
    hideFoodtruckReviewsLoadingIcon();
}

function onSuccessfulFoodtruckReviewsLoading(xhr) {
    try {
        //handle the response
        let jsonResponse = JSON.parse(xhr.responseText);
        if (!jsonResponse.success)
            handleFoodtruckReviewsError(jsonResponse.error);
        else {
            // Clear the list
            removeChildren(foodtruckReviewList);

            // Add the reviews to the list
            for (let i = 0; i < jsonResponse.reviews.length; i++)
                addReviewToReviewList(foodtruckReviewList, jsonResponse.reviews[i]);
            if (jsonResponse.reviews.length === 0)
                addNoReviewsMsgToList(foodtruckReviewList);

            // Hide the loading icon
            hideFoodtruckReviewsLoadingIcon();
        }
    }
    catch (err) {
        handleFoodtruckReviewsError(xhr.responseText);
    }
}

/* CURRENT FOODITEM REVIEWS */

// On load button press
document.getElementById('loadFoodItemReviews').addEventListener('click', (event) => {
    event.preventDefault();
    getCurrentFooditemReviews();
});

function getCurrentFooditemReviews() {
    // Show the loading icon && hide the error box
    showFooditemReviewsLoadingIcon();
    hideError('fooditemReviewsErrorBox');

    // Get the current food item name
    const foodName = getCurrFoodItemName();

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/reviews/foodtruck/foodItem", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfulFooditemReviewsLoading, handleFooditemReviewsError);

    // Sending the request
    xmlHttp.send(JSON.stringify({ foodName: foodName }));
}

function getCurrFoodItemName() {
    const currFoodItemNameElem = document.getElementById('currFoodItemName');

    return (
        currFoodItemNameElem.innerText === undefined ||
        currFoodItemNameElem.innerText === null ||
        currFoodItemNameElem.innerText === ""
    ) ? currFoodItemNameElem.value : currFoodItemNameElem.innerText;
}

function showFooditemReviewsLoadingIcon() {
    fooditemReviewsLoadingIcon.style.display = 'block';
}

function hideFooditemReviewsLoadingIcon() {
    fooditemReviewsLoadingIcon.style.display = 'none';
}

function handleFooditemReviewsError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('fooditemReviewsErrorBox', errorMsg);
    hideFooditemReviewsLoadingIcon();
}

function onSuccessfulFooditemReviewsLoading(xhr) {
    //handle the response
    try {
        let jsonResponse = JSON.parse(xhr.responseText);
        if (!jsonResponse.success)
            handleFooditemReviewsError(jsonResponse.error);
        else {
            // Clear the list
            removeChildren(fooditemReviewList);

            // Add the reviews to the list
            for (let i = 0; i < jsonResponse.reviews.length; i++)
                addReviewToReviewList(fooditemReviewList, jsonResponse.reviews[i]);
            if (jsonResponse.reviews.length === 0)
                addNoReviewsMsgToList(fooditemReviewList);

            // Hide the loading icon
            hideFooditemReviewsLoadingIcon();
        }
    }
    catch (err) {
        handleFooditemReviewsError(xhr.responseText);
    }
}

// creating review html elements

function addReviewToReviewList(reviewList, review) {
    let reviewElement = createReviewElement(review);
    reviewList.prepend(reviewElement);
}

function createReviewElement(review) {
    // Create the list item
    let li = document.createElement('li');
    li.classList.add('review-item');

    // Set the inner review
    li.innerHTML = review;

    return li;
}

function addNoReviewsMsgToList(reviewList) {
    // Create the no reviews msg li
    let li = document.createElement('li');
    li.classList.add('review-item');
    li.innerHTML = '<h1>No reviews exist</h1>';

    // Add it to the list
    reviewList.prepend(li);
}