/**
 * Function to handle login submission
 * @param event - submit event
 */
function submitSignupData(event) {
    event.preventDefault();

    // show the loading icon
    setVisibility(document.getElementById("signupLoading"), true);

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
    let confirmPassword = document.getElementById('confirmPassword').value.trim();

    let isOwner = document.getElementById('foodtruckOwner').checked;

    // Make sure the inputs are valid before sending them to the server
    let valid = checkInputs(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password, confirmPassword);

    // Try to signup the user data
    if (valid)
        sendSignupDataToServer(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password, isOwner);
    else
        setVisibility(document.getElementById("signupLoading"), false);
}

/**
 * Check if inputs are correct before sending them to the server
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
 * @param confirmPassword - the confirmation password of the user
 * @returns {boolean} - whether the inputs are valid
 */
function checkInputs(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password, confirmPassword) {
    if (confirmPassword !== password) {
        createSignupError("Confirmation password does not match password");
        return false;
    }

    return true;
}

/**
 * Function to create a signup error
 * @param message - the error message
 */
function createSignupError(message) {
    handleSignupError({responseText: message}, null, null);
}

/**
 * Send a request to signup the user data
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
function sendSignupDataToServer(firstName, lastName, email, phoneNumber, city, street, postalCode, houseNr, bus, password, isOwner) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/signup/register", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulSignup, handleSignupError);

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
        password: password,
        isOwner: isOwner
    }));
}

/**
 * Function to handle a successful login
 */
function successfulSignup(xhr) {
    // hide the loading icon
    setVisibility(document.getElementById("signupLoading"), false);

    // handle the response if the function is not a success
    if (xhr.responseText !== "success") {
        handleSignupError(xhr);
        setVisibility(document.getElementById("signupLoading"), false);
        return;
    }

    // Handle the response if it is a success
    setVisibility(document.getElementById("signupLoading"), false);
    setVisibility(document.getElementById("successBox"), true);

    // If the user isn't a foodtruck owner, redirect to the previous page
    if (!document.getElementById('foodtruckOwner').checked)
        window.location.href = document.getElementById('prevPage').innerText;

    // Else redirect to the foodtruck create page
    else
        window.location.href = "/foodtruck/create";
}

/**
 * Function to handle login errors
 * @param xhr - the XMLHttpRequest object
 * @param ajaxOptions - the options for the XMLHttpRequest
 * @param thrownError - the error thrown
 */
function handleSignupError(xhr, ajaxOptions = null, thrownError = null) {
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
    highlightErrorInputIfPresent(xhr.responseText, "confirmPassword", "confirmation password");

    setVisibility(document.getElementById("signupLoading"), false);
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
    document.getElementById("confirmPassword").classList.remove("is-invalid");
}

/**
 * Swap the color pallet based on the user type
 */
function swapColorPallete() {
    // Remove the old color pallet
    document.getElementById('colors').remove();

    /* src: https://stackoverflow.com/questions/11833759/add-stylesheet-to-head-using-javascript-in-body */
    let link = document.createElement("link");

    link.type = "text/css";
    link.rel = "stylesheet";

    // Getting the correct color pallet
    if (document.getElementById('foodtruckOwner').checked)
        link.href = "css/foodtruckWorkerTheme.css";
    else
        link.href = "css/consumerTheme.css";

    link.id = "colors";

    // Adding the new color pallet
    document.head.appendChild(link);
}

//Listen for the form submit
document.getElementById('signupForm').addEventListener('submit', submitSignupData, false);
document.getElementById('foodtruckOwner').addEventListener('change', swapColorPallete, false);
document.getElementById('customer').addEventListener('change', swapColorPallete, false);