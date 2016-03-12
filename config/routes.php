<?php
use Cake\Routing\Router;

Router::plugin('Encount', function ($routes) {
    $routes->fallbacks('DashedRoute');
});
