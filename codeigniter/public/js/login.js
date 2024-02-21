/**
 * Function to handle login submission
 * @param event - submit event
 */
function submitLogin(event) {
    event.preventDefault();

    // show the loading icon
    setVisibility(document.getElementById("loginLoading"), true);

    // Hide the (potential) error messages
    resetErrors();

    // Get the input values
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;

    // Make sure the inputs are valid before sending them to the server
    // checkLoginInputs(email, password); (Not needed, is done by html5 validation)

    // Try to log in the user
    sendLoginToServer(email, password);
}

/**
 * Send login request to server, returns whether the login was successful
 * @param email - the email address of the user
 * @param password - the password of the user
 */
function sendLoginToServer(email, password) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/login/login", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulLogin, handleLoginError);

    // Sending the request
    xmlHttp.send(JSON.stringify({email: email, password: password}));
}

/**
 * Function to handle a successful login
 */
function successfulLogin(xhr) {
    // hide the loading icon
    setVisibility(document.getElementById("loginLoading"), false);

    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleLoginError(jsonResponse.error);
    else
        window.location.href = jsonResponse.redirect;

    setVisibility(document.getElementById("saveLoading"), false);
}

/**
 * Function to handle login errors
 * @param xhr - the XMLHttpRequest object
 * @param ajaxOptions - the options for the XMLHttpRequest
 * @param thrownError - the error thrown
 */
function handleLoginError(xhr, ajaxOptions = null, thrownError = null) {
    // Display the error messages
    document.getElementById("errorBox").innerText = xhr;
    setVisibility(document.getElementById("errorBox"), true);

    // Mark the wrong inputs
    highlightErrorInputIfPresent(xhr.responseText, "email");
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
    if (errorMsg.toLocaleLowerCase().includes(inputName))
        document.getElementById(inputId).classList.add("is-invalid");
}

/**
 * Function to reset the error messages
 */
function resetErrors() {
    // Hide the error box
    setVisibility(document.getElementById("errorBox"), false);

    // Remove the error classes from the inputs
    document.getElementById("email").classList.remove("is-invalid");
    document.getElementById("password").classList.remove("is-invalid");
}

//Listen for the form submit
document.getElementById('loginForm').addEventListener('submit', submitLogin, false);