/* EXTRA INFO FOR FOODITEMS */
let currFoodItemExtraInfo = null;
let currFoodItem = null;

function openExtraInfo(foodItem) {
    currFoodItem = foodItem;
    showLoadingExtraInfo(foodItem);

    let modal = document.getElementById('extraInfoModal');

    if (modal.getElementsByClassName('name')[0].innerHTML ===
        foodItem.getElementsByClassName('info')[0].getElementsByTagName('h2')[0].innerHTML)
    {
        openModalDirect(modal);
        hideLoadingExtraInfo(foodItem);
    }
    else
        currFoodItemExtraInfo = getExtraInfo(foodItem);
}

function showLoadingExtraInfo(foodItem) {
    let icon = foodItem.getElementsByClassName('extra-info-icon')[0];
    icon.src = '../Gifs/loading.gif';
    icon.classList.add('extra-info-loading-icon');
}

function hideLoadingExtraInfo(foodItem) {
    // If the user is the owner, use that function instead
    if (typeof hideLoadingExtraInfoEdit === 'function') {
        hideLoadingExtraInfoEdit(foodItem);
        return;
    }

    let icon = foodItem.getElementsByClassName('extra-info-icon')[0];
    icon.src = '../Icons/info.png';
    icon.classList.remove('extra-info-loading-icon');
}

function setExtraInfo(extraInfo, modal) {
    // If the user is the owner, use that function instead
    if (typeof setExtraInfoEdit === 'function') {
        setExtraInfoEdit(extraInfo, modal);
        return;
    }

    modal.getElementsByClassName('name')[0].innerHTML = extraInfo.name;
    modal.getElementsByClassName('rating')[0].innerHTML = viewRating(extraInfo.rating);
    modal.getElementsByClassName('description')[0].innerHTML = extraInfo.description;
    modal.getElementsByClassName('ingredients')[0].innerHTML = extraInfo.ingredients;
    modal.getElementsByClassName('primary-image-container')[0].innerHTML = extraInfo.baseImg;

    // Clear media
    let media = modal.getElementsByClassName('media')[0];
    while (media.firstChild) {
        media.removeChild(media.firstChild);
    }

    // Add media


    for (let i = 0; i < extraInfo.media.length; i++) {
        let mediaItem = document.createElement('div');
        mediaItem.classList.add('media-item');
        mediaItem.innerHTML = extraInfo.media[i];

        media.appendChild(mediaItem);
    }
}

function getExtraInfo(foodItem) {
    let info = foodItem.getElementsByClassName('info')[0];

    let extraInfo = {
        'name': info.getElementsByTagName('h2')[0].innerHTML,
        'description': info.getElementsByTagName('p')[0].innerHTML,
        'ingredients': info.getElementsByTagName('p')[1].innerHTML,
        'price': foodItem.getElementsByClassName('price')[0].getElementsByTagName('span')[0].innerHTML,
        'baseImg': foodItem.getElementsByClassName('image-container')[0].innerHTML,
        'media': [],
        'reviews': [],
        'rating': 0
    }

    // Get extra info from server
    sendExtraInfoRequest(extraInfo.name);

    return extraInfo;
}

function sendExtraInfoRequest(foodItemName) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/getExtraFoodItemInfo", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulExtraInfoRequest, handleExtraInfoRequestError);

    // Sending the request
    xmlHttp.send(JSON.stringify({foodItemName: foodItemName}));
}

function successfulExtraInfoRequest(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);

    // Check if the server send a success message
    if (!jsonResponse.success)
        handleExtraInfoRequestError(jsonResponse.error);

    // If the server send a success message, display the results
    else {
        currFoodItemExtraInfo.media = jsonResponse.media;
        currFoodItemExtraInfo.reviews = jsonResponse.reviews;
        currFoodItemExtraInfo.rating = jsonResponse.rating;

        // Clear reviews
        removeChildren(document.getElementById('foodItemReviewsList'));

        let modal = document.getElementById('extraInfoModal');
        setExtraInfo(currFoodItemExtraInfo, modal);
        openModalDirect(modal);

        // Hide loading icon
        hideLoadingExtraInfo(currFoodItem);
    }
}

function handleExtraInfoRequestError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    console.log(errorMsg);

    hideLoadingExtraInfo(currFoodItem);
}

/* Open Extra Info on extraInfo button click */
let extraInfoButtons = document.getElementsByClassName('extraInfo');
for (let i = 0; i < extraInfoButtons.length; i++) {
    extraInfoButtons[i].addEventListener('click', (event) => {
        openExtraInfo(event.target.closest('.food-item'));
    }, false);
}