// DATA to be used across the project



// METHODS to be used by all other JS

/**
 * Disable scrolling.
 * src: https://www.geeksforgeeks.org/how-to-disable-scrolling-temporarily-using-javascript/
 */
function disableScroll() {
    // Get the current page scroll position
    let scrollTop = scrollY;
    let scrollLeft = scrollX;

    // if any scroll is attempted,
    // set this to the previous value
    window.onscroll = function () {
        window.scrollTo({
            left: scrollLeft,
            top: scrollTop,
            behavior: 'instant'
        });
    };
}

/**
 * Enable scrolling.
 */
function enableScroll() {
    window.onscroll = function () { };
}

/**
 * View a rating as stars.
 * @param rating - the rating to view
 * @returns {string} - the rating as stars
 */
function viewRating(rating) {
    if (typeof rating === 'string')
        return rating;

    else {
        let ratingString = '';

        for (let i = 1; i <=5; i++) {
            if (i > rating)
                ratingString += '☆';
            else
                ratingString += '★';
        }

        return ratingString;
    }
}

/**
 * Remove all the children of an element.
 * @param element - the element to remove the children of
 */
function removeChildren(element) {
    while (element.firstChild)
        element.removeChild(element.firstChild);
}

/**
 * Display an error inside an error box.
 * @param errorBoxId - the id of the error box to display the error in
 * @param errorMessage - the error message to display
 * @param marginTop - the margin from the top of the page to scroll to
 */
function displayError(errorBoxId, errorMessage, marginTop = 100) {
    let errorBox = document.getElementById(errorBoxId);
    errorBox.innerHTML = errorMessage;
    setVisibility(document.getElementById(errorBoxId), true);

    // Check if the error box is inside the viewport
    let rect = errorBox.getBoundingClientRect();
    if (rect.top >= 0 && rect.left >= 0 && rect.bottom <= window.innerHeight && rect.right <= window.innerWidth)
        return;

    // Otherwise scroll to the error box with a margin so the errorBox isn't behind the nav bar
    let y = errorBox.getBoundingClientRect().top + window.scrollY - marginTop;
    if (y < 0) y = 0;

    window.scrollTo({top: y, behavior: 'smooth'})
}

/**
 * Hide an error box.
 * @param errorBoxId - the id of the error box to hide
 */
function hideError(errorBoxId) {
    setVisibility(document.getElementById(errorBoxId), false);
}

/**
 * Hide all the error boxes on the current page.
 */
function hideAllErrors() {
    let errorBoxes = document.getElementsByClassName('alert-danger');

    for (let i = 0; i < errorBoxes.length; i++)
        setVisibility(errorBoxes[i], false);
}

/**
 * Get the pascal case version of a string.
 * @param string - the string to convert
 * @returns {*} - the pascal case version of the string
 * @src: https://stackoverflow.com/questions/4068573/convert-string-to-pascal-case-aka-uppercamelcase-in-javascript
 */
function toPascalCase(string) {
    return string.replace(/\w+/g,
        function(w){return w[0].toUpperCase() + w.slice(1).toLowerCase();});
}

const days = { 0: 'monday', 1: 'tuesday', 2: 'wednesday', 3: 'thursday', 4: 'friday', 5: 'saturday', 6: 'sunday' };

/**
 * Get the day of the week from a string.
 * @param day - the day of the week number
 * @returns {*} - the day of the week as a string
 */
function intToDayOfWeek(day) {
    return days[day];
}

/**
 * Get the day of the week as a number from a string.
 * @param day - the day of the week as a string
 * @returns {*} - the day of the week number
 */
function dayOfWeekToInt(day) {
    for (let daysKey in days) {
        if (days[daysKey] === day.trim().toLowerCase())
            return daysKey;
    }
}

/**
 * Compare two days of the week.
 * @param day1 - the first day
 * @param day2 - the second day
 * @returns {number} - the difference between the two days
 */
function compareDays(day1, day2) {
    return dayOfWeekToInt(day1) - dayOfWeekToInt(day2);
}

/**
 * Compare two day times.
 * @param time1 - the first day time
 * @param time2 - the second day time
 * @returns {number} - the difference between the two day times
 */
function compareDayTimes(time1, time2) {
    let dayDiff = compareDays(time1.day, time2.day);

    if (dayDiff !== 0)
        return dayDiff;

    let t1From = new Date()
    t1From.setHours(time1.from.split(':')[0], time1.from.split(':')[1]);

    let t2From = new Date()
    t2From.setHours(time2.from.split(':')[0], time2.from.split(':')[1]);

    return t1From - t2From;
}

/**
 * Compare two dates as strings.
 * @param date1 - the first date as a string
 * @param date2 - the second date as a string
 * @returns {number} - the difference between the two dates
 */
function compareDates(date1, date2) {
    let date1Model = new Date(date1);
    let date2Model = new Date(date2);

    return date1Model - date2Model;
}

/**
 * Convert a file model to an HTML element.
 * @param file - the file model
 * @returns {string} - the HTML element
 */
function fileToHtml(file) {
    let tag = getElementTagFromFileType(file.type);

    if (tag === 'video') {
        return `<${tag} controls><source src="${file.base64}" type="video/mp4"></${tag}>`;
    }
    else
        return `<${tag} src="${file.base64}"></${tag}>`;
}

/**
 * Get the tag of an element based on the file type.
 * @param type - the file type
 * @returns {string} - the tag of the element
 */
function getElementTagFromFileType(type) {
    switch (type.trim().toLowerCase()) {
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'webp':
        case 'img':
            return 'img';

        case 'mp4':
        case 'vid':
        case 'video':
            return 'video';

        case 'ogg':
            return 'audio';

        default:
            return 'div';
    }
}

/**
 * Make a function receive the base64 of an input field each time it changes.
 * @param fileInputField - the input field to get the file from
 * @param callback - the function to call when the file is read (each time after it changes), containing the result as a parameter
 * result = {base64, type, size}
 * @src https://stackoverflow.com/questions/17710147/image-convert-to-base64
 */
function readFileOnChange(fileInputField, callback) {
    function readFile(fileInputField, callback) {
        if (fileInputField.files.length === 0)
            return;

        const FR = new FileReader();

        FR.addEventListener("load", function (e) {
            let file = {
                base64: e.target.result,
                type: fileInputField.value.split('.').pop(),
                size: fileInputField.files[0].size
            }

            callback(file);
        });

        FR.readAsDataURL(fileInputField.files[0]);
    }

    fileInputField.addEventListener('change', (event) => {
        readFile(fileInputField, callback);
    });
}

/**
 * Swaps the position of two elements in the DOM.
 * @param node1 - the first element
 * @param node2 - the second element
 * @src https://stackoverflow.com/questions/4406206/how-to-swap-position-of-html-tags
 */
function swapNodes(node1, node2) {
    let node2_copy = node2.cloneNode(true);
    node1.parentNode.insertBefore(node2_copy, node1);
    node2.parentNode.insertBefore(node1, node2);
    node2.parentNode.replaceChild(node2, node2_copy);
}

/**
 * Sets the visibility of an element to the given value.
 * @param element - the element to set the visibility of
 * @param value - the value to set the visibility to
 */
function setVisibility(element, value) {
    if (value)
        element.style.display = "block";
    else
        element.style.display = "none";
}

/**
 * Toggles the visibility of an element.
 * @param element - the element to toggle the visibility of
 */
function toggleVisibility(element) {
    setVisibility(element, element.style.display !== "block");
}

/**
 * Handles the result of an AJAX request.
 * @param xmlHttp - the XMLHttpRequest object
 * @param onSuccess - the function to call if the request was successful
 * @param onFailure - the function to call if the request was not successful
 * @returns {(function(): void)|*} - the function to call when the AJAX request is done
 */
function handleAjaxResult(xmlHttp, onSuccess, onFailure) {
    return function () {
        if (xmlHttp.readyState === 4) {
            if (xmlHttp.status === 200) {
                // Request was successful
                onSuccess(xmlHttp);
            } else {
                // Request was not successful
                onFailure(xmlHttp.statusText);
            }
        }
    }
}

// HELPER METHODS for initialisation methods
function setMainHamburgerHoverEvent() {
    setVisibility(document.getElementById('mainNavVerticalList'), true);
}

// METHODS to initialize general JS

function setupNavBarListeners() {
    document.getElementById('mainNav').addEventListener('mouseleave', (event) => {
        setVisibility(document.getElementById('mainNavVerticalList'), false);
    }, false);

    document.getElementById('mainNavHamburger').addEventListener('click', (event) => {
        toggleVisibility(document.getElementById('mainNavVerticalList'));
    }, false);
}

function onResize() {
    if (screen.width > 600)
        document.getElementById('mainNavHamburger').addEventListener('mouseenter', setMainHamburgerHoverEvent, false);
    else
        document.getElementById('mainNavHamburger').removeEventListener('mouseenter', setMainHamburgerHoverEvent);
}

function setupListeners() {
    window.addEventListener("resize", onResize, false);

    if (document.getElementById('mainNav') != null)
        setupNavBarListeners();
}

// SETUP default webpage

setupListeners();