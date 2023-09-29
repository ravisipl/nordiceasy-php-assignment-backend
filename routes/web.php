<?php


/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('', function () use ($router) {
    return $router->app->version();
});


/*======================== API route api/v1 group =============================*/
$router->group(['prefix' => 'api/v1'], function () use ($router) {

    $router->group(['prefix' => 'client'], function () use ($router) {
        // Get Clients
        $router->get('', 'ClientController@index');

        // Store client
        $router->post('store', 'ClientController@store');

        // Update client
        $router->put('update/{id}', 'ClientController@update');

        // Get Client
        $router->get('{id}', 'ClientController@show');

        // Update client
        $router->delete('destroy/{id}', 'ClientController@destroy');
    });
});



