// READY FUNCTIONALITY

const readyButtons = document.getElementsByClassName('ready-button');
for (let i = 0; i < readyButtons.length; i++) {
    readyButtons[i].onclick = function(event) {
        sendOrderReady(event.target.parentElement);
    };
}

function sendOrderReady(orderElement) {
    sendOrderConfirmation(orderElement, 'ready');
}

// RECEIVED FUNCTIONALITY

const receivedButtons = document.getElementsByClassName('received-button');
for (let i = 0; i < receivedButtons.length; i++) {
    receivedButtons[i].addEventListener('click', function(event) {
        sendOrderReceived(event.target.parentElement);
    })
}

function sendOrderReceived(orderElement) {
    sendOrderConfirmation(orderElement, 'received');
}

// Send order confirmation to server

function sendOrderConfirmation(orderElement, endpoint) {
    // Hide the error box && show loading
    hideError('errorBox');
    showLoadingIcon();

    // Getting the order id
    const orderId = orderElement.getElementsByClassName('order-object-title')[0].innerHTML.substring(7);

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/orders/" + endpoint, true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfulOrderConfirmation, handleOrderConfirmationError);

    // Sending the request
    xmlHttp.send(JSON.stringify({orderId: orderId}));
}

function onSuccessfulOrderConfirmation(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleOrderConfirmationError(jsonResponse.error);
    else {
        // Update the order status
        const orderElement = getOrderElement(jsonResponse.orderId);

        if (jsonResponse.ready) {
            moveOrderElementToReceivedFromReady(orderElement);
        }
        else if (jsonResponse.received) {
            removeOrderElementFromReceived(orderElement);
        }

        hideLoadingIcon();
    }
}

function handleOrderConfirmationError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('errorBox', errorMsg);
    hideLoadingIcon();
}

function showLoadingIcon() {
    document.getElementById('loadingIcon').style.display = 'block';
}

function hideLoadingIcon() {
    document.getElementById('loadingIcon').style.display = 'none';
}

// Functions to handle orders

function getOrderElement(orderId) {
    const allOrders = document.getElementsByClassName('order');
    for (let i = 0; i < allOrders.length; i++) {
        if (allOrders[i].getElementsByClassName('order-object-title')[0].innerHTML.substring(7) === orderId) {
            return allOrders[i];
        }
    }
}

function moveOrderElementToReceivedFromReady(orderElement) {
    // add the order element to the received orders
    const receivedOrders = document.getElementById('toBeReceived').getElementsByClassName('orders')[0];
    receivedOrders.appendChild(orderElement);

    // change the button
    const button = orderElement.getElementsByClassName('ready-button')[0];
    button.innerHTML = 'Has been picked up';
    button.classList.remove('ready-button');
    button.classList.add('received-button');

    // remove the old event listener
    button.onclick = null;

    // add the new event listener
    button.addEventListener('click', function(event) {
        sendOrderReceived(event.target.parentElement);
    });
}

function removeOrderElementFromReceived(orderElement) {
    orderElement.remove();
}