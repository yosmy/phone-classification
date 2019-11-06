<?php

namespace Yosmy\Test;

use Yosmy;
use PHPUnit\Framework\TestCase;

class ClassificationTest extends TestCase
{
    public function testGetters()
    {
        $voip = true;

        $lookup = new Yosmy\Phone\Classification(
            $voip
        );

        $this->assertEquals(
            $voip,
            $lookup->isVoip()
        );
    }
}