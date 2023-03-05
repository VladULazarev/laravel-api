<?php

namespace App\Traits;

trait HttpResponses
{
    /**
     * The message, if the request was successful
     *
     * @return mixed
     */
    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Request was successful.',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * The message, if the request was not successful
     *
     * @return mixed
     */
    protected function error($data, $message = null, $code)
    {
        return response()->json([
            'status' => 'Error has occurred...',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}