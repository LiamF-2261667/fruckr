/**
 * Function to handle login submission
 * @param event - submit event
 */
function submitSaveData(event) {
    event.preventDefault();

    // show the loading icon
    setVisibility(document.getElementById("saveLoading"), true);

    // Hide the (potential) error messages
    resetErrors();

    // Hide the success message
    setVisibility(document.getElementById("successBox"), false);

    // Get the input values
    let firstName = document.getElementById("firstName").value.trim();
    let lastName = document.getElementById("lastName").value.trim();

    let email = document.getElementById("email").value.trim();
    let phoneNumber = document.getElementById("phoneNumber").value.trim();

    let city = document.getElementById("city").value.trim();
    let street = document.getElementById("street").value.trim();
    let postalCode = document.getElementById("postalCode").value.trim();
    let houseNr = document.getElementById("houseNr").value.trim();
    let bus = document.getElementById("bus").value.trim();

    let password = document.getElementById('password').value.trim();

    // Make sure the inputs are valid before sending them to the server
    // let valid = checkInputs(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password); /* Not necessary */

    // Try to save the user data
    // if (valid)
        sendSaveDataToServer(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password);
    // else
    //  setVisibility(document.getElementById("saveLoading"), false);
}

/**
 * Function to create a save error
 * @param message - the error message
 */
function createSaveError(message) {
    handleSaveError({responseText: message}, null, null);
}

/**
 * Send a request to save the user data
 * @param firstName - the first name of the user
 * @param lastName - the last name of the user
 * @param email - the email address of the user
 * @param phoneNumber - the phone number of the user
 * @param city - the city of the user
 * @param street - the street of the user
 * @param postalCode - the postal code of the user
 * @param houseNr - the house number of the user
 * @param bus - the bus of the user
 * @param password - the password of the user
 */
function sendSaveDataToServer(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/profile/update", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulSave, handleSaveError);

    // Sending the request
    xmlHttp.send(JSON.stringify({
        firstName: firstName,
        lastName: lastName,
        email: email,
        phoneNumber: phoneNumber,
        city: city,
        street: street,
        postalCode: postalCode,
        houseNr: houseNr,
        bus: bus,
        password: password
    }));
}

/**
 * Function to handle a successful login
 */
function successfulSave(xhr) {
    // hide the loading icon
    setVisibility(document.getElementById("saveLoading"), false);

    // handle the response if the function is not a success
    if (xhr.responseText !== "success") {
        handleSaveError(xhr);
        setVisibility(document.getElementById("saveLoading"), false);
        return;
    }

    // Handle the response if it is a success
    setVisibility(document.getElementById("saveLoading"), false);
    setVisibility(document.getElementById("successBox"), true);
}

/**
 * Function to handle login errors
 * @param xhr - the XMLHttpRequest object
 * @param ajaxOptions - the options for the XMLHttpRequest
 * @param thrownError - the error thrown
 */
function handleSaveError(xhr, ajaxOptions = null, thrownError = null) {
    // Display the error messages
    document.getElementById("errorBox").innerText = xhr.responseText;
    setVisibility(document.getElementById("errorBox"), true);

    // Mark the wrong inputs
    highlightErrorInputIfPresent(xhr.responseText, "firstName", "firstname");
    highlightErrorInputIfPresent(xhr.responseText, "lastName", "lastname");
    highlightErrorInputIfPresent(xhr.responseText, "firstName", "name");
    highlightErrorInputIfPresent(xhr.responseText, "lastName", "name");

    highlightErrorInputIfPresent(xhr.responseText, "email");
    highlightErrorInputIfPresent(xhr.responseText, "phoneNumber", "phone number");

    highlightErrorInputIfPresent(xhr.responseText, "city");
    highlightErrorInputIfPresent(xhr.responseText, "street");
    highlightErrorInputIfPresent(xhr.responseText, "postalCode", "postal code");
    highlightErrorInputIfPresent(xhr.responseText, "houseNr", "house number");
    highlightErrorInputIfPresent(xhr.responseText, "bus");

    highlightErrorInputIfPresent(xhr.responseText, "password");

    setVisibility(document.getElementById("saveLoading"), false);
}

/**
 * Function to highlight the input if it is present in the error message
 * @param errorMsg - the error message
 * @param inputId - the id of the input
 * @param inputName - the name of the input
 */
function highlightErrorInputIfPresent(errorMsg, inputId, inputName = inputId) {
    if (errorMsg.toLowerCase().includes(inputName))
        document.getElementById(inputId).classList.add("is-invalid");
}

/**
 * Function to reset the error messages
 */
function resetErrors() {
    // Hide the error box
    setVisibility(document.getElementById("errorBox"), false);

    // Remove the error classes from the inputs
    document.getElementById("firstName").classList.remove("is-invalid");
    document.getElementById("lastName").classList.remove("is-invalid");

    document.getElementById("email").classList.remove("is-invalid");
    document.getElementById("phoneNumber").classList.remove("is-invalid");

    document.getElementById("city").classList.remove("is-invalid");
    document.getElementById("street").classList.remove("is-invalid");
    document.getElementById("postalCode").classList.remove("is-invalid");
    document.getElementById("houseNr").classList.remove("is-invalid");
    document.getElementById("bus").classList.remove("is-invalid");

    document.getElementById("password").classList.remove("is-invalid");
}

//Listen for the form submit
document.getElementById('saveForm').addEventListener('submit', submitSaveData, false);