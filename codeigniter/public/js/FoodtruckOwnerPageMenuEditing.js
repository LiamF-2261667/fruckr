let oldFoodItemName = null;

function setExtraInfoEdit(extraInfo, modal) {
    hideAllErrors();

    // Make sure the modal is in save mode by default
    document.getElementById('extraInfoModalSaveButton').innerHTML = 'Save';

    // Make sure the delete section is shown by default
    document.getElementById('delete-section').style.display = 'block';

    // Clear the reviews
    removeChildren(document.getElementById('foodItemReviewsList'));

    modal.getElementsByClassName('name')[0].value = extraInfo.name;
    modal.getElementsByClassName('rating')[0].innerHTML = viewRating(extraInfo.rating);
    modal.getElementsByClassName('description')[0].value = extraInfo.description;
    modal.getElementsByClassName('price')[0].value = extraInfo.price.replace('€', '');
    modal.getElementsByClassName('primary-image-container')[0].innerHTML = extraInfo.baseImg;
    setIngredientsEdit(extraInfo.ingredients, modal);
    setMedias(extraInfo.media, modal);

    oldFoodItemName = extraInfo.name;
}

function setIngredientsEdit(ingredients, modal) {
    // Preparing the necessary variables
    let ingredientsTable = modal.getElementsByClassName('ingredients')[0];
    let ingredientsTableAddRow = ingredientsTable.getElementsByClassName('add-row')[0];

    let ingredientsList = ingredients.split(', ');

    // Clear ingredients table
    while (!ingredientsTableAddRow.previousElementSibling.classList.contains('table-header')) {
        ingredientsTableAddRow.previousElementSibling.remove();
    }

    // Add ingredients to the table
    for (let i = 0; i < ingredientsList.length; i++) {
        if (ingredientsList[i].trim() !== '')
            addIngredient(ingredientsList[i], ingredientsTableAddRow);
    }
}

function addIngredient(ingredient, ingredientsTableAddRow) {
    let row = document.createElement('tr');

    // Create the ingredient cell
    let ingredientCell = document.createElement('td');
    ingredientCell.innerHTML = ingredient;
    ingredientCell.classList.add('ingredients-item');

    // Add the cells to the row
    row.appendChild(ingredientCell);
    row.appendChild(createRemoveButtonCell());

    // Add the row to the table
    ingredientsTableAddRow.insertAdjacentElement('beforebegin', row);
}

function getAllIngredients() {
    let ingredientsElements = document.getElementsByClassName('ingredients-item');
    let ingredients = [];

    for (let i = 0; i < ingredientsElements.length; i++)
        ingredients.push(ingredientsElements[i].innerHTML);

    return ingredients;

}

function setMedias(medias, modal) {
    let mediaTable = modal.getElementsByClassName('media')[0];
    let mediaTableAddRow = mediaTable.getElementsByClassName('add-row')[0];

    // Clear media
    while (!mediaTableAddRow.previousElementSibling.classList.contains('table-header'))
        mediaTableAddRow.previousElementSibling.remove();

    // Add media
    for (let i = 0; i < medias.length; i++)
        addMedia(medias[i], mediaTableAddRow);
}

function addMedia(media, mediaTableAddRow) {
    let row = document.createElement('tr');

    // Create the media cell
    let mediaCell = document.createElement('td');
    mediaCell.innerHTML = media;
    mediaCell.classList.add('media-item');

    // Add the cells to the row
    row.appendChild(mediaCell);
    row.appendChild(createRemoveButtonCell());

    // Add the row to the table
    mediaTableAddRow.insertAdjacentElement('beforebegin', row);
}

function getTotalMediaSize() {
    let mediaElements = document.getElementsByClassName('media-item');
    let totalMediaSize = 0;

    for (let i = 0; i < mediaElements.length; i++) {
        if (mediaElements[i].getElementsByTagName('img').length > 0)
            totalMediaSize += mediaElements[i].getElementsByTagName('img')[0].src.length * (3/4);
        else
            totalMediaSize += mediaElements[i].getElementsByTagName('source')[0].src.length * (3/4);
    }

    return totalMediaSize;
}

function getAllMediaFiles() {
    let mediaElements = document.getElementsByClassName('media-item');
    let mediaFiles = [];

    for (let i = 0; i < mediaElements.length; i++) {
        if (mediaElements[i].getElementsByTagName('img').length > 0)
            mediaFiles.push({
                src: srcToBase64(mediaElements[i].getElementsByTagName('img')[0].src),
                type: 'IMG'
            });
        else
            mediaFiles.push({
                src: srcToBase64(mediaElements[i].getElementsByTagName('source')[0].src),
                type: 'VID'
            });
    }

    return mediaFiles;
}

function createRemoveButtonCell() {
    let removeBtnCell = document.createElement('td');
    let removeBtn = document.createElement('button');
    removeBtn.classList.add('logo-button');
    removeBtn.innerHTML = '<img src="../Icons/minus.png" alt="remove icon">';
    removeBtn.addEventListener('click', function() {
        removeBtn.parentElement.parentElement.remove();
    });
    removeBtnCell.appendChild(removeBtn);

    return removeBtnCell;
}

const extraInfoModal = document.getElementById('extraInfoModal');
const ingredientsTable = extraInfoModal.getElementsByClassName('ingredients')[0];
const addIngredientsRow = ingredientsTable.getElementsByClassName('add-row')[0];

// Make the add buttons inside the food editor work
function addIngredientButtonFucntionality() {

    addIngredientsRow.getElementsByTagName('button')[0].addEventListener('click', function(event) {
        event.preventDefault();

        hideError('ingredientsError');

        let ingredient = addIngredientsRow.getElementsByTagName('input')[0].value;
        if (isValidIngredient(ingredient)) {
            addIngredient(ingredient, addIngredientsRow);
            addIngredientsRow.getElementsByTagName('input')[0].value = '';
        }

        addIngredientsRow.getElementsByTagName('input')[0].focus();
    });
}

function isValidIngredient(ingredient) {
    if (ingredient.trim() === '') {
        displayError('ingredientsError', 'Please enter an ingredient.');
        return false;
    }

    let regex = '[a-zA-Z éçèà\-]+';
    if (!ingredient.match(regex)) {
        displayError('ingredientsError', 'Invalid ingredient, only letters, spaces and dashes are allowed.');
        return false;
    }

    return true;
}

let currMediaAddFile = null;
function addMediaButtonFunctionality() {
    let extraInfoModal = document.getElementById('extraInfoModal');
    let mediaTable = extraInfoModal.getElementsByClassName('media')[0];
    let addRow = mediaTable.getElementsByClassName('add-row')[0];

    addRow.getElementsByTagName('button')[0].addEventListener('click', function(event) {
        event.preventDefault();

        hideError('mediaError');

        if (isValidMedia(currMediaAddFile)) {
            addMedia(fileToHtml(currMediaAddFile), addRow);
            currMediaAddFile = null;
            addRow.getElementsByTagName('input')[0].value = '';
        }
    });

    function isValidMedia(file) {
        if (file === null) {
            displayError('mediaError', 'Please select a file.');
            return false;
        }

        if (getElementTagFromFileType(file.type) === 'div' || getElementTagFromFileType(file.type) === 'audio') {
            displayError('mediaError', 'Invalid file type, only .png, .jpg, .jpeg, .mp4 are allowed.');
            return false;
        }

        if (file.size > 10000000) {
            displayError('mediaError', 'Please select a file smaller than 10MB.');
            return false;
        }

        if (getTotalMediaSize() + file.size > 10000000) {
            displayError('mediaError', 'The total size of the media cannot exceed 10MB.');
            return false;
        }

        return true;
    }

    readFileOnChange(addRow.getElementsByTagName('input')[0], function(file) {
        currMediaAddFile = file;
    });
}

function changePrimaryImageFunctionality() {
    let extraInfoModal = document.getElementById('extraInfoModal');
    let primaryImageContainer = extraInfoModal.getElementsByClassName('primary-image-container')[0];

    function isValidPrimaryImage(file) {
        if (file === null) {
            displayError('primaryImageError', 'Please select a file.');
            return false;
        }

        if (getElementTagFromFileType(file.type) !== 'img') {
            displayError('primaryImageError', 'Invalid file type, only .png, .jpg, .jpeg are allowed.');
            return false;
        }

        if (file.size > 3000000) {
            displayError('primaryImageError', 'Please select a file smaller than 3MB.');
            return false;
        }

        return true;
    }

    readFileOnChange(document.getElementById('primary-image-change-input'), function(file) {
        hideError('primaryImageError');

        if (isValidPrimaryImage(file))
            primaryImageContainer.innerHTML = fileToHtml(file);
    });
}

addIngredientButtonFucntionality();
addMediaButtonFunctionality();
changePrimaryImageFunctionality();

// Ignore enters for form submission
let inputfields = extraInfoModal.getElementsByTagName('input');
for (let i = 0; i < inputfields.length; i++) {
    inputfields[i].addEventListener('keypress', function(e) {
        if (e.keyCode === 13)
            e.preventDefault();
    });
}

document.getElementById('addIngredientField').addEventListener('keypress', function(e) {
    if (e.keyCode === 13) {
        e.preventDefault();

        hideError('ingredientsError');

        let ingredient = addIngredientsRow.getElementsByTagName('input')[0].value;
        if (isValidIngredient(ingredient)) {
            addIngredient(ingredient, addIngredientsRow);
            addIngredientsRow.getElementsByTagName('input')[0].value = '';
        }
    }
});

// Change the extra info icon to the edit icon
let extraInfoIcons = document.getElementsByClassName('extra-info-icon');

for (let i = 0; i < extraInfoIcons.length; i++)
    changeExtraInfoIconToEdit(extraInfoIcons[i]);

function changeExtraInfoIconToEdit(iconElement) {
    iconElement.src = '../Icons/edit.png';
}

function hideLoadingExtraInfoEdit(foodItem) {
    let icon = foodItem.getElementsByClassName('extra-info-icon')[0];
    icon.src = '../Icons/edit.png';
    icon.classList.remove('extra-info-loading-icon');
}

/* Saving food item */

function getFoodItemData() {
    let modal = document.getElementById('extraInfoModal');

    return {
        name: modal.getElementsByClassName('name')[0].value,
        description: modal.getElementsByClassName('description')[0].value,
        ingredients: getAllIngredients(),
        price: modal.getElementsByClassName('price')[0].value,
        image: srcToBase64(modal.getElementsByClassName('primary-image-container')[0].getElementsByTagName('img')[0].src),
        media: getAllMediaFiles()
    };
}

function validateFoodItemData(foodItemData) {
    // name is required
    if (foodItemData.name.trim() === '') {
        displayError('foodItemSaveError', 'Please enter a name.');
        return false;
    }

    // Name may only contain letters, numbers, spaces and dashes
    let regex = '[a-zA-Z0-9 éçèà\-]+';
    if (!foodItemData.name.match(regex)) {
        displayError('foodItemSaveError', 'Invalid name, only letters, numbers, spaces and dashes are allowed.');
        return false;
    }

    // Name may not be longer than 50 characters
    if (foodItemData.name.length > 50) {
        displayError('foodItemSaveError', 'Name may not be longer than 50 characters.');
        return false;
    }

    // Description is required
    if (foodItemData.description.trim() === '') {
        displayError('foodItemSaveError', 'Please enter a description.');
        return false;
    }

    // Description may not be longer than 500 characters
    if (foodItemData.description.length > 500) {
        displayError('foodItemSaveError', 'Description may not be longer than 500 characters.');
        return false;
    }

    // The price is required
    if (foodItemData.price.trim() === '') {
        displayError('foodItemSaveError', 'Please enter a price.');
        return false;
    }

    // The price must be positive
    if (foodItemData.price < 0) {
        displayError('foodItemSaveError', 'Please enter a positive price.');
        return false;
    }

    // All ingredients
    for (let i = 0; i < foodItemData.ingredients.length; i++) {
        // may not be empty strings
        if (foodItemData.ingredients[i].trim() === '') {
            displayError('foodItemSaveError', 'Please enter a valid ingredient.');
            return false;
        }

        // May not be longer than 50 characters
        if (foodItemData.ingredients[i].length > 50) {
            displayError('foodItemSaveError', 'Ingredient may not be longer than 50 characters.');
            return false;
        }

        // May only contain letters, spaces and dashes
        let regex = '[a-zA-Z éçèà\-]+';
        if (!foodItemData.ingredients[i].match(regex)) {
            displayError('foodItemSaveError', 'Invalid ingredient, only letters, spaces and dashes are allowed.');
            return false;
        }
    }

    // primary image is required
    if (foodItemData.image === null || foodItemData.image.includes('missingImage.png')) {
        displayError('foodItemSaveError', 'Please select a primary image.');
        return false;
    }

    return true;
}

function handleFoodItemSaveError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText

    displayError('foodItemSaveError', errorMsg);
    hideFoodItemSaveLoadingIcon();
}

function successfulSaveFoodItem(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);

    // Check if the server send a success message
    if (!jsonResponse.success)
        handleFoodItemSaveError(jsonResponse.error);

    // If the server send a success message, close the modal & reset
    else {
        // Change the food item in the menu
        updateFoodItemElement(oldFoodItemName, jsonResponse.foodItemHtml);

        hideFoodItemSaveLoadingIcon();
        closeModal('extraInfoModal');
        currFoodItemExtraInfo = null;
        currFoodItem = null;
        oldFoodItemName = null;
        document.getElementById('extraInfoModalSaveButton').innerHTML = 'Save';
    }
}

function successfulCreateFoodItem(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);

    // Check if the server send a success message
    if (!jsonResponse.success)
        handleFoodItemSaveError(jsonResponse.error);

    // If the server send a success message, close the modal & reset
    else {
        // Change the food item in the menu
        addNewFoodItemElement(jsonResponse.foodItemHtml);

        hideFoodItemSaveLoadingIcon();
        closeModal('extraInfoModal');
        currFoodItemExtraInfo = null;
        currFoodItem = null;
        oldFoodItemName = null;
        document.getElementById('extraInfoModalSaveButton').innerHTML = 'Save';
    }
}

function updateFoodItemElement(oldFoodItemName, newFoodItemHtml) {
    let foodItems = document.getElementsByClassName('food-item');

    for (let i = 0; i < foodItems.length; i++) {
        if (foodItems[i].getElementsByClassName('info')[0].getElementsByTagName('h2')[0].innerHTML === oldFoodItemName) {
            foodItems[i].outerHTML = newFoodItemHtml;

            let extraInfoIcon = foodItems[i].getElementsByClassName('extra-info-icon')[0];

            extraInfoIcon.addEventListener('click', (event) => {
                openExtraInfo(event.target.closest('.food-item'));
            }, false);

            changeExtraInfoIconToEdit(extraInfoIcon);
            return;
        }
    }
}

function addNewFoodItemElement(newFoodItemHtml) {
    let newFoodItemElementContainer = document.createElement('div');
    newFoodItemElementContainer.innerHTML = newFoodItemHtml;

    // Insert the new food item element in the correct place
    addFoodItemElement.insertAdjacentElement('beforebegin', newFoodItemElementContainer.firstElementChild);

    let newFoodItemElement = addFoodItemElement.previousElementSibling;

    // Add the edit icon to the new food item
    let extraInfoIcon = newFoodItemElement.getElementsByClassName('extra-info-icon')[0];

    extraInfoIcon.addEventListener('click', (event) => {
        openExtraInfo(newFoodItemElement);
    }, false);

    changeExtraInfoIconToEdit(extraInfoIcon);
}

function showFoodItemSaveLoadingIcon() {
    setVisibility(document.getElementById('foodItemSaveLoading'), true);
}

function hideFoodItemSaveLoadingIcon() {
    setVisibility(document.getElementById('foodItemSaveLoading'), false);
}

function sendFoodItemSaveData(oldFoodItemName, foodItemData) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/saveFoodItem", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulSaveFoodItem, handleFoodItemSaveError);

    // Check if the old name is set
    if (oldFoodItemName === null) {
        handleFoodItemSaveError('An internal error happened while saving the food item, please try again later.');
        return;
    }

    // Sending the request
    xmlHttp.send(JSON.stringify({oldFoodItemName: oldFoodItemName, foodItemData: foodItemData}));
}

function sendFoodItemCreateData(foodItemData) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/createFoodItem", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulCreateFoodItem, handleFoodItemSaveError);

    // Sending the request
    xmlHttp.send(JSON.stringify({foodItemData: foodItemData}));
}

function saveFoodItem() {
    showFoodItemSaveLoadingIcon();
    hideAllErrors();

    let foodItemData = getFoodItemData();
    if (validateFoodItemData(foodItemData)) {
        if (document.getElementById('extraInfoModalSaveButton').innerHTML === 'Create')
            sendFoodItemCreateData(foodItemData);
        else
            sendFoodItemSaveData(oldFoodItemName, foodItemData);
    }
    else
        hideFoodItemSaveLoadingIcon();
}

document.getElementById('extraInfoModal').addEventListener('submit', function(event) {
    event.preventDefault();
    saveFoodItem();
});

/* CREATE NEW FOOD ITEM */

const addFoodItemElement = document.getElementById('add-food-item');

function addFoodItemElementFunctionality() {
    addFoodItemElement.addEventListener('click', function(event) {
        event.preventDefault();

        // Check if it isn't already in create mode
        if (document.getElementById('extraInfoModalSaveButton').innerHTML !== 'Create') {
            // Reset the modal
            setExtraInfoEdit({
                'name': '',
                'description': '',
                'ingredients': '',
                'price': '',
                'baseImg': '<img src="../Images/missingImage.png" alt="food item image">',
                'media': [],
                'reviews': [],
                'rating': 0
            }, document.getElementById('extraInfoModal'));

            // Change the save button to the create button
            document.getElementById('extraInfoModalSaveButton').innerHTML = 'Create';

            // Hide the delete section
            document.getElementById('delete-section').style.display = 'none';
        }

        // Open the modal
        hideAllErrors();
        openModal('extraInfoModal');
    });
}

addFoodItemElementFunctionality();

/* DELETE FOOD ITEM */

function deleteFoodItem() {
    showFoodItemSaveLoadingIcon();
    hideAllErrors();

    if (!validateDeletion())
        return;

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/deleteFoodItem", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulDeleteFoodItem, handleFoodItemSaveError);

    // Sending the request
    xmlHttp.send(JSON.stringify({foodItemName: oldFoodItemName}));
}

function successfulDeleteFoodItem(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);

    // Check if the server send a success message
    if (!jsonResponse.success)
        handleFoodItemSaveError(jsonResponse.error);

    // If the server send a success message, close the modal & reset
    else {
        // Reload the page
        location.reload();
    }
}

function validateDeletion() {
    // Check if the old name is set
    if (oldFoodItemName === null) {
        handleFoodItemSaveError('An internal error happened while deleting the food item, please try again later.');
        return false;
    }

    // Make sure the confirmation name and original name match
    if (document.getElementById('delete-name').value !== oldFoodItemName) {
        handleFoodItemSaveError('The name you entered does not match the food item name.');
        return false;
    }

    return true;
}

document.getElementById('extraInfoModalDeleteButton').addEventListener('click', function(event) {
    event.preventDefault();
    deleteFoodItem();
});