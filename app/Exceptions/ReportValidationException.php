<?php

namespace App\Exceptions;

use Exception;

class ReportValidationException extends Exception
{
    public function errors()
    {
        return json_decode($this->getMessage());
    }

    public static function error($error)
    {
        return json_encode(['error' => $error]);
    }
}
