# Encount plugin for CakePHP

[![Code Quality](http://img.shields.io/scrutinizer/g/fusic/encount.svg?style=flat-square)](https://scrutinizer-ci.com/g/fusic/encount/)

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

use Encount\Error\ErrorHandler;

(new ErrorHandler(Configure::read('Error')))->register();
```

## Config

```php
// config/app.php
<?php

return [

-snip-

    'Error' => [
        'errorLevel' => E_ALL & ~E_DEPRECATED,
        'exceptionRenderer' => 'Cake\Error\ExceptionRenderer',
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        # Encount config
        'encount' => [
            'force' => false,
            'sender' => ['Encount.Mail'],
            'mail' => [
                'prefix' => '',
                'html' => true
            ]
        ],
    ],

-snip-

];
```

## Sender

### Encount.Mail
