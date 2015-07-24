<?php
/**
 * Realejo Lib Unit Test Bootstrap
 *
 * @category  TestUnit
 * @author    Realejo
 * @copyright Copyright (c) 2013 Realejo (http://realejo.com.br)
 */
error_reporting(E_ALL | E_STRICT);

define('APPLICATION_ENV', 'testing');
define('TEST_ROOT', __DIR__);
define('APPLICATION_DATA', TEST_ROOT . '/_files/data');

/**
 * Setup autoloading
 */
require __DIR__ . '/../vendor/autoload.php';
