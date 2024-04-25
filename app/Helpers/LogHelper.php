<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class LogHelper
{
    public static function logRequest($request, $status, $message)
    {
        $logData = [
            'url' => stripslashes($request->fullUrl()),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'body' => stripslashes(json_encode($request->all())),
            'status' => $status,
            'message' => $message
        ];

        $logString = "[" . date('Y-m-d H:i:s') . "] " . json_encode($logData) . PHP_EOL;
        Log::info()
        File::append(storage_path('logs/requests.log'), $logString);
    }
}
