<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use LogicException;

class InvalidStatusTransitionException extends LogicException
{
    public function __construct(
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {
        parent::__construct(sprintf(
            'Transition interdite : « %s » vers « %s ».',
            $from->getLabel(),
            $to->getLabel(),
        ));
    }
}
