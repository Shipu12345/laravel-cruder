<?php

namespace Shipu\Cruder\Exceptions;

use Exception;

class MissingRelationDataException extends Exception
{
    public function __construct($type = null)
    {
        parent::__construct($type . ' is missing required related data.');
    }
}
