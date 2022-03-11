<?php

namespace app\helpers;

use app\Application;

class RequireHelper
{
    public function start($paths): void
    {
        foreach ($paths as $path) {
            require_once Application::$root . $path;
        }
    }
}
