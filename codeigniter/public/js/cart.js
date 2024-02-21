/* REMOVE CART ITEM FUNCTIONALITY */

// Remove cart item
function removeCartItem(button) {

    // Send the cart item id to the server to remove it from the cart (database)
    const cartItemName = button.parentElement.parentElement.getElementsByClassName('info')[0].
                                                                        getElementsByTagName('h2')[0].innerText;
    sendRemovingCartItemRequest(cartItemName);
}

function sendRemovingCartItemRequest(cartItemName) {
    // Creating the request
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "/cart/remove", true);

    // Handling the response
    xmlHttp.onreadystatechange = handleAjaxResult(xmlHttp, onSuccessfullCartItemRemoval, handleCartItemRemovelError);

    // Sending the request
    xmlHttp.send(JSON.stringify({cartItemName: cartItemName}));
}

function onSuccessfullCartItemRemoval(xhr) {
    //handle the response
    let jsonResponse = JSON.parse(xhr.responseText);
    if (!jsonResponse.success)
        handleCartItemRemovelError(jsonResponse.error);
    else {
        // Update the total order price
        const totalPrice = document.getElementById('total-price');
        totalPrice.innerText = jsonResponse.totalPrice;

        // Update the total item count
        const totalItems = document.getElementById('total-count');
        totalItems.innerText = jsonResponse.totalItemCount;

        // Remove the cart item from the cart (html)
        const cartItemName = jsonResponse.cartItemName;
        const cartItem = document.getElementsByClassName('food-item');
        for (let i = 0; i < cartItem.length; i++) {
            const item = cartItem[i];
            if (item.getElementsByClassName('info')[0].getElementsByTagName('h2')[0].innerText === cartItemName) {
                item.parentElement.parentElement.remove();
                break;
            }
        }

        // Check if the cart is empty
        if (cartItem.length === 0) {
            const cart = document.getElementsByClassName('cart-items')[0];
            cart.innerHTML = '<li class="cart-item">Your cart is empty</li>';
        }
    }
}

function handleCartItemRemovelError(error) {
    let errorMsg = error;
    if (error !== null && error.responseText !== undefined && error.responseText !== null && error.responseText !== "")
        errorMsg = error.responseText;

    console.error(errorMsg);
}

// Add remove item button functionality
const removeCartItemButtons = document.getElementsByClassName('delete-button');
for (let i = 0; i < removeCartItemButtons.length; i++) {
    const button = removeCartItemButtons[i];
    button.addEventListener('click', function (event) {
        removeCartItem(event.target);
    });
}