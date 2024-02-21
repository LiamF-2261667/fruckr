// Create a backup of the save data when going into edit mode, so when you cancel the edit, the data is restored
let saveDataBackup = null;

function openEdit() {
    // Create a backup of the save data
    saveDataBackup = getSaveData();

    // Go into edit mode
    changeEditMode(true);
}

function saveEdit() {
    // Show the loading icon
    showSaveLoadingIcon();

    // Reset the error messages
    hideAllErrors();

    // Get the save data
    let saveData = getSaveData();

    // Validate the save data
    let validationResult = validateSaveData(saveData);
    if (!validationResult.isValid) {
        handleError(validationResult.errorMsg);
        hideSaveLoadingIcon();
        return;
    }

    // Send the save data to the server
    sendSaveData(saveData);
}

function cancelEdit() {
    // Restore the backup
    if (saveDataBackup !== null)
        loadData(saveDataBackup);

    // Reset the error messages
    hideAllErrors();

    // Go back to read only mode
    changeEditMode(false);
}

function changeEditMode(toEditOnly) {
    let editOnly = document.getElementsByClassName("editonly");
    for (let i = 0; i < editOnly.length; i++)
        editOnly[i].style.display = toEditOnly ? "block" : "none";

    let readOnly = document.getElementsByClassName("readonly");
    for (let i = 0; i < readOnly.length; i++)
        readOnly[i].style.display = toEditOnly ? "none" : "block";

    let editOnlyFlex = document.getElementsByClassName("editonly-flex");
    for (let i = 0; i < editOnlyFlex.length; i++)
        editOnlyFlex[i].style.display = toEditOnly ? "flex" : "none";

    let readOnlyFlex = document.getElementsByClassName("readonly-flex");
    for (let i = 0; i < readOnlyFlex.length; i++)
        readOnlyFlex[i].style.display = toEditOnly ? "none" : "flex";
}

if (document.getElementById("editButton") !== null) document.getElementById("editButton").addEventListener("click", openEdit);
if (document.getElementById("saveButton") !== null) document.getElementById("saveButton").addEventListener("click", saveEdit);
if (document.getElementById("cancelButton") !== null) document.getElementById("cancelButton").addEventListener("click", cancelEdit);

/* LOADING ICON */
/**
 * Show the loading icon for saving
 */
function showSaveLoadingIcon() {
    let saveButton = document.getElementById("saveButton");
    saveButton.innerHTML = '<img id="saveLoadingIcon" src="../Gifs/loading.gif" alt="Loading Icon">';
}

/**
 * Hide the loading icon for saving
 */
function hideSaveLoadingIcon() {
    let saveButton = document.getElementById("saveButton");
    saveButton.innerHTML = '<img src="../Icons/save.png" alt="Save Icon">Save';
}

/* SENDING & RECEIVING DATA WITH AJAX */
/**
 * Sends the save data to the server
 * @param saveData The save data
 */
function sendSaveData(saveData) {
    let requestUrl  = "/foodtruck/save";

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", requestUrl, true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, successfulSave, handleSaveError);

    // Sending the request
    xmlHttp.send(JSON.stringify({foodtruckDataModel: saveData}));
}

/**
 * Function to handle a successful save response
 */
function successfulSave(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleSaveError(jsonResponse.error);
    else
        location.reload();
}

/**
 * Function to handle save errors
 * @param xhr - the XMLHttpRequest object
 * @param ajaxOptions - the options for the XMLHttpRequest
 * @param thrownError - the error thrown
 */
function handleSaveError(xhr, ajaxOptions = null, thrownError = null) {
    if (xhr.responseText === undefined || xhr.responseText === null || xhr.responseText === "")
        handleError(xhr);
    else
        handleError(xhr.responseText);

    // Hide the loading icon
    hideSaveLoadingIcon();
}

/* DISPLAY ERROR */
/**
 * Displays an error message at the correct place on the page
 * @param errorMsg The error message
 */
function handleError(errorMsg) {
    if (errorMsg.toLowerCase().includes("information: ".toLowerCase()))
        displayError("informationErrorBox", errorMsg.replace("Information: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("tags: ".toLowerCase()))
        displayError("tagsErrorBox", errorMsg.replace("Tags: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("open on: ".toLowerCase()))
        displayError("openOnErrorBox", errorMsg.replace("Open on: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("future location: ".toLowerCase()))
        displayError("futureLocationsErrorBox", errorMsg.replace("Future locations: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("banners: ".toLowerCase()))
        displayError("bannerErrorBox", errorMsg.replace("Banners: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("profile image: ".toLowerCase()))
        displayError("profileImageErrorBox", errorMsg.replace("Profile image: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("name: ".toLowerCase()))
        displayError("titleErrorBox", errorMsg.replace("Name: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("description: ".toLowerCase()))
        displayError("descriptionErrorBox", errorMsg.replace("Description: ", ""), 200);

    else if (errorMsg.toLowerCase().includes("extra: ".toLowerCase()))
        displayError("extraErrorBox", errorMsg.replace("Extra: ", ""), 200);

    else
        displayError("generalErrorBox", errorMsg, 200);
}

/* VALIDATE SAVE DATA */
/**
 * Validates the save data
 * @param saveData The save data
 * @returns {{isValid: boolean, errorMsg: *}} True if the save data is valid, false otherwise
 */
function validateSaveData(saveData) {
    if (saveData.name === null || saveData.name === undefined || saveData.name.trim() === "")
        return {isValid: false, errorMsg: "Name: Name is required."};

    let profileImageValidationResult = validateProfileImage(saveData.profileImageBase64);
    if (!profileImageValidationResult.isValid)
        return {isValid: false, errorMsg: "Profile image: " + profileImageValidationResult.errorMsg};

    let bannersValidationResult = validateBanners(saveData.banners);
    if (!bannersValidationResult.isValid)
        return {isValid: false, errorMsg: "Banners: " + bannersValidationResult.errorMsg};

    if (saveData.description === null || saveData.description === undefined || saveData.description.trim() === "")
        return {isValid: false, errorMsg: "Description: Description is required."};

    let informationValidationResult = validateInformation(saveData.information);
    if (!informationValidationResult.isValid)
        return {isValid: false, errorMsg: "Information: " + informationValidationResult.errorMsg};

    let openOnValidationResult = validateOpenOn(saveData.openOn);
    if (!openOnValidationResult.isValid)
        return {isValid: false, errorMsg: "Open on: " + openOnValidationResult.errorMsg};

    let futureLocationsValidationResult = validateFutureLocations(saveData.futureLocations);
    if (!futureLocationsValidationResult.isValid)
        return {isValid: false, errorMsg: "Future locations: " + futureLocationsValidationResult.errorMsg};

    let tagsValidationResult = validateTags(saveData.tags);
    if (!tagsValidationResult.isValid)
        return {isValid: false, errorMsg: "Tags: " + tagsValidationResult.errorMsg};

    return {isValid: true};
}

/**
 * Validates the information
 * @param information The information
 * @returns {{isValid: boolean, errorMsg: string}|{isValid: boolean}} True if the information is valid, false otherwise containing an error message
 */
function validateInformation(information) {
    let city = information.city;
    let street = information.street;
    let postalCode = information.postalCode;
    let houseNr = information.houseNr;
    let bus = information.bus;
    let phoneNumber = information.phoneNumber;
    let email = information.email;

    if (city === null || city === undefined || city === "")
        return {isValid: false, errorMsg: "City is required."};

    if (street === null || street === undefined || street === "")
        return {isValid: false, errorMsg: "Street is required."};

    if (postalCode === null || postalCode === undefined || postalCode === "")
        return {isValid: false, errorMsg: "Postal code is required."};

    if (houseNr === null || houseNr === undefined || houseNr === "")
        return {isValid: false, errorMsg: "House number is required."};

    if (phoneNumber === null || phoneNumber === undefined || phoneNumber === "")
        return {isValid: false, errorMsg: "Phone number is required."};

    if (email === null || email === undefined || email === "")
        return {isValid: false, errorMsg: "Email is required."};

    // Phone number has to be 9 to 16 symbols long
    if (phoneNumber.length < 9 || phoneNumber.length > 16)
        return {isValid: false, errorMsg: "Phone number has to be at least 9 to 16 long."};

    // Streets can only contain letters and dashes
    let streetRegex = /^[a-zA-Z\s\-]+$/;
    if (!streetRegex.test(street))
        return {isValid: false, errorMsg: "Street can only contain letters and dashes."};

    // Cities as well
    let cityRegex = /^[a-zA-Z\s\-]+$/;
    if (!cityRegex.test(city))
        return {isValid: false, errorMsg: "City can only contain letters and dashes."};

    return {isValid: true};
}

/**
 * Validates the tags
 * @param tags The tags
 * @returns {{isValid: boolean, errorMsg: string}} True if the tags are valid, false otherwise containing an error message
 */
function validateTags(tags) {
    for (let i = 0; i < tags.length; i++) {
        if (tags[i] === null || tags[i] === undefined || tags[i].trim() === "")
            return {isValid: false, errorMsg: "Each tag must have a value."};
    }

    return {isValid: true};
}

/**
 * Validates the open on models
 * @param openOn The open on models
 * @returns {{isValid: boolean, errorMsg: string}|{isValid: boolean}} True if the open on models are valid, false otherwise containing an error message
 */
function validateOpenOn(openOn) {
    for (let i = 0; i < openOn.length; i++) {
        let day = openOn[i].day;
        let from = openOn[i].from;
        let to = openOn[i].to;

        if (day === null || day === undefined || day === "")
            return {isValid: false, errorMsg: "Each day must have a value."};

        if (from === null || from === undefined || from === "")
            return {isValid: false, errorMsg: "Each from must have a value."};

        if (to === null || to === undefined || to === "")
            return {isValid: false, errorMsg: "Each to must have a value."};

        // from has to be before to
        let fromDate = new Date();
        fromDate.setHours(from.split(':')[0], from.split(':')[1]);

        let toDate = new Date();
        toDate.setHours(to.split(':')[0], to.split(':')[1]);

        if (fromDate > toDate)
            return {isValid: false, errorMsg: "The from time has to be before the to time."};

        // The hour span may not be contained in already existing open on rows
        for (let j = i + 1; j < openOn.length; j++) {
            let otherOpenOn = openOn[j];

            // The day has to be the same
            if (day !== otherOpenOn.day)
                continue;

            // The hour span may not be contained in already existing open on rows
            let otherOpenOnDateFrom = new Date();
            otherOpenOnDateFrom.setHours(otherOpenOn.from.split(':')[0], otherOpenOn.from.split(':')[1]);

            let otherOpenOnDateTo = new Date();
            otherOpenOnDateTo.setHours(otherOpenOn.to.split(':')[0], otherOpenOn.to.split(':')[1]);

            if (from >= otherOpenOnDateFrom && to <= otherOpenOnDateTo)
                return {isValid: false, errorMsg: "The hour span may not be contained in already existing open on rows."};
        }
    }

    // There has to be at least one open on row
    if (openOn.length === 0)
        return {isValid: false, errorMsg: "You should be open at least one day."};

    return {isValid: true};
}

/**
 * Validates the future locations
 * @param futureLocations The future locations
 * @returns {{isValid: boolean, errorMsg: string}|{isValid: boolean}} True if the future locations are valid, false otherwise containing an error message
 */
function validateFutureLocations(futureLocations) {
    for (let i = 0; i < futureLocations.length; i++) {
        let date = futureLocations[i].date;
        let street = futureLocations[i].street;
        let houseNr = futureLocations[i].houseNr;
        let bus = futureLocations[i].bus;
        let postalCode = futureLocations[i].postalCode;
        let city = futureLocations[i].city;

        if (date === null || date === undefined || date.trim() === "")
            return {isValid: false, errorMsg: "Each date must have a value."};

        if (street === null || street === undefined || street.trim() === "")
            return {isValid: false, errorMsg: "Each street must have a value."};

        if (houseNr === null || houseNr === undefined || houseNr.trim() === "")
            return {isValid: false, errorMsg: "Each house number must have a value."};

        if (postalCode === null || postalCode === undefined || postalCode.trim() === "")
            return {isValid: false, errorMsg: "Each postal code must have a value."};

        if (city === null || city === undefined || city.trim() === "")
            return {isValid: false, errorMsg: "Each city must have a value."};

        // Streets can only contain letters and dashes
        let streetRegex = /^[a-zA-Z\s\-]+$/;
        if (!streetRegex.test(street))
            return {isValid: false, errorMsg: "Street can only contain letters and dashes."};

        // Cities as well
        let cityRegex = /^[a-zA-Z\s\-]+$/;
        if (!cityRegex.test(city))
            return {isValid: false, errorMsg: "City can only contain letters and dashes."};

        // The date has to be in the future
        let dateParts = date.split('-');
        let dateObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        let today = new Date();
        today.setHours(0, 0, 0, 0);

        if (dateObj < today)
            return {isValid: false, errorMsg: "The date has to be in the future."};
    }

    return {isValid: true};
}

/**
 * Validates the profile image
 * @param banners The banners
 * @returns {{isValid: boolean, errorMsg: string}|{isValid: boolean}} True if the profile image is valid, false otherwise containing an error message
 */
function validateBanners(banners) {
    if (banners.length === 0)
        return {isValid: false, errorMsg: "At least one banner is required."};

    for (let i = 0; i < banners.length; i++) {
        let banner = banners[i];

        if (banner.base64 === null || banner.base64 === undefined || banner.base64.trim() === "")
            return {isValid: false, errorMsg: "Each banner must have a value."};

        if (banner.type === null || banner.type === undefined || banner.type.trim() === "")
            return {isValid: false, errorMsg: "Each banner must have a type, try removing and adding them again."};
    }

    return {isValid: true};
}

/**
 * Validates the profile image
 * @param profileImageBase64 The profile image
 * @returns {{isValid: boolean, errorMsg: string}|{isValid: boolean}} True if the profile image is valid, false otherwise containing an error message
 */
function validateProfileImage(profileImageBase64) {
    if (profileImageBase64 === null || profileImageBase64 === undefined || profileImageBase64.trim() === "" || profileImageBase64.includes("/Images/missingImage"))
        return {isValid: false, errorMsg: "Profile image is required."};

    return {isValid: true};
}

/* GET SAVE DATA */
/**
 * Gets the save data
 * @returns
 */
function getSaveData() {
    return {
        information: getInformation(),
        tags: getTags(),
        extra: document.getElementById('extraInfoEdit').value,
        description: document.getElementById('descriptionEdit').value,
        openOn: getOpenOn(),
        futureLocations: getFutureLocations(),
        banners: getBanners(),
        profileImageBase64: srcToBase64(document.getElementsByClassName('profile-image')[0].src),
        name: document.getElementById('foodtruckName').value
    };
}

/**
 * Gets the information
 * @returns {{city, street, postalCode, houseNr, bus, phoneNumber, email}} The information
 */
function getInformation() {
    let informationContainer = document.getElementsByClassName('information')[0];
    let inputs = informationContainer.getElementsByTagName('input');

    return {
        city: inputs[0].value,
        street: inputs[1].value,
        postalCode: inputs[2].value,
        houseNr: inputs[3].value,
        bus: inputs[4].value,

        phoneNumber: inputs[5].value,

        email: inputs[6].value,
    }
}

/**
 * Sets the information
 * @param information The information
 */
function setInformation(information) {
    let informationContainer = document.getElementsByClassName('information')[0];
    let inputs = informationContainer.getElementsByTagName('input');

    inputs[0].value = information.city;
    inputs[1].value = information.street;
    inputs[2].value = information.postalCode;
    inputs[3].value = information.houseNr;
    inputs[4].value = information.bus;

    inputs[5].value = information.phoneNumber;

    inputs[6].value = information.email;
}

/**
 * Gets the tags
 * @returns {Array} The tags
 */
function getTags() {
    let tagsContainer = document.getElementsByClassName('tags')[0];
    let tagElements = tagsContainer.getElementsByTagName('p');

    let tags = [];
    for (let i = 0; i < tagElements.length; i++)
        tags.push(tagElements[i].innerHTML);

    return tags;
}

/**
 * Sets the tags
 * @param tags The tags
 */
function setTags(tags) {
    // Remove all tags
    let rows = getTagRows();
    for (let i = 0; i < rows.length; i++)
        rows[i].remove();

    // Add the tags
    for (let i = 0; i < tags.length; i++) {
        addTagRow.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value = tags[i];
        addTagRowToTable();
    }
}

/**
 * Gets the open on
 * @returns {array} The open on
 */
function getOpenOn() {
    return getOpenRowModels();
}

/**
 * Sets the open on
 * @param openOn The open on
 */
function setOpenOn(openOn) {
    // Remove all open rows
    let rows = getOpenRows();
    for (let i = 0; i < rows.length; i++)
        rows[i].remove();

    // Add the open rows
    for (let i = 0; i < openOn.length; i++) {
        addOpenTableRow.getElementsByTagName('td')[0].getElementsByTagName('select')[0].value = dayOfWeekToInt(openOn[i].day);
        addOpenTableRow.getElementsByTagName('td')[1].getElementsByTagName('input')[0].value = openOn[i].from;
        addOpenTableRow.getElementsByTagName('td')[2].getElementsByTagName('input')[0].value = openOn[i].to;

        addOpenRow();
    }
}

/**
 * Gets the future locations
 * @returns {array} The future locations
 */
function getFutureLocations() {
    let rawFutureLocations = getFutureLocationRowModels();
    let futureLocations = [];

    for (let i = 0; i < rawFutureLocations.length; i++) {
        let rawFutureLocation = rawFutureLocations[i];
        let address = addressStrToModel(rawFutureLocation.location);

        futureLocations.push({
            date: rawFutureLocation.date,
            street: address.street,
            houseNr: address.houseNr,
            bus: address.bus,
            postalCode: address.postalCode,
            city: address.city
        });
    }

    return futureLocations;
}

/**
 * Sets the future locations
 * @param futureLocations The future locations
 */
function setFutureLocations(futureLocations) {
    // Remove all future location rows
    let rows = getFutureLocationRows();
    for (let i = 0; i < rows.length; i++)
        rows[i].remove();

    // Add the future location rows
    for (let i = 0; i < futureLocations.length; i++) {
        let futureLocation = futureLocations[i];

        addFutureLocationRow.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value = futureLocation.date;
        let locationInfo = addFutureLocationRow.getElementsByTagName('td')[1].getElementsByTagName('input');
        locationInfo[0].value = futureLocation.city;
        locationInfo[1].value = futureLocation.street;
        locationInfo[2].value = futureLocation.postalCode;
        locationInfo[3].value = futureLocation.houseNr;
        locationInfo[4].value = futureLocation.bus;

        addFutureLocationRowToTable();
    }
}

/**
 * Gets the banners
 * @returns {array} The banners
 */
function getBanners() {
    let rawBanners = banners;
    let bannerModels = [];

    for (let i = 0; i < rawBanners.length; i++) {
        let rawBanner = rawBanners[i];

        let img = rawBanner.getElementsByTagName('img');
        let vid = rawBanner.getElementsByTagName('video');

        // get the type and src of the banner based on if the banner contains an img or video tag
        let type = null;
        let src = null;

        if (img.length > 1) { /* There is always a minus img */
            type = 'img';
            src = img[0].src;
        }

        else if (vid.length > 0) {
            type = 'vid';
            src = vid[0].getElementsByTagName('source')[0].src;
        }

        // If neither, continue to next banner
        else
            continue;

        bannerModels.push({
            base64: srcToBase64(src),
            type: type,
            order: rawBanner.getElementsByTagName('input')[0].value
        });
    }

    return bannerModels;
}

/**
 * Sets the banners
 * @param bannerModels The banners

 */
function setBanners(bannerModels) {
    // Remove all banners
    let banners = bannerImages.getElementsByClassName('bannerImage');
    while (banners.length > 0){
        banners[0].nextElementSibling.remove();
        banners[0].remove();
    }

    // Add the banners
    for (let i = 0; i < bannerModels.length; i++) {
        let bannerModel = bannerModels[i];

        document.getElementById('addBannerImageOrder').value = bannerModel.order;

        let type = (getElementTagFromFileType(bannerModel.type) === 'img') ? "jpg" : "mp4";
        addBannerFile = {
            base64: base64ToSrc(bannerModel.base64, type),
            type: type
        };

        addBannerOnClick();
    }
}

/**
 * Get a model containing the address information based on the required elements of an address
 * @param addressStr The address string
 * @returns {{street, houseNr, bus, postalCode, city}} The address model
 */
function addressStrToModel(addressStr) {
    let addressSmall = addressStr.split(',')[0].split(' '); /* Street, houseNr, bus */
    let addressLarge = addressStr.split(',')[1].split(' '); /* Postal code, city */

    let street = addressSmall[0];
    let houseNr = addressSmall[1];
    let bus = null;
    if (addressSmall.length > 2)
        bus = addressSmall[2];

    let postalCode = addressLarge[1];
    let city = addressLarge[2];

    return {
        street: street,
        houseNr: houseNr,
        bus: bus,
        postalCode: postalCode,
        city: city
    }
}

/**
 * Convert a src containing a base64 string to a base64 string
 * @param src The src
 * @returns {string} The base64 string
 */
function srcToBase64(src) {
    if (src.includes("data:image"))
        return src.replace(/^data:image\/(png|jpg|jpeg|webp);base64,/, "");
    else
        return src.replace(/^data:video\/(mp4|ogg);base64,/, "");
}

/**
 * Convert a base64 string to a src string
 * @param base64 The base64 string
 * @param type The type of the file
 * @returns {string} The src string
 */
function base64ToSrc(base64, type) {
    if (getElementTagFromFileType(type) === 'img')
        return "data:image/jpg;base64," + base64;
    else
        return "data:video/mp4;base64," + base64;
}

/* LOAD DATA */
/**
 * Loads the data
 */
function loadData(foodtruckData) {
    document.getElementById('extraInfoEdit').value = foodtruckData.extra;
    document.getElementById('descriptionEdit').value = foodtruckData.description;
    document.getElementsByClassName('profile-image')[0].src = base64ToSrc(foodtruckData.profileImageBase64, "png");
    document.getElementById('foodtruckName').value = foodtruckData.name;

    setInformation(foodtruckData.information);
    setTags(foodtruckData.tags);
    setOpenOn(foodtruckData.openOn);
    setFutureLocations(foodtruckData.futureLocations);
    setBanners(foodtruckData.banners);
}