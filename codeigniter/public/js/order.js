// On submit, send order to server, via ajax => seamless error handling

document.getElementById('orderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendOrder();
});

// Send order to server

function sendOrder() {
    // Hide errors & reloading
    hideError('orderErrorBox');
    showLoadingIcon();

    // Get the order
    const order = {
        city: document.getElementById('city').value,
        postal: document.getElementById('postalCode').value,
        street: document.getElementById('street').value,
        houseNr: document.getElementById('houseNr').value,
        bus: document.getElementById('bus').value,
        cardNumber: document.getElementById('cardNumber').value,
        cardHolder: document.getElementById('cardHolder').value,
        expirationDate: getExpirationDateStringFromSelections()
    };

    // validate the order
    // if (!validateOrder(order)) return; /* Done by html5 validation */

    // Send the request
    sendOrderRequest(order);
}

function showLoadingIcon() {
    document.getElementById('orderLoading').style.display = 'block';
}

function hideLoadingIcon() {
    document.getElementById('orderLoading').style.display = 'none';
}

function getExpirationDateStringFromSelections() {
    const month = document.getElementById('expireMM').value;
    const year = document.getElementById('expireYY').value;
    return `${month}/${year}`;
}

function handleOrderError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('orderErrorBox', errorMsg);

    hideLoadingIcon();
}

function sendOrderRequest(order) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/order/post", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfulOrder, handleOrderError);

    // Sending the request
    xmlHttp.send(JSON.stringify(order));
}

function onSuccessfulOrder(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleOrderError(jsonResponse.error);
    else {
        // Redirect to the order confirmation page
        window.location.href = '/order/confirmation';
    }
}