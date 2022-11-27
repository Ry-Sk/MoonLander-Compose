<?php

namespace Bot\Provider\IIROSE\Resolver;

use Bot\Provider\IIROSE\Event\GotoEvent;
use Bot\Provider\IIROSE\IIROSEProvider;
use Bot\Provider\IIROSE\Resolver;
use Models\Bot;

class GotoResolver implements Resolver
{
    public function isPacket($message, $firstChar, $count, $explode)
    {
        if (/* $firstChar == '"' &&  */$count == 12
            && substr($explode[3],0,2)=='\'2'
            && $explode[0]>Bot::$instance->startAt
            && $explode[2]!=Bot::$instance->username) 
        {
            return true;
        }
    }

    public function pharse($message)
    {
        $a = IIROSEProvider::decode(explode('>', $message));
        return new GotoEvent(
            $a[5],
            $a[8],
            $a[2],
            $a[9],
            $a[1],
            substr($a[11], 1),
            $a[0]
        );
    }
}
