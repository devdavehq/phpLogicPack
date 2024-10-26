<?php 

class Route
{
    private $routeId;

    public function __construct($routeId)
    {
        $this->routeId = $routeId;
    }

    public function name($name)
    {
        Router::$namedRoutes[$name] = $this->routeId;
        return $this;
    }
}