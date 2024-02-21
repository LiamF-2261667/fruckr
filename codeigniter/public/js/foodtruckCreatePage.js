function create() {
    // Show the loading icon
    showCreateLoadingIcon();

    // Reset the error messages
    hideAllErrors();

    // Get the save data
    let createData = getSaveData();

    // Validate the save data
    let validationResult = validateSaveData(createData);
    if (!validationResult.isValid) {
        handleError(validationResult.errorMsg);
        hideCreateLoadingIcon();
        return;
    }

    // Send the save data to the server
    sendCreateData(createData);
}

document.getElementById("createButton").addEventListener("click", create);

/* LOADING ICON */
/**
 * Show the loading icon for creating
 */
function showCreateLoadingIcon() {
    let saveButton = document.getElementById("createButton");
    saveButton.innerHTML = '<img id="createLoadingIcon" src="../Gifs/loading.gif" alt="Loading Icon">';
}

/**
 * Hide the loading icon for creating
 */
function hideCreateLoadingIcon() {
    let saveButton = document.getElementById("createButton");
    saveButton.innerHTML = '<img src="../Icons/save.png" alt="Create Icon">Create';
}

/* SENDING & RECEIVING DATA WITH AJAX */
/**
 * Sends the creation data to the server
 * @param createData The create data
 */
function sendCreateData(createData) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/create/create", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulCreate, handleCreateError);

    // Sending the request
    xmlHttp.send(JSON.stringify({foodtruckDataModel: createData}));
}

/**
 * Function to handle a successful save response
 */
function successfulCreate(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleCreateError(jsonResponse.error);
    else {
        // Redirect to the foodtruck page
        window.location.href = "/foodtruck/" + jsonResponse.foodtruckId;
    }
}

/**
 * Function to handle a save error
 * @param xhr - The xhr object
 * @param ajaxOptions - The ajax options
 * @param thrownError - The thrown error
 */
function handleCreateError(xhr, ajaxOptions = null, thrownError = null) {
    if (xhr.responseText === undefined || xhr.responseText === null || xhr.responseText === "")
        handleError(xhr);
    else
        handleError(xhr.responseText);

    // Hide the loading icon
    hideCreateLoadingIcon();
}