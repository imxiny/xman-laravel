<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CustomException extends Exception
{
    protected $data;

    public function __construct($message = "", $code = 0, $data = [], Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        $res = ['message' => $this->getMessage(),];
        if ($this->data) {
            $res['data'] = $this->data;
        }
        return response()->json($res)->setStatusCode($this->getCode() ?: 404);
    }
}
