/* ADDING FOOD ITEMS TO CART */

// Sending the food item add request
let currAddIcon = null;

function addFoodItemToCart(foodItemName, foodItemAmount) {
    showLoadingIcon();

    // Getting the foodtruck id
    const foodtruckId = document.getElementById("foodtruckId").innerHTML;

    // Check validity
    if (!isValidFoodItem(foodItemName, foodItemAmount, foodtruckId)) {
        handleFoodItemAddingErrors("Cannot add this food item to the cart, please try again later!");
        return;
    }

    // Send the request
    sendFoodItemAddRequest(foodtruckId, foodItemName, foodItemAmount);
}

function sendFoodItemAddRequest(foodtruckId, foodItemName, foodItemAmount) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/cart/add", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulAdd, handleFoodItemAddingErrors);

    // Sending the request
    xmlHttp.send(JSON.stringify({foodtruckId: foodtruckId, foodName: foodItemName, amount: foodItemAmount}));
}

function successfulAdd(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);

    // Check if the server send a success message
    if (!jsonResponse.success)
        handleFoodItemAddingErrors(jsonResponse.error);

    // If the server send a success message, show a confirmation message
    else {
        // Show success message
        showSuccessfulAdd(jsonResponse.foodItemName, jsonResponse.amount);
    }
}

// Checking the food item validity
function isValidFoodItem(foodItemName, foodItemAmount, foodtruckId) {
    return  !(foodItemAmount === undefined || foodItemAmount === null || foodItemAmount === "" || foodItemAmount === 0) &&
            !(foodItemName === undefined || foodItemName === null || foodItemName === "") &&
            !(foodtruckId === undefined || foodtruckId === null || foodtruckId === "");
}

// Handle food item adding errors
function handleFoodItemAddingErrors(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText

    displayMessage("Error", errorMsg);
}

// Sending success message
function showSuccessfulAdd(foodItemName, foodItemAmount) {
    displayMessage("Added " + foodItemName, "Successfully added " + foodItemAmount + "x " + foodItemName + " to the shopping cart!");
}

// Sending a message

function displayMessage(title, message) {
    // Hide the loading icon
    hideLoadingIcon();
    
    const modal = document.getElementById("messageModal");

    // Setting the title & message
    modal.getElementsByClassName("message-title")[0].innerHTML = title;
    modal.getElementsByClassName("message-content")[0].innerHTML = message;

    // Show the modal
    openModalDirect(modal);
}

// Loading icon

function showLoadingIcon() {
    if (currAddIcon !== null) {
        currAddIcon.src = "../Gifs/loading.gif";
        currAddIcon.style = "filter: grayscale(1) brightness(1.2);"
    }
}

function hideLoadingIcon() {
    if (currAddIcon !== null) {
        currAddIcon.src = "../Icons/add.png";
        currAddIcon.style = "filter: invert(1) brightness(1);"
    }
}

// Adding functionality to add food items button

const addFoodItemButtons = document.getElementsByClassName("add-food-item-button");
for (let i = 0; i < addFoodItemButtons.length; i++) {
    addFoodItemButtons[i].addEventListener("click", function (event) {
        event.preventDefault();

        // Get the food item name & amount
        const foodItemName = event.target.closest('.food-item').getElementsByClassName('info')[0].getElementsByTagName('h2')[0].innerHTML;
        const foodItemAmount = event.target.closest('.price').getElementsByTagName('input')[0].value;

        // Set the current add icon
        currAddIcon = addFoodItemButtons[i].getElementsByTagName("img")[0];

        // Add the food item to the cart
        addFoodItemToCart(foodItemName, foodItemAmount);
    });
}