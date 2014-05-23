<?php
/**
 * CacheTest test case.
 *
 * @author     Realejo
 * @copyright  Copyright (c) 2014 Realejo Design Ltda. (http://www.realejo.com.br)
 */
use Realejo\App\Model\Cache, Zend\Db\Adapter\Adapter;

/**
 * Base test case.
 */
class CacheTest extends PHPUnit_Framework_TestCase
{

    protected $dataPath = '/../../../assets/data';

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // Verifica se a pasta do cache existe
        if (file_exists($this->dataPath)) {
            $this->rrmdir($this->dataPath);
        }
        $oldumask = umask(0);
        mkdir($this->dataPath, 0777, true);
        umask($oldumask);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->rrmdir($this->dataPath);
    }

    /**
     * setAPPLICATION_DATA define o APPLICATION_DATA se não existir
     *
     * @return string
     */
    public function setAPPLICATION_DATA()
    {
        // Verifica se a pasta de cache existe
        if (defined('APPLICATION_DATA') === false) {
            define('APPLICATION_DATA', $this->dataPath);
        }
    }

    /**
     * Remove os arquivos da pasta e a pasta
     *
     * @return string
     */
    public function rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file)) {
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    /**
     * getCachePath sem nome da pasta
     *
     * @expectedException Exception
     */
    public function testGetCacheRootSemAPPLICATION_DATA()
    {
        Cache::getCacheRoot();
    }

    /**
     * getCachePath sem nome da pasta
     */
    public function testGetCacheRoot()
    {
        $this->setAPPLICATION_DATA();

        $path = Cache::getCacheRoot();

        $this->assertNotNull($path);
        $this->assertEquals(realpath(APPLICATION_DATA.'/cache'), $path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));
    }

    /**
     * getCachePath sem nome da pasta
     */
    public function testGetCachePath()
    {
        $this->assertEquals(Cache::getCacheRoot(), Cache::getCachePath(null));
        $this->assertEquals(Cache::getCacheRoot(), Cache::getCachePath(''));
        $this->assertEquals(Cache::getCacheRoot(), Cache::getCachePath());

        $path = Cache::getCachePath('Album');

        $this->assertNotNull($path);
        $this->assertEquals(realpath(APPLICATION_DATA.'/cache/album'), $path);
        $this->assertNotEquals(realpath(APPLICATION_DATA.'/cache/Album'), $path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));

        $this->rrmdir($path);

        $this->assertFalse(file_exists($path));

        $path = Cache::getCachePath('album');

        $this->assertNotNull($path);
        $this->assertEquals(realpath(APPLICATION_DATA.'/cache/album'), $path);
        $this->assertNotEquals(realpath(APPLICATION_DATA.'/cache/Album'), $path);
        $this->assertTrue(file_exists($path), 'Verifica se a pasta album existe');
        $this->assertTrue(is_dir($path), 'Verifica se a pasta album é uma pasta');
        $this->assertTrue(is_writable($path), 'Verifica se a pasta album tem permissão de escrita');

        $this->rrmdir($path);

        $this->assertFalse(file_exists($path));

        $path = Cache::getCachePath('album_Teste');

        $this->assertNotNull($path);
        $this->assertEquals(realpath(APPLICATION_DATA.'/cache/album/teste'), $path);
        $this->assertNotEquals(realpath(APPLICATION_DATA.'/cache/Album/Teste'), $path);
        $this->assertTrue(file_exists($path), 'Verifica se a pasta album_Teste existe');
        $this->assertTrue(is_dir($path), 'Verifica se a pasta album_Teste é uma pasta');
        $this->assertTrue(is_writable($path), 'Verifica se a pasta album_Teste tem permissão de escrita');

        $this->rrmdir($path);

        $this->assertFalse(file_exists($path), 'Verifica se a pasta album_Teste foi apagada');

        $path = Cache::getCachePath('album/Teste');

        $this->assertNotNull($path, 'Teste se o album/Teste foi criado');
        $this->assertEquals(realpath(APPLICATION_DATA.'/cache/album/teste'), $path);
        $this->assertNotEquals(realpath(APPLICATION_DATA.'/cache/Album/Teste'), $path);
        $this->assertTrue(file_exists($path), 'Verifica se a pasta album/Teste existe');
        $this->assertTrue(is_dir($path), 'Verifica se a pasta album/Teste é uma pasta');
        $this->assertTrue(is_writable($path), 'Verifica se a pasta album/Teste tem permissão de escrita');

    }

    /**
     * getFrontend sem nome da class
     */
    public function testgetFrontendSemClass()
    {
        $this->markTestIncomplete('Ainda nao fiz');
        Cache::getFrontend(null);
    }

    /**
     * getFrontend com nome da class
     */
    public function testgetFrontendComClass()
    {
        $this->markTestIncomplete('Ainda nao fiz');
        Cache::getFrontend('Album');
    }

}

