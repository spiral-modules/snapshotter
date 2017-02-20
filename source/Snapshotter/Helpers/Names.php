<?php

namespace Spiral\Snapshotter\Helpers;

class Names
{
    public function onlyName($name)
    {
        $pos = mb_strripos($name, '\\');

        if ($pos !== false) {
            return mb_substr($name, $pos + 1);
        }

        return $name;
    }

    public function filename($name)
    {

    }
}