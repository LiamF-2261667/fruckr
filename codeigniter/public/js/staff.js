// Adding a staff member

const staffForm = document.getElementById('addStaffForm');
const successBox = document.getElementById('addStaffSuccess');
const addStaffLoadingIcon = document.getElementById('addStaffLoading');

staffForm.addEventListener('submit', (e) => {
   e.preventDefault();
   addStaffMember();
});

function addStaffMember() {
    showAddLoadingIcon();
    hideError('addStaffError');
    successBox.style.display = 'none';

    const email = document.getElementById('email').value;

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/addWorker", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfullAdd, handleAddError);

    // Sending the request
    xmlHttp.send(JSON.stringify({email: email}));

    // Clear the form
    document.getElementById('email').value = '';
}

function showAddLoadingIcon() {
    addStaffLoadingIcon.style.display = 'block';
}

function hideAddLoadingIcon() {
    addStaffLoadingIcon.style.display = 'none';
}

function handleAddError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('addStaffError', errorMsg);
    hideAddLoadingIcon();
}

function onSuccessfullAdd(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleAddError(jsonResponse.error);
    else {
        // Send a success msg
        successBox.style.display = 'block';
        successBox.innerHTML = jsonResponse.email + ' is now invited to work at your food truck!';
        successBox.style.display = 'block';

        hideAddLoadingIcon();
    }
}

// Deleting a staff member

const deleteStaffBtns = document.getElementsByClassName('remove-staff-btn');
const deleteStaffLoadingIcon = document.getElementById('deleteStaffLoading');

for (let i = 0; i < deleteStaffBtns.length; i++) {
    deleteStaffBtns[i].addEventListener('click', (e) => {
        e.preventDefault();
        deleteStaffMember(e.target.closest('.staffMember'));
    });
}

function deleteStaffMember(element) {
    showDeleteLoadingIcon();
    hideError('deleteStaffError');

    const uid = element.getAttribute('data-uid');

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/foodtruck/removeWorker", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfullDelete, handleDeleteError);

    // Sending the request
    xmlHttp.send(JSON.stringify({uid: uid}));
}

function showDeleteLoadingIcon() {
    deleteStaffLoadingIcon.style.display = 'block';
}

function hideDeleteLoadingIcon() {
    deleteStaffLoadingIcon.style.display = 'none';
}

function handleDeleteError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('deleteStaffError', errorMsg);
    hideDeleteLoadingIcon();
}

function onSuccessfullDelete(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleDeleteError(jsonResponse.error);
    else {
        // Remove the staff member from the list
        let staffMembers = document.getElementsByClassName('staffMember');
        for (let i = 0; i < staffMembers.length; i++) {
            if (staffMembers[i].getAttribute('data-uid') === jsonResponse.uid) {
                staffMembers[i].remove();
                break;
            }
        }

        hideDeleteLoadingIcon();
    }
}