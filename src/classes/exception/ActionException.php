<?php

namespace iutnc\nrv\exception;

class ActionException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}