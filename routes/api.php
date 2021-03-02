<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API

Route::group([
    'middleware' => ['guest:api'],
    'prefix' => 'v1/auth'
], function() {
    Route::post('request-code', 'Api\v1\Auth\LoginController@requestCode');
    Route::post('login', 'Api\v1\Auth\LoginController@login');
    Route::post('login/refresh', 'Api\v1\Auth\LoginController@refresh');

});

Route::group([
    'middleware' => ['guest:api'],
    'prefix' => 'v1/user',
    'namespace' => 'Api\v1\User'
], function() {

    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'ResetPasswordController@reset');

    Route::post('register', 'RegisterController@register');

});

Route::group([
    'middleware' => ['guest:api'],
    'prefix' => 'v1',
    'namespace' => 'Api\v1'
], function() {

    Route::get('user/{id}', 'User\ProfileController@info');

    Route::get('business-cards/', 'BusinessCardController@index');
});

Route::group([
    'middleware' => ['jwt'],
    'prefix' => 'v1',
    'namespace' => 'Api\v1'
], function() {
    Route::put('user/updateFCM', 'User\ProfileController@updateFCM');
    Route::post('user/logout', 'Auth\LoginController@logout');
    Route::get('user/me', 'Auth\LoginController@me');
    Route::patch('user/profile', 'User\ProfileController@update');
    Route::post('user/avatar', 'User\ProfileController@uploadAvatar');
    Route::delete('user/avatar', 'User\ProfileController@deleteAvatar');
    Route::get('user/{id}', 'User\ProfileController@info');

    Route::get('my-object', 'User\MyObjectController@index');
    Route::get('my-object/{id}', 'User\MyObjectController@load');
    Route::get('my-object/{id}/xml', 'User\MyObjectController@loadXml');
    Route::post('my-object/create', 'User\MyObjectController@create');
    Route::post('my-object/update', 'User\MyObjectController@update');
    Route::delete('my-object/remove/{id}', 'User\MyObjectController@destroy');

    Route::get('orders', 'User\OrdersController@index');
    Route::get('orders/my', 'User\OrdersController@indexMy');
    Route::post('orders/create', 'User\OrdersController@create');
    Route::post('orders/{id}/add-file', 'User\OrdersController@addFileAttachment');
    Route::post('orders/{id}/add-photo', 'User\OrdersController@addPhotoAttachment');

    Route::get('proposals/{id}', 'User\ProposalController@index');
    Route::post('proposals/{id}/create', 'User\ProposalController@create');

    Route::get('reviews/{id}', 'ReviewController@index');
    Route::post('reviews/{id}/create', 'ReviewController@create');

    Route::post('business-cards/create', 'BusinessCardController@create');
    Route::post('business-cards/update', 'BusinessCardController@update');
    Route::delete('business-cards/delete', 'BusinessCardController@destroy');

    Route::get('chat/dialogs', 'Chat\DialogsController@index');
    Route::get('chat/dialog/create/{toId}', 'Chat\DialogsController@create');
    Route::get('chat/dialog/{id}/all', 'Chat\MessagesController@getAllMessages');
    Route::post('chat/dialog/{id}/all', 'Chat\MessagesController@postLastMessages');
    Route::post('chat/dialog/{id}/send', 'Chat\MessagesController@sendMessage');
});

// SPA

Route::group([
    'guard' => 'spa',
    'middleware' => ['guest:api'],
    'namespace' => 'Spa\Auth'
], function() {
    Route::post('login', 'LoginController@login');
    Route::post('login/refresh', 'LoginController@refresh');

    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'ResetPasswordController@reset');

    Route::post('register', 'RegisterController@register');
});

Route::group([
    'middleware' => ['jwt'],
    'namespace' => 'Spa'
], function() {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('me', 'Auth\LoginController@me');
    Route::put('profile', 'ProfileController@update');

    Route::get('support/dialogs', 'Support\DialogsController@index');
    Route::get('support/dialog/{id}/all', 'Support\MessagesController@getAllMessages');
    Route::post('support/dialog/{id}/all', 'Support\MessagesController@postLastMessages');
    Route::post('support/dialog/{id}/send', 'Support\MessagesController@sendMessage');
});

// Old
/*
Route::group(['middleware' => ['guest:api']], function() {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('login/refresh', 'Auth\LoginController@refresh');

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('register', 'Auth\RegisterController@register');
});

Route::group(['middleware' => ['jwt']], function() {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('me', 'Auth\LoginController@me');
    Route::put('profile', 'ProfileController@update');
});
*/
