# Encount plugin for CakePHP5.x.

## Requirements

- PHP >= 8.1.*
- CakePHP >= 5.*

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require fusic/encount
```

## Usage

```php
// config/bootstrap.php
<?php

use Encount\Error\EncountErrorTrap;
use Encount\Error\EncountExceptionTrap;

/*
 * Register application error and exception handlers.
 */
// (new ErrorTrap(Configure::read('Error')))->register();
// (new ExceptionTrap(Configure::read('Error')))->register();
(new EncountErrorTrap(Configure::read('Error')))->register();
(new EncountExceptionTrap(Configure::read('Error')))->register();
```

```php
// src/Application.php
<?php

use Encount\Middleware\EncountErrorHandlerMiddleware;

$middleware
    // ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))
    ->add(new EncountErrorHandlerMiddleware(Configure::read('Error'), $this))
```

## Config

```php
// config/app.php
<?php

return [

-snip-

    'Error' => [
        'errorLevel' => E_ALL & ~E_DEPRECATED,
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        'encount' => [
            'force' => false,
            'sender' => [
                'Encount.Mail',
            ],
            // ignore ex)
            // 'deny' => [
            //     'exception' => [
            //         '\Cake\Http\Exception\MissingControllerException', // 404
            //         '\Cake\Http\Exception\MethodNotAllowedException', // 404
            //         '\Cake\Http\Exception\ForbiddenException', // isAuthorized
            //         '\Cake\Controller\Exception\MissingActionException', // 404
            //         '\Cake\Datasource\Exception\RecordNotFoundException', // notFoundRecored
            //     ],
            // ],
        ],
    ],

-snip-

    'EmailTransport' => [
        'default' => [
        ],
        // Encount Email config
        'encount' => [
            'className' => SmtpTransport::class,
            'port' => xxx,
            'timeout' => xx,
            'host' => 'xxxxxxxxxxxxxxxxx',
            'username' => 'xxxxxxxx@example.com',
            'password' => 'xxxxxxxx',
            'log' => true,
            'tls' => true,
        ],
    ],

    'Email' => [
        'default' => [
        ],
        // Encount Email config
        'error' => [
            'transport' => 'encount',
            'from' => 'from@example.com',
            'to' => 'to@example.com',
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
    ],

-snip-

];
```

## Sender

### Encount.Mail
### [Encount sender for faultline](https://github.com/fusic/encount-sender-faultline)
