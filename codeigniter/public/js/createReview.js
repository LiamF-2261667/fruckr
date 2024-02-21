const form = document.getElementById('createFoodtruckReview');
const item = form.getElementsByClassName('create-review-food-item')[0];
const rating = form.getElementsByClassName('create-review-rating')[0];
const title = form.getElementsByClassName('create-review-title')[0];
const content = form.getElementsByClassName('create-review-content')[0];
const submit = form.getElementsByClassName('create-review-submit')[0];

const loadingIcon = document.getElementById('loadingIcon');
const successBox = document.getElementById('successBox');

form.addEventListener('submit', (e) => {
    e.preventDefault();
    createReview();
});

// Submit review

function createReview() {
    // Hide the success box, error box and loading icon
    showLoadingIcon();
    hideError('errorBox');
    hideError('successBox');

    // Check if there are any items left to review
    if (item.options.length === 0) {
        handleReviewError('No more items to review!');
        return;
    }

    const review = {
        foodName: item.value,
        rating: rating.value,
        title: title.value,
        content: content.value
    };

    if (!validateReview(review))
        return;

    sendReview(review);
}

function showLoadingIcon() {
    loadingIcon.style.display = 'block';
}

function hideLoadingIcon() {
    loadingIcon.style.display = 'none';
}

function validateReview(review) {
    // make sure there is content if there is a title set
    if (review.title !== "" && review.content === "") {
        handleReviewError("Please enter a message!");
        return false;
    }

    // make sure there is a title if there is content set
    if (review.content !== "" && review.title === "") {
        handleReviewError("Please enter a title!");
        return false;
    }

    // max length of title is 255
    if (review.title.length > 255) {
        handleReviewError("Title is too long, use a max of 255 characters!");
        return false;
    }

    // max length of content is 1000
    if (review.content.length > 1000) {
        handleReviewError("Message is too long, use a max of 1000 characters!");
        return false;
    }

    return true;
}

function sendReview(review) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/review/create", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfulReviewCreation, handleReviewError);

    // Sending the request
    xmlHttp.send(JSON.stringify(review));
}

function handleReviewError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('errorBox', errorMsg);
    hideLoadingIcon();
}

function onSuccessfulReviewCreation(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleReviewError(jsonResponse.error);
    else {
        // Display success message
        successBox.style.display = 'block';

        // Set the success message
        successBox.innerHTML = '<h2>Successfully created review!</h2>' + '<div class="review-item">' + jsonResponse.review + '</div>';

        // Remove the review option from the select
        removeReviewOption(jsonResponse.item);

        // Reset the inputs
        resetInputs();

        // Hide the loading icon
        hideLoadingIcon();
    }
}

function removeReviewOption(name) {
    let options = item.options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === name) {
            item.options[i].remove();
            break;
        }
    }
}

function resetInputs() {
    if (item.options.length === 0)
        item.disabled = true;
    else
        item.value = item.options[0].value;

    rating.value = "";
    title.value = "";
    content.value = "";
}