<?php
/**
 * Version test case.
 *
 * @link      http://github.com/realejo/libraray-zf2
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   http://unlicense.org
 */
use Realejo\App\Version;

class VersionTest extends PHPUnit_Framework_TestCase
{
    public function testGetLatest()
    {
        $this->assertNotEmpty(Version::getLatest());
    }

    public function testCompareVersion()
    {
        $this->assertEquals(0, Version::compareVersion(Version::VERSION));
        $this->assertContains(Version::compareVersion(Version::getLatest()), array(-1,0,1));
    }
}

