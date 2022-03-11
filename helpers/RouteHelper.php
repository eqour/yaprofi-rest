<?php

namespace app\helpers;

class RouteHelper
{
    public function parse(string $route): array
    {
        $questionPos = strpos($route, '?');

        if ($questionPos !== false) {
            $route = substr($route, 0, $questionPos);
        }

        $paths = explode('/', $route);
        $newPaths = [];
        foreach ($paths as $path) {
            if ($path !== '') {
                $newPaths[] = $path;
            }
        }

        return $newPaths;
    }
}
