<?php
/**
 * Realejo Library ZF2
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
namespace Realejo;

use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

define('APPLICATION_ENV', 'testing');
define('MYSQL_IP', (isset($_SERVER['COMPUTERNAME']) && $_SERVER['COMPUTERNAME'] == 'RODRIGO') ? '192.168.2.23' : '192.168.100.25');


/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        $path = static::findParentPath('vendor');
        if ($path) {
            $zf2ModulePaths[] = $path;
        }
        static::initAutoloader();
    }

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath('module'));
        chdir($rootPath);
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        $zf2Path = getenv('ZF2_PATH');
        if (!$zf2Path) {
            if (defined('ZF2_PATH')) {
                $zf2Path = ZF2_PATH;
            } elseif (is_dir($vendorPath . '/ZF2/library')) {
                $zf2Path = $vendorPath . '/ZF2/library';
            } elseif (is_dir($vendorPath . '/zendframework/zendframework/library')) {
                $zf2Path = $vendorPath . '/zendframework/zendframework/library';
            }
        }

        if (!$zf2Path) {
            throw new RuntimeException(
                    'Unable to load ZF2. Run `php composer.phar install` or'
                    . ' define a ZF2_PATH environment variable.'
            );
        }

        $libraryPath = realpath($vendorPath .'/../library');
        if (file_exists($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
            $loader->setUseIncludePath($libraryPath . '/Realejo');
        }
        set_include_path(implode(PATH_SEPARATOR, array($libraryPath, get_include_path())));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();
//Bootstrap::chroot();
/*  ---

$root = dirname(__DIR__);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath($root . '/library'),
    get_include_path()
)));
unset($root); */