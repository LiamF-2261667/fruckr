/* FOODTRUCK BANNERS */
let bannerImages = document.getElementsByClassName('bannerImages')[0];
let banners = bannerImages.getElementsByClassName('bannerImage');
let addBannerFile = null;

/**
 * Calculates the total size of the banners
 * @returns {number} - the total size of the banners
 */
function calculateTotalBannerSize() {
    let totalBannerSize = 0;

    for (let i = 0; i < banners.length; i++) {
        let banner = banners[i];
        totalBannerSize += banner.getElementsByTagName('img')[0].src.length * (3/4);
    }

    return totalBannerSize;
}

/**
 * Removes banners live from the editing view if the user clicks on the remove button
 */
function removeBannerLiveEditing() {
    for (let i = 0; i < banners.length; i++) {
        let banner = banners[i];

        banner.getElementsByTagName('button')[0].addEventListener('click', (event) => {
            banner.nextElementSibling.remove(); /* Remove the hr element */
            banner.remove(); /* Remove the banner element */
        });
    }
}

/**
 * Changes the order of the banners live from the editing view if the user edits the order input field
 */
function changeOrderLiveEditing() {
    for (let i = 0; i < banners.length; i++) {
        let banner = banners[i];

        let orderInput = banner.getElementsByTagName('input')[0];
        orderInput.addEventListener('change', (event) => {
            let newOrder = orderInput.value;
            updateBannerPosition(newOrder, banner);
        });
    }
}

/**
 * Swaps the position of two banners if necessary
 * @param newOrder - the new order of the banner
 * @param banner - the banner to move
 */
function updateBannerPosition(newOrder, banner) {
    let direction = getBannerMoveDirection(newOrder, banner);

    if (direction === 0)
        return;

    /*
    for banner.nextElementSibling.nextElementSibling 'nextElementSibling' has to be called twice for the <hr> in between the banners
    the same goes for the previousElementSibling
     */
    if (direction === 1)
        swapNodes(banner, banner.nextElementSibling.nextElementSibling);
    else if (direction === -1)
        swapNodes(banner, banner.previousElementSibling.previousElementSibling);

    updateBannerPosition(newOrder, banner);
}

/**
 * Gets the direction in which the banner should move.
 * @param newOrder - the new order of the banner
 * @param banner - the banner to move
 * @returns -1 for up, 0 for no change, 1 for down
 */
function getBannerMoveDirection(newOrder, banner) {
    if (banner.nextElementSibling !== null &&
        banner.nextElementSibling.nextElementSibling !== null &&
        banner.nextElementSibling.nextElementSibling.getElementsByTagName('input')[0].value < newOrder)
        return 1;

    if (banner.previousElementSibling !== null &&
        banner.previousElementSibling.previousElementSibling !== null &&
        banner.previousElementSibling.previousElementSibling.getElementsByTagName('input')[0].value > newOrder)
        return -1;

    return 0;
}

/**
 * Adds a new banner to the editing view if the user clicks on the add button
 */
function addBannerLiveEditing() {
    let addBannerButton = document.getElementById('addBannerButton');

    addBannerButton.addEventListener('click', addBannerOnClick);

    readFileOnChange(document.getElementById('addBannerImage'), (result) => {
        addBannerFile = result;
        document.getElementById('addBannerButton').disabled = false;
    });
}

function addBannerOnClick() {
    // Hide the error box
    hideError("bannerErrorBox");

    // Check if the banner to be added is valid
    let validationResult = validateBannerAdding();
    if (!validationResult.isValid) {
        displayError("bannerErrorBox", validationResult.message, 100);
        return;
    }

    // Get the new banner information
    let newOrder = getAddBannerOrder();
    let newBanner = createNewBanner(addBannerFile, newOrder);

    // Add the new banner
    bannerImages.appendChild(newBanner);

    // Add a new <hr> element (for mobile)
    bannerImages.appendChild(createNewHr());

    // Update the banner position
    updateBannerPosition(newOrder, newBanner);

    // Add the listeners to the new banner
    removeBannerLiveEditing();
    changeOrderLiveEditing();

    // Remove the current add image
    document.getElementById('addBannerImage').value = null;
    document.getElementById('addBannerButton').disabled = true;
}

/**
 * Validates the banner to be added
 * @returns {{isValid: boolean}|{isValid: boolean, message: string}} - the validation result
 */
function validateBannerAdding() {
    // Check if the file is valid
    if (addBannerFile === null || addBannerFile === undefined)
        return {
            isValid: false,
            message: "Please select a file."
        };

    // Check if the file type is valid
    let allowedFileExtensions = ['jpg', 'jpeg', 'png', 'mp4', 'webp'];
    if (!allowedFileExtensions.includes(addBannerFile.type.toLowerCase()))
        return {
            isValid: false,
            message: "You can only upload .jpg, .jpeg, .png, webp and .mp4 files."
        };

    // Check if the file size is valid
    else if (addBannerFile.size > 10000000)
        return {
            isValid: false,
            message: "The file size may not exceed 10MB."
        };

    // Check if adding the banner wouldn't exceed the max total banner size
    else if (calculateTotalBannerSize() + addBannerFile.size > 10000000)
        return {
            isValid: false,
            message: "The total banner size may not exceed 10MB."
        }

    // Check if the order is valid
    let newOrder = getAddBannerOrder();
    if (newOrder < 0)
        return {
            isValid: false,
            message: "Please select a valid order."
        };

    return {
        isValid: true
    };
}

/**
 * Gets the order of the new banner
 * @returns {number} - the order of the new banner
 */
function getAddBannerOrder() {
    let newOrder = document.getElementById('addBannerImageOrder').value;
    if (newOrder === '' || newOrder === null || newOrder === undefined)
        newOrder = banners.length;

    return newOrder;
}

/**
 * Creates a new <hr> element for the banner
 * @returns {HTMLHRElement} - the new <hr> element
 */
function createNewHr() {
    let newHr = document.createElement('hr');
    newHr.classList.add('mobile');

    return newHr;
}

/**
 * Creates a new banner element
 * @param file - the file of the banner
 * @param order - the order of the banner
 * @returns {HTMLDivElement} - the new banner element
 */
function createNewBanner(file, order) {
    let newBanner = document.createElement('div');
    newBanner.classList.add('bannerImage');
    newBanner.classList.add('row');

    newBanner.appendChild(createNewBannerFile(file.base64, getElementTagFromFileType(file.type)));
    newBanner.appendChild(createNewBannerOrderLabel());
    newBanner.appendChild(createNewBannerOrder(order));
    newBanner.appendChild(createNewBannerRemoveButton());

    return newBanner;
}

/**
 * Creates a new banner remove button element
 * @returns {HTMLButtonElement} - the new banner remove button element
 */
function createNewBannerRemoveButton() {
    let newBannerRemoveButton = document.createElement('button');
    newBannerRemoveButton.type = 'button';
    newBannerRemoveButton.classList.add('logo-button');
    newBannerRemoveButton.classList.add('offset-sm-1');
    newBannerRemoveButton.id = "removeBannerButton";

    newBannerRemoveButton.innerHTML = '<img src="../Icons/minus.png" alt="remove icon">';

    return newBannerRemoveButton;
}

/**
 * Creates a new banner order element
 * @param order - the order of the banner
 * @returns {HTMLInputElement} - the new banner order element
 */
function createNewBannerOrder(order) {
    let newBannerOrder = document.createElement('input');
    newBannerOrder.type = 'number';
    newBannerOrder.value = order;
    newBannerOrder.min = "0";
    newBannerOrder.id = "bannerImageOrder";
    newBannerOrder.classList.add('col-sm-1');

    return newBannerOrder;
}

/**
 * Creates a new banner image element
 * @returns {HTMLLabelElement} - the new banner image element
 */
function createNewBannerOrderLabel() {
    let newBannerOrderLabel = document.createElement('label');
    newBannerOrderLabel.innerText = 'Order';
    newBannerOrderLabel.classList.add('col-sm-2');

    return newBannerOrderLabel;
}

/**
 * Creates a new banner file element
 * @param data - the data of the banner
 * @param elementType - the type of the banner (img or video)
 * @returns {*}
 */
function createNewBannerFile(data, elementType) {
    let newBannerFile = document.createElement(elementType);

    if (elementType === 'img')
        newBannerFile.src = data;
    else {
        let source = document.createElement('source');
        source.src = data;
        source.type = 'video/' + addBannerFile.type;
        newBannerFile.appendChild(source)
    }

    newBannerFile.alt = 'New Banner';
    newBannerFile.classList.add('col-sm-4');
    newBannerFile.classList.add('offset-sm-2');
    newBannerFile.muted = "muted";
    newBannerFile.autoplay = "autoplay";
    newBannerFile.loop = "loop";

    return newBannerFile;
}

removeBannerLiveEditing();
changeOrderLiveEditing();
addBannerLiveEditing();

/* PROFILE IMAGE */
readFileOnChange(document.getElementById('profileImage'), (result) => {
    if (result.type.toLowerCase() !== 'jpg' && result.type.toLowerCase() !== 'jpeg' && result.type.toLowerCase() !== 'png' && result.type.toLowerCase() !== 'webp')
        displayError("profileImageErrorBox", "You can only upload .jpg, .jpeg, webp and .png files.", 200);

    else if (result.size > 3000000)
        displayError("profileImageErrorBox", "The file size may not exceed 3MB.", 200);

    else {
        document.getElementsByClassName('profile-image')[0].src = result.base64;
        hideError("profileImageErrorBox");
    }
});

/* OPEN ON */
let openOnTable = document.getElementsByClassName('open-times')[0].getElementsByClassName('editonly')[0];
let addOpenTableRow = openOnTable.getElementsByTagName('tr')[openOnTable.getElementsByTagName('tr').length - 1];

/**
 * Gets the open on rows from the table
 * @returns {*} - the open on rows from the table
 */
function getOpenRows() {
    let openOnRows = [];
    let trs = openOnTable.getElementsByTagName('tr');
    for (let i = 1; i < trs.length - 1; i++) /* Skip the first and last row (the header and the add row) */
        openOnRows.push(trs[i]);

    return openOnRows;
}

/**
 * Gets the open on row models from the table
 * @returns {*[]} - the open on row models from the table
 */
function getOpenRowModels() {
    let openRowModels = [];
    let openRows = getOpenRows();

    for (let i = 0; i < openRows.length; i++) {
        let openRow = openRows[i];
        openRowModels.push(getOpenRowModel(openRow));
    }

    return openRowModels;
}

/**
 * Gets the open row model of a specified row
 * @returns {{from, to, day}}
 */
function getOpenRowModel(row) {
    return {
        day: row.getElementsByTagName('p')[0].innerHTML,
        from: row.getElementsByTagName('p')[1].innerHTML,
        to: row.getElementsByTagName('p')[2].innerHTML
    }
}

/**
 * Sorts the open on rows by day
 */
function addOpenRowSorted(openRow) {
    // Add the new open on row
    let tbody = openOnTable.children[0];
    let openRows = getOpenRows();

    // If there are no future location rows yet, just add the new one before the add row
    if (openRows.length === 0) {
        tbody.insertBefore(openRow, addOpenTableRow);
        return;
    }

    tbody.insertBefore(openRow, getOpenRows()[0]);

    // Sort the new openRow on the already sorted rows
    openRows = getOpenRows();
    let isSorted = false;
    while (!isSorted) {
        if (openRow.nextElementSibling === addOpenTableRow) {
            isSorted = true;
            continue;
        }

        let compRes = compareDayTimes(getOpenRowModel(openRow), getOpenRowModel(openRow.nextElementSibling));

        // Swap the new row with its sibling if it's later
        if (compRes > 0)
            swapNodes(openRow, openRow.nextElementSibling);
        else
            isSorted = true;
    }
}

/**
 * Removes an open on row if the user clicks on the remove button
 */
function removeOpenRowOnClick() {
    let openRows = getOpenRows();

    for (let i = 0; i < openRows.length; i++) {
        let openRow = openRows[i];

        openRow.getElementsByTagName('button')[0].addEventListener('click', (event) => {
            openRow.remove();
        });
    }
}

/**
 * Creates a new open on row
 */
function addOpenRow() {
    resetInvalidOpenRowInput();

    // Get the values from the add row
    let day = toPascalCase(intToDayOfWeek(addOpenTableRow.getElementsByTagName('td')[0].getElementsByTagName('select')[0].value));
    let from = addOpenTableRow.getElementsByTagName('td')[1].getElementsByTagName('input')[0].value;
    let to = addOpenTableRow.getElementsByTagName('td')[2].getElementsByTagName('input')[0].value;

    // Create the new open on row
    let newOpenRow = createNewOpenRow(day, from, to);

    // Validate the new open on row
    if (!validateOpenRowAdd(newOpenRow))
        return;

    // Add the open on row
    addOpenRowSorted(newOpenRow);
    removeOpenRowOnClick();
    resetOpenRowAddInputs();
}

/**
 * Resets the inputs of the add open on row
 */
function resetOpenRowAddInputs() {
    addOpenTableRow.getElementsByTagName('td')[0].getElementsByTagName('select')[0].value = '0';
    addOpenTableRow.getElementsByTagName('td')[1].getElementsByTagName('input')[0].value = '09:00';
    addOpenTableRow.getElementsByTagName('td')[2].getElementsByTagName('input')[0].value = '17:00';
}

function validateOpenRowAdd(newRow) {
    let newRowModel = getOpenRowModel(newRow);

    // from and to have to be filled
    if (newRowModel.from === '' || newRowModel.to === '') {
        invalidOpenRowInput('Please fill in the from and to fields.');
        return false;
    }

    // from and to have to be in the format HH:MM
    const regex = new RegExp('\\b[0-9]{2}:[0-9]{2}\\b');
    if (!regex.test(newRowModel.from) || !regex.test(newRowModel.to)) {
        invalidOpenRowInput('Please fill in the from and to fields in the format HH:MM.');
        return false;
    }

    // from has to be before to
    let from = new Date();
    from.setHours(newRowModel.from.split(':')[0], newRowModel.from.split(':')[1]);

    let to = new Date();
    to.setHours(newRowModel.to.split(':')[0], newRowModel.to.split(':')[1]);

    if (from > to) {
        invalidOpenRowInput('The from time has to be before the to time.');
        return false;
    }

    // The hour span may not be contained in already existing open on rows
    let openRowModels = getOpenRowModels();

    for (let i = 0; i < openRowModels.length; i++) {
        let openRowModel = openRowModels[i];

        // The day has to be the same
        if (openRowModel.day !== newRowModel.day)
            continue;

        // The hour span may not be contained in already existing open on rows
        let openRowFrom = new Date();
        openRowFrom.setHours(openRowModel.from.split(':')[0], openRowModel.from.split(':')[1]);

        let openRowTo = new Date();
        openRowTo.setHours(openRowModel.to.split(':')[0], openRowModel.to.split(':')[1]);

        if (from >= openRowFrom && to <= openRowTo) {
            invalidOpenRowInput('The hour span may not be contained in already existing open on rows.');
            return false;
        }
    }

    return true;
}

function invalidOpenRowInput(message) {
    let errorBox = document.getElementById('openOnErrorBox');
    errorBox.innerHTML = message;
    errorBox.style.display = 'block';
}

function resetInvalidOpenRowInput() {
    let errorBox = document.getElementById('openOnErrorBox');
    errorBox.style.display = 'none';
}

/**
 * Creates a new open on row element
 * @param day - the day of the open on row
 * @param from - the from of the open on row
 * @param to - the to of the open on row
 * @returns {HTMLTableRowElement} - the new open on row element
 */
function createNewOpenRow(day, from, to) {
    let newOpenRow = document.createElement('tr');

    newOpenRow.appendChild(createNewOpenRowText(day));
    newOpenRow.appendChild(createNewOpenRowText(from));
    newOpenRow.appendChild(createNewOpenRowText(to));
    newOpenRow.appendChild(createNewOpenRowRemoveButton());

    return newOpenRow;
}

/**
 * Creates a new open on row text element
 */
function createNewOpenRowText(text) {
    let p = document.createElement('p');
    p.innerHTML = text;

    let td = document.createElement('td');
    td.appendChild(p);

    return td;
}

/**
 * Creates a new open on row remove button element
 */
function createNewOpenRowRemoveButton() {
    let newOpenRowRemoveButton = document.createElement('button');
    newOpenRowRemoveButton.type = 'button';
    newOpenRowRemoveButton.classList.add('logo-button');
    newOpenRowRemoveButton.id = "removeOpenRowButton";

    newOpenRowRemoveButton.innerHTML = '<img src="../Icons/minus.png" alt="remove icon">';

    let td = document.createElement('td');
    td.appendChild(newOpenRowRemoveButton);

    return td;
}

addOpenTableRow.getElementsByTagName('td')[addOpenTableRow.getElementsByTagName('td').length - 1].
getElementsByTagName('button')[0].addEventListener('click', (event) => {
    addOpenRow();
});

removeOpenRowOnClick();

/* FUTURE LOCATIONS */

let futureLocationsTable = document.getElementsByClassName('future-locations')[0].getElementsByClassName('editonly')[0];
let addFutureLocationRow = futureLocationsTable.getElementsByTagName('tr')[futureLocationsTable.getElementsByTagName('tr').length - 1];

/**
 * Gets the future location rows from the table
 */
function getFutureLocationRows() {
    let futureLocationRows = [];
    let trs = futureLocationsTable.getElementsByTagName('tr');
    for (let i = 1; i < trs.length - 1; i++) /* Skip the first and last row (the header and the add row) */
        futureLocationRows.push(trs[i]);

    return futureLocationRows;
}

/**
 * Gets the future location row models from the table
 */
function getFutureLocationRowModels() {
    let futureLocationRowModels = [];
    let futureLocationRows = getFutureLocationRows();

    for (let i = 0; i < futureLocationRows.length; i++) {
        let futureLocationRow = futureLocationRows[i];
        futureLocationRowModels.push(getFutureLocationRowModel(futureLocationRow));
    }

    return futureLocationRowModels;
}

/**
 * Gets the future location row model of a specified row
 */
function getFutureLocationRowModel(row) {
    return {
        date: row.getElementsByTagName('p')[0].innerHTML,
        location: row.getElementsByTagName('p')[1].innerHTML,
    }
}

/**
 * Sorts the future location rows by date
 */
function addFutureLocationRowSorted(futureLocationRow) {
    // Add the new future location row
    let tbody = futureLocationsTable.children[0];

    let futureLocationRows = getFutureLocationRows();

    // If there are no future location rows yet, just add the new one before the add row
    if (futureLocationRows.length === 0) {
        tbody.insertBefore(futureLocationRow, addFutureLocationRow);
        return;
    }

    tbody.insertBefore(futureLocationRow, getFutureLocationRows()[0]);

    // Sort the new future location row on the already sorted rows
    futureLocationRows = getFutureLocationRows();
    let isSorted = false;
    while (!isSorted) {
        if (futureLocationRow.nextElementSibling === addFutureLocationRow) {
            isSorted = true;
            continue;
        }


        let compRes = compareDates(getFutureLocationRowModel(futureLocationRow).date, getFutureLocationRowModel(futureLocationRow.nextElementSibling).date);

        // Swap the new row with its sibling if it's later
        if (compRes > 0)
            swapNodes(futureLocationRow, futureLocationRow.nextElementSibling);
        else
            isSorted = true;
    }
}

/**
 * Removes a future location row if the user clicks on the remove button
 */
function removeFutureLocationRowOnClick() {
    let futureLocationRows = getFutureLocationRows();

    for (let i = 0; i < futureLocationRows.length; i++) {
        let futureLocationRow = futureLocationRows[i];

        futureLocationRow.getElementsByTagName('button')[0].addEventListener('click', (event) => {
            futureLocationRow.remove();
        });
    }
}

/**
 * Creates a new future location row
 */
function addFutureLocationRowToTable() {
    resetInvalidFutureLocationRowInput();

    // Get the values from the add row
    let date = addFutureLocationRow.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value;
    let locationInfo = addFutureLocationRow.getElementsByTagName('td')[1].getElementsByTagName('input');
    let city = locationInfo[0].value;
    let street = locationInfo[1].value;
    let postal = locationInfo[2].value;
    let house = locationInfo[3].value;
    let bus = locationInfo[4].value;
    let location = getLocationString(city, street, postal, house, bus);

    // Create the new future location row
    let newFutureLocationRow = createNewFutureLocationRow(location, date);

    // Validate the new future location row
    if (!validateFutureLocationRowAdd(date, city, street, postal, house, bus))
        return;

    // Add the future location row
    addFutureLocationRowSorted(newFutureLocationRow);
    removeFutureLocationRowOnClick();
    resetFutureLocationAddRowInputs();
}

function resetFutureLocationAddRowInputs() {
    addFutureLocationRow.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value = '';
    let locationInfo = addFutureLocationRow.getElementsByTagName('td')[1].getElementsByTagName('input');
    locationInfo[0].value = '';
    locationInfo[1].value = '';
    locationInfo[2].value = '';
    locationInfo[3].value = '';
    locationInfo[4].value = '';
}

function getLocationString(city, street, postal, house, bus) {
    let location = street + " " + house;

    if (bus !== null && bus !== undefined && bus !== '')
        location += " " + bus;
    location += ", " + postal + " " + city.toUpperCase();

    return location;
}

function validateFutureLocationRowAdd(date, city, street, postal, house, bus) {
    // location and date have to be filled
    if (city    === '' || city    === null || city      === undefined ||
        street  === '' || street  === null || street    === undefined ||
        postal  === '' || postal  === null || postal    === undefined ||
        house   === '' || house   === null || house     === undefined)
    {
        invalidFutureLocationRowInput('Please fill in the required* fields.');
        return false;
    }

    if (date    === '' || date    === null || date      === undefined) {
        invalidFutureLocationRowInput('Please fill in the date field.');
        return false;
    }

    // Streets can only contain letters and dashes
    let streetRegex = /^[a-zA-Z\s\-]+$/;
    if (!streetRegex.test(street)) {
        invalidFutureLocationRowInput('Streets can only contain letters and dashes.');
        return false;
    }

    // Cities as well
    let cityRegex = /^[a-zA-Z\s\-]+$/;
    if (!cityRegex.test(city)) {
        invalidFutureLocationRowInput('City can only contain letters and dashes.');
        return false;
    }

    // date has to be in the format DD/MM/YYYY
    const regex = new RegExp('\\b[0-9]{4}-[0-9]{2}-[0-9]{2}\\b');
    if (!regex.test(date)) {
        invalidFutureLocationRowInput('Please fill in the date field in the format DD-MM-YYYY.');
        return false;
    }

    // date has to be in the future
    let dateModel = new Date();
    dateModel.setFullYear(date.split('-')[0], date.split('-')[1] - 1,date.split('-')[2]);

    if (dateModel < new Date()) {
        invalidFutureLocationRowInput('The date has to be in the future.');
        return false;
    }

    return true;
}

function invalidFutureLocationRowInput(message) {
    let errorBox = document.getElementById('futureLocationsErrorBox');
    errorBox.innerHTML = message;
    errorBox.style.display = 'block';
}

function resetInvalidFutureLocationRowInput() {
    let errorBox = document.getElementById('futureLocationsErrorBox');
    errorBox.style.display = 'none';
}

/**
 * Creates a new future location row element
 * @param location - the location of the future location row
 * @param date - the date of the future location row
 * @returns {HTMLTableRowElement} - the new future location row element
 */
function createNewFutureLocationRow(location, date) {
    let newFutureLocationRow = document.createElement('tr');

    newFutureLocationRow.appendChild(createNewFutureLocationRowText(date));
    newFutureLocationRow.appendChild(createNewFutureLocationRowText(location));
    newFutureLocationRow.appendChild(createNewFutureLocationRowRemoveButton());

    return newFutureLocationRow;
}

/**
 * Creates a new future location row text element
 */
function createNewFutureLocationRowText(text) {
    let p = document.createElement('p');
    p.innerHTML = text;

    let td = document.createElement('td');
    td.appendChild(p);

    return td;
}

/**
 * Creates a new future location row remove button element
 */
function createNewFutureLocationRowRemoveButton() {
    let newFutureLocationRowRemoveButton = document.createElement('button');
    newFutureLocationRowRemoveButton.type = 'button';
    newFutureLocationRowRemoveButton.classList.add('logo-button');
    newFutureLocationRowRemoveButton.id = "removeFutureLocationRowButton";

    newFutureLocationRowRemoveButton.innerHTML = '<img src="../Icons/minus.png" alt="remove icon">';

    let td = document.createElement('td');
    td.appendChild(newFutureLocationRowRemoveButton);

    return td;
}

addFutureLocationRow.getElementsByTagName('td')[addFutureLocationRow.getElementsByTagName('td').length - 1].
getElementsByTagName('button')[0].addEventListener('click', (event) => {
    addFutureLocationRowToTable();
});

removeFutureLocationRowOnClick();

/* TAGS */

let tagsTable = document.getElementsByClassName('tags')[0].getElementsByTagName('table')[0];
let addTagRow = tagsTable.getElementsByTagName('tr')[tagsTable.getElementsByTagName('tr').length - 1];

/**
 * Gets the tag rows from the table
 * @returns {HTMLTableRowElement[]} - the tag rows from the table
 */
function getTagRows() {
    let tagRows = [];
    let trs = tagsTable.getElementsByTagName('tr');
    for (let i = 1; i < trs.length - 1; i++) /* Skip the first and last row (the header and the add row) */
        tagRows.push(trs[i]);

    return tagRows;
}

/**
 * Gets the tag row model of a specified row
 */
function getTagRowModel(row) {
    return {
        tag: row.getElementsByTagName('p')[0].innerHTML,
    }
}

/**
 * Removes a tag row if the user clicks on the remove button
 */
function removeTagRowOnClick() {
    let tagRows = getTagRows();

    for (let i = 0; i < tagRows.length; i++) {
        let tagRow = tagRows[i];

        tagRow.getElementsByTagName('button')[0].addEventListener('click', (event) => {
            tagRow.remove();
        });
    }
}

/**
 * Creates a new tag row
 */
function addTagRowToTable() {
    resetInvalidTagRowInput();

    // Get the values from the add row
    let tag = addTagRow.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value;

    // Create the new tag row
    let newTagRow = createNewTagRow(tag);

    // Validate the new tag row
    if (!validateTagRowAdd(tag))
        return;

    // Add the tag row
    let tbody = tagsTable.children[0];
    tbody.insertBefore(newTagRow, addTagRow);
    removeTagRowOnClick();
    resetTagRowAddInput();
}

function resetTagRowAddInput() {
    addTagRow.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value = '';
}

function validateTagRowAdd(tag) {
    // tag has to be filled
    if (tag === '' || tag === null || tag === undefined) {
        invalidTagRowInput('Please fill in the required* fields.');
        return false;
    }

    // tag may not appear twice
    let tagRows = getTagRows();

    for (let i = 0; i < tagRows.length; i++) {
        let tagRow = tagRows[i];

        if (getTagRowModel(tagRow).tag === tag) {
            invalidTagRowInput('The tag may not appear twice.');
            return false;
        }
    }

    return true;
}

function invalidTagRowInput(message) {
    let errorBox = document.getElementById('tagsErrorBox');
    errorBox.innerHTML = message;
    errorBox.style.display = 'block';
}

function resetInvalidTagRowInput() {
    let errorBox = document.getElementById('tagsErrorBox');
    errorBox.style.display = 'none';
}

/**
 * Creates a new tag row element
 * @param tag - the tag of the tag row
 * @returns {HTMLTableRowElement} - the new tag row element
 */
function createNewTagRow(tag) {
    let newTagRow = document.createElement('tr');

    newTagRow.appendChild(createNewTagRowText(tag));
    newTagRow.appendChild(createNewTagRowRemoveButton());

    return newTagRow;
}

/**
 * Creates a new tag row text element
 */
function createNewTagRowText(text) {
    let p = document.createElement('p');
    p.innerHTML = text;

    let td = document.createElement('td');
    td.appendChild(p);

    return td;
}

/**
 * Creates a new tag row remove button element
 */
function createNewTagRowRemoveButton() {
    let newTagRowRemoveButton = document.createElement('button');
    newTagRowRemoveButton.type = 'button';
    newTagRowRemoveButton.classList.add('logo-button');
    newTagRowRemoveButton.id = "removeTagRowButton";

    newTagRowRemoveButton.innerHTML = '<img src="../Icons/minus.png" alt="remove icon">';

    let td = document.createElement('td');
    td.appendChild(newTagRowRemoveButton);

    return td;
}

addTagRow.getElementsByTagName('td')[addTagRow.getElementsByTagName('td').length - 1].
getElementsByTagName('button')[0].addEventListener('click', (event) => {
    addTagRowToTable();
});

removeTagRowOnClick();

