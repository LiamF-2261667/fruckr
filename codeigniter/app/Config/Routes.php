<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomepageController::index');

$routes->get(\App\Controllers\AccessibilityController::$INDEX_ROUTE, 'AccessibilityController::index');

$routes->get(\App\Controllers\CartController::$INDEX_ROUTE, 'CartController::index');
$routes->post(\App\Controllers\CartController::$ADD_TO_CART_ROUTE, 'CartController::addToCart');
$routes->post(\App\Controllers\CartController::$REMOVE_FROM_CART_ROUTE, 'CartController::removeFromCart');

$routes->get(\App\Controllers\ChatController::$INDEX_ROUTE, 'ChatController::index/$1');
$routes->get(\App\Controllers\ChatController::$CREATE_CHAT_ROUTE, 'ChatController::startChat/$1');
$routes->post(\App\Controllers\ChatController::$SEND_MESSAGE_ROUTE, 'ChatController::sendMessage');

$routes->get(\App\Controllers\ChatsController::$CLIENT_ROUTE, 'ChatsController::index');
$routes->get(\App\Controllers\ChatsController::$FOODTRUCK_ROUTE, 'ChatsController::index/$1');

$routes->get(\App\Controllers\CreateReviewController::$INDEX_ROUTE, 'CreateReviewController::index/$1');
$routes->post(\App\Controllers\CreateReviewController::$CREATE_REVIEW_ROUTE, 'CreateReviewController::createReviewForCurrentFoodtruck');

$routes->get(\App\Controllers\FoodtruckController::$INDEX_ROUTE, 'FoodtruckController::index');
$routes->get(\App\Controllers\FoodtruckController::$INDEX_WITH_ID_ROUTE, 'FoodtruckController::index/$1');
$routes->get(\App\Controllers\FoodtruckController::$CREATE_FOODTRUCK_PAGE_ROUTE, 'FoodtruckController::createFoodtruckPage');
$routes->post(\App\Controllers\FoodtruckController::$SAVE_FOODTRUCK_ROUTE, 'FoodtruckController::saveFoodtruck');
$routes->post(\App\Controllers\FoodtruckController::$CREATE_FOODTRUCK_ROUTE, 'FoodtruckController::createFoodtruck');
$routes->post(\App\Controllers\FoodtruckController::$GET_EXTRA_FOOD_ITEM_INFO_ROUTE, 'FoodtruckController::getExtraFoodItemInfo');
$routes->post(\App\Controllers\FoodtruckController::$SAVE_FOOD_ITEM_ROUTE, 'FoodtruckController::saveFoodItem');
$routes->post(\App\Controllers\FoodtruckController::$CREATE_FOOD_ITEM_ROUTE, 'FoodtruckController::createFoodItem');
$routes->post(\App\Controllers\FoodtruckController::$DELETE_FOOD_ITEM_ROUTE, 'FoodtruckController::deleteFoodItem');

$routes->get(\App\Controllers\FoodtruckOrdersController::$INDEX_ROUTE, 'FoodtruckOrdersController::index/$1');
$routes->post(\App\Controllers\FoodtruckOrdersController::$SET_READY_ROUTE, 'FoodtruckOrdersController::setReady');
$routes->post(\App\Controllers\FoodtruckOrdersController::$SET_RECEIVED_ROUTE, 'FoodtruckOrdersController::setReceived');

$routes->get(\App\Controllers\HomepageController::$INDEX_ROUTE, 'HomepageController::index');

$routes->get(\App\Controllers\LoginController::$INDEX_ROUTE, 'LoginController::index');
$routes->post(\App\Controllers\LoginController::$LOGIN_ROUTE, 'LoginController::login');

$routes->get(\App\Controllers\LogoutController::$INDEX_ROUTE, 'LogoutController::index');

$routes->get(\App\Controllers\MyFoodtrucksController::$INDEX_ROUTE, 'MyFoodtrucksController::index');

$routes->get(\App\Controllers\OrderController::$INDEX_ROUTE, 'OrderController::index');
$routes->post(\App\Controllers\OrderController::$POST_ORDER_ROUTE, 'OrderController::postOrder');

$routes->get(\App\Controllers\OrderConfirmationController::$INDEX_ROUTE, 'OrderConfirmationController::index');

$routes->get(\App\Controllers\ProfileController::$INDEX_ROUTE, 'ProfileController::index');
$routes->post(\App\Controllers\ProfileController::$UPDATE_ROUTE, 'ProfileController::update');

$routes->post(\App\Controllers\ReviewController::$GET_CURRENT_FOODTRUCK_REVIEWS_ROUTE, 'ReviewController::getCurrentFoodtruckReviews');
$routes->post(\App\Controllers\ReviewController::$GET_CURRENT_FOODTRUCK_ITEM_REVIEWS_ROUTE, 'ReviewController::getCurrentFoodtruckItemReviews');

$routes->get(\App\Controllers\SearchController::$INDEX_ROUTE, 'SearchController::index');
$routes->post(\App\Controllers\SearchController::$SEARCH_ROUTE, 'SearchController::search');

$routes->get(\App\Controllers\StaffController::$INDEX_ROUTE, 'StaffController::index/$1');
$routes->get(\App\Controllers\StaffController::$ACCEPT_ROUTE, 'StaffController::accept/$1');
$routes->get(\App\Controllers\StaffController::$DECLINE_ROUTE, 'StaffController::decline/$1');
$routes->post(\App\Controllers\StaffController::$ADD_WORKER_ROUTE, 'StaffController::addWorker');
$routes->post(\App\Controllers\StaffController::$REMOVE_WORKER_ROUTE, 'StaffController::removeWorker');

$routes->get(\App\Controllers\SignupController::$INDEX_ROUTE, 'SignupController::index');
$routes->post(\App\Controllers\SignupController::$REGISTER_ROUTE, 'SignupController::register');