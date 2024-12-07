<?php

namespace App\API;

class Route
{
    public $handler;
    public $middleware = false;
    public $requiredRole = null;
    public ?array $params = null;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function middleware(bool $middleware = false): self
    {
        $this->middleware = $middleware;
        return $this;
    }

    public function requiredRole(string $role = null): self
    {
        $this->requiredRole = $role;
        return $this;
    }
}
