<?php

namespace ConfidencesTest\Util;

use Confidences\Util;
use ConfidencesTest\TestCase;

class UtilTest extends TestCase
{
    public function testUtf8()
    {
        // UTF-8 string
        $x = "\xc3\xa9";
        $this->assertSame(Util\Util::utf8($x), $x);

        // Latin-1 string
        $x = "\xe9";
        $this->assertSame(Util\Util::utf8($x), "\xc3\xa9");

        // Not a string
        $x = true;
        $this->assertSame(Util\Util::utf8($x), $x);
    }
}
