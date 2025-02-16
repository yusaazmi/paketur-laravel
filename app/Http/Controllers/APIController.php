<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class APIController extends Controller
{

    private $response;

    public function respond($data, $message = null, $status = 200) {
        $response['status'] = $status;
        $response['message'] = $message;
  
        if (isset($data)){
            $response['data'] = $data;
        }  
        return response()->json($response, $status);
    }
}
