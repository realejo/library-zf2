<?php

namespace RealejoTest\App;

/**
 * Version test case.
 */

use PHPUnit\Framework\TestCase;
use Realejo\App\Version;

class VersionTest extends TestCase
{
    public function testGetLatest():void
    {
        $this->assertNotEmpty(Version::getLatest());
    }

    public function testCompareVersion():void
    {
        $this->assertEquals(0, Version::compareVersion(Version::VERSION));
        $this->assertContains(Version::compareVersion(Version::getLatest()), array(-1, 0, 1));
    }
}
