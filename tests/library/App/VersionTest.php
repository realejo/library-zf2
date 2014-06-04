<?php
/**
 * Version test case.
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

