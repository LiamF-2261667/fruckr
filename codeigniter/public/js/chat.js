// On document load, scroll to the bottom of the chat

const chat = document.getElementById('chatMessages');
const recipientName = document.getElementById('recipientName').innerText;
const contentInput = document.getElementById('contentInput');
const messageForm = document.getElementById('chatInput');
const maxMsgLength = document.getElementById('maxMsgLength');

document.addEventListener('DOMContentLoaded', () => {
    chat.scrollTop = chat.scrollHeight;
});

// On enter, submit message

contentInput.addEventListener('keydown', (e) => {
    if (e.keyCode === 13 && !e.shiftKey) {
        e.preventDefault();
        sendMessage(contentInput.value);
    }
});

// Updating max msg length indicator

contentInput.addEventListener('input', () => {
    maxMsgLength.innerText = contentInput.value.length + "/500";

    if (contentInput.value.length > 500)
        maxMsgLength.style.color = 'red';
    else
        maxMsgLength.style.color = 'grey';
});

// Sending a message
messageForm.addEventListener('submit', (e) => {
    e.preventDefault();
    sendMessage(document.getElementById('contentInput').value);
});

function sendMessage(message) {
    // Hide error box
    hideError('messageErrorBox');

    // Validate the message
    if (!validateMessage(message))
        return;

    // Clear the input
    document.getElementById('contentInput').value = "";

    // Add the message to the chat
    let now = new Date();
    addMessage(message, true, now.getHours() + ":" + now.getMinutes());

    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/chat/sendMessage", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfulSend, handleMessageError);

    // Sending the request
    xmlHttp.send(JSON.stringify({content: message}));
}

function onSuccessfulSend(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleMessageError(jsonResponse.error);

    // If it was a success
    else {

    }
}

function validateMessage(message) {
    if (message.trim() === 0) {
        handleMessageError('Message cannot be empty');
        return false;
    }

    if (message.length > 500) {
        handleMessageError('Message cannot be longer than 500 characters');
        return false;
    }

    return true;
}

function handleMessageError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    displayError('messageErrorBox', errorMsg);
}

// ADDING MSG TO THE CHAT

function addMessage(content, sendByYou, time) {
    // Creating the message li
    let message = document.createElement('li');
    message.classList.add('message');

    // Creating the msg object
    let messageObj = getMessageObject(sendByYou);

    // Creating the message content
    let messageContent = getMessageContent(content);

    // Creating the extra info
    let extraInfo = getMessageExtraInfo(time, sendByYou);

    // Appending the content and extra info to the message object
    messageObj.appendChild(messageContent);
    messageObj.appendChild(extraInfo);

    // Appending the message object to the message
    message.appendChild(messageObj);

    // Appending the message to the chat
    chat.appendChild(message);

    // Scrolling to the bottom of the chat
    chat.scrollTop = chat.scrollHeight;
}

function getMessageObject(sendByYou) {
    let messageObject = document.createElement('div');
    messageObject.classList.add('message-object');

    // Adding the correct sender class
    if (sendByYou)
        messageObject.classList.add('you');
    else
        messageObject.classList.add('recipient');

    return messageObject;
}

function getMessageContent(content) {
    let messageContent = document.createElement('p');
    messageContent.classList.add('content');
    messageContent.innerText = content;

    return messageContent;
}

function getMessageExtraInfo(time, sendByYou) {
    let extraInfo = document.createElement('p');
    extraInfo.classList.add('extra-info');
    extraInfo.innerText = time + " - " + (sendByYou ? "You" : recipientName);

    return extraInfo;
}