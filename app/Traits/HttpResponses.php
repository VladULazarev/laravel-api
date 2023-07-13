<?php

namespace App\Traits;

trait HttpResponses
{
    protected function success($data, $message = null, $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'Request was successful.',
            'message' => $message,
            'data' => $data,
            'statusCode' => $code
        ], $code);
    }

    protected function error($data, $message = null, $code): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'Error has occurred...',
            'message' => $message,
            'data' => $data,
            'statusCode' => $code
        ], $code);
    }
}
