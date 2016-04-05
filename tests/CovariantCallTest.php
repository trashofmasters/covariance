<?php

namespace Ofc\Covariance\Tests;

use Ofc\Covariance\CovariantCall;
use PHPUnit_Framework_TestCase as TestCase;

class CovariantCallTest extends TestCase
{
    public function testUsesQualifiedParameterTypes()
    {
        $call = new CovariantCall('mth');

        $this->assertContains(
            __NAMESPACE__,
            get_class($this),
            "Parameter name relies on get_class() to return a qualified name."
        );
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessageRegExp /.+CovariantCallTest to covariant method.+CovariantCallTest::mth/
     */
    public function testOneMethodHandler()
    {
        $call = new CovariantCall('mth');
        $call->setCallSubject($this);
        $call->execute($this);
    }

    public function testOneClosureHandler()
    {
        $testCaseHandler = function (TestCase $test) {
            $this->assertSame($this, $test);
            return true;
        };

        $call = new CovariantCall('mth');

        $call->addHandler(
            // declare one handler for TestCase
            get_class($this),
            $testCaseHandler
        );

        $this->assertSame(
            $testCaseHandler,
            $call->getHandlerForType($this)
        );

        $this->assertTrue(
            $call->execute($this)
        );
    }

    public function testOneClosureHandlerWithArgs()
    {
        $testCaseHandler = function (TestCase $test, $a, $b, $c) {
            $this->assertSame($this, $test);
            return $a + $b + $c;
        };

        $call = new CovariantCall('mth');

        $call->addHandler(
            // declare one handler for TestCase
            get_class($this),
            $testCaseHandler
        );

        $this->assertSame(
            $testCaseHandler,
            $call->getHandlerForType($this)
        );

        $a = 1;
        $b = 2;
        $c = 3;
        $this->assertEquals(
            $a + $b + $c,
            $call->execute($this, $a, $b, $c)
        );
    }

    public function testLineageHandler()
    {
        $call = new CovariantCall('share');

        $girl = new Girl;
        $this->assertEquals(
            [$girl, 'shareGirl'],
            $call->findHandlerLineage(
                $girl,
                'share',
                Girl::class
            )
        );

        $boy = new Boy;
        $this->assertEquals(
            [$boy, 'shareBoy'],
            $call->findHandlerLineage(
                $boy,
                'share',
                Boy::class
            )
        );
    }

    public function testLineageHandler2()
    {
        $call = new CovariantCall('share');

        $girl = new Girl;
        $this->assertEmpty(
            $call->findHandlerLineage(
                $girl,
                'share',
                Boy::class
            )
        );
    }

    public function testCovariantMethodDefaultBody()
    {
        $call = new CovariantCall('mth');
        $call->setMethodBody(function () {
            return 'called';
        });
        $call->setCallSubject($this);
        $this->assertEquals(
            'called',
            $call->execute($this)
        );
    }
}

class Skier {
    function share(Skier $s) {
        // The covariant method.
    }
}

class Girl extends Skier {
    function shareGirl(Girl $g) {}
}

class Boy extends Skier {
    function shareBoy(Boy $b) {}
}
