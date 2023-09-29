<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // Sends a success response.
    public function sendSuccessResponse($data = [], $message, $code = 200) {
        $response['success'] = true;
        $response['success_code'] = $code;
        if($message){
            $response['message'] = $message;
        }
        if($data){
            $response['data'] = $data;
        }
        return response()->json($response, $code);
    }

    // Sends a failure response.
    public function sendFailureResponse($message = 'Something went wrong', $code = 422) {
        $response['success'] = false;
        $response['success_code'] = $code;
        $response['message'] = $message;
        // $response['data'] = $data;
        return response()->json($response, $code);
    }
}
