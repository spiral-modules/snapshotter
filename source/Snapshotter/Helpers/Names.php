<?php

namespace Spiral\Snapshotter\Helpers;

class Names
{
    /**
     * @param string $name
     * @return string
     */
    public function onlyName(string $name): string
    {
        $name = str_replace('/', '\\', $name);

        $pos = mb_strripos($name, '\\');

        if ($pos !== false) {
            return mb_substr($name, $pos + 1);
        }

        return $name;
    }
}