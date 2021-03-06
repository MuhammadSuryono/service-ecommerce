<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('storage/images/{filename}', 'CustomerController@getAll');


$router->post('notif', 'TransactionController@notif');

$router->group(['prefix'=>'api/v1'], function() use ($router)
{
    // Customer Route
    $router->post('auth/login', 'AuthControllers@authLogin');
    $router->post('customer/register', 'CustomerController@register');
    $router->put('customer/{id}', 'CustomerController@update');
    $router->delete('customer/{id}', 'CustomerController@delete');
    $router->get('customer', 'CustomerController@getAll');
    $router->get('customer/{id}', 'CustomerController@getById');

    // Order Route
    $router->post('order/push', 'OrderController@insert');
    $router->get('order', 'OrderController@getAll');
    $router->get('order/{id}', 'OrderController@getById');
    $router->post('order/by-user', 'OrderController@GetOrderByUserId');
	$router->get('order/order-id/{orderId}', 'OrderController@GetOrder');
	$router->get('order/cancel/{orderId}', 'OrderController@CancelOrder');
    $router->get('order/received/{orderId}', 'OrderController@ReceivedOrder');


    // category route
    $router->get('category', 'CategoryController@getAll');
    $router->post('category', 'CategoryController@create');
    $router->get('category/{id}', 'CategoryController@getById');
    $router->post('category/update', 'CategoryController@update');
    $router->delete('category/{id}', 'CategoryController@delete');


    // Product Route
    $router->post('product', 'ProductController@insert');
    $router->post('product/{id}', 'ProductController@update');
    $router->delete('product/{id}', 'ProductController@delete');
    $router->get('product', 'ProductController@getAll');
    $router->get('product/category', 'ProductController@getProductByCategory');
    $router->get('product/{id}', 'ProductController@getById');

    // Payment Route
    $router->post('transactions/push', 'TransactionController@create');
    $router->get('transactions', 'TransactionController@getAll');
    $router->get('transactions/{id}', 'TransactionController@getById');
    $router->post('transactions/midtrans/push', 'TransactionController@notif');
	$router->get('transactions/detail/{orderId}', 'TransactionController@getByOrderId');
	$router->post('transactions/status-pengiriman', 'TransactionController@UpdateStatusPengiriman');

    $router->get('ongkir/province', 'RajaOngkir\RajaOngkirController@GetProvince');
    $router->get('ongkir/city', 'RajaOngkir\RajaOngkirController@GetCity');
    $router->post('ongkir/cost', 'RajaOngkir\RajaOngkirController@GetCost');

    $router->get('cart/{userId}', 'CartController@getCartByUser');
    $router->post('cart', 'CartController@addCart');
    $router->put('cart/{id}', 'CartController@updateQty');
    $router->delete('cart/{id}', 'CartController@removeCart');
    $router->get('cart/checkout/{userId}', 'CartController@getCartByCheckout');
    $router->get('cart/transaction/{orderId}', 'CartController@getCartByOrderId');

    $router->get('report', 'ReportController@getReport');
    $router->get('send_email' ,'SendEmail@mail');
    $router->post('otp/validate', 'OtpController@ValidateOtp');
});
