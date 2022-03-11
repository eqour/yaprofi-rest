<?php

namespace app;

use app\helpers\RequireHelper;
use app\helpers\RouteHelper;
use PDO;
use Throwable;

class Application
{
    public static array $route;
    public static string $root;
    public static PDO $db;
    private array $config;

    public function __construct(array $config = [])
    {
        try {
            $this->config = $config;
            $this->init();
        } catch (Throwable $ex) {
            http_response_code(500);
            return;
        }
    }

    private function init(): void
    {
        self::$root = $this->config['app']['root'];
        self::$db = new PDO($this->config['db']['dsn'], $this->config['db']['username'], $this->config['db']['password']);
        require self::$root . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'RequireHelper.php';
        (new RequireHelper())->start(require $this->config['app']['require_list']);
    }

    public function run(): void
    {
        try {
            self::$route = (new RouteHelper())->parse($_SERVER['REQUEST_URI']);
            $controllerMethod = 'action' . ucfirst(strtolower($_SERVER['REQUEST_METHOD']));
    
            if (isset(self::$route)
                && count(self::$route) >= 1
                && count(self::$route) <= 4
                && isset(self::$route[0])
                && class_exists($controllerName = 'app\controllers\\' . ucfirst(self::$route[0]) . 'Controller')
                && method_exists($controllerName, $controllerMethod)) {
                $controller = new $controllerName();
                $response = $controller->$controllerMethod();
                if ($response != null) {
                    echo json_encode($response);
                }
            } else {
                http_response_code(404);
            }
        } catch (Throwable $ex) {
            http_response_code(500);
            return;
        }
    }

    public static function get(): array
    {
        return $_GET;
    }

    public static function post(): array
    {
        return $_POST;
    }

    public static function jsonDataFromBody(): array
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}
