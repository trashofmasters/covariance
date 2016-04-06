<?php

namespace Ofc\Covariance\Tests;

use Ofc\Covariance\CovariantCall;
use PHPUnit_Framework_TestCase as TestCase;

class CovariantCallTest extends TestCase
{
    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessageRegExp /.+CovariantCallTest to covariant method.+CovariantCallTest::mth/
     */
    public function testCallToUndefinedMethodWithNoHandlerThrows()
    {
        $call = new CovariantCall('mth');
        $call->setCallSubject($this);
        $call->execute($this);
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessageRegExp /.+Boy to covariant method.+Girl::share/
     */
    public function test()
    {
        $girl = new Girl();
        $boy  = new Boy();

        $call = new CovariantCall('share');
        $call->setCallSubject($girl);
        $call->execute($boy);
    }

    public function testTypeSpecificClosureCall()
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

    public function testTypeSpecificClosureCallWithArgs()
    {
        $testCaseHandler = function (TestCase $test, $a, $b, $c) {
            $this->assertSame($this, $test);
            return $a + $b + $c;
        };

        $call = new CovariantCall('mth');

        $call->addHandler(
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

    public function testLookupHandlerInSubjectClassLineageReturnsCallable()
    {
        $call = new CovariantCall('share');

        // We're calling Girl::share(Girl), so we expect
        // this call to resolve to the method: Girl::shareGirl(Girl).
        $subject = new Girl;
        $this->assertEquals(
            [$subject, 'shareGirl'],
            $call->findHandlerLineage(
                $subject,
                'share',
                Girl::class
            )
        );

        // We're calling Boy::share(Boy), so we expect
        // this call to resolve to the method: Boy::shareBoy(Boy).
        $subject = new Boy;
        $this->assertEquals(
            [$subject, 'shareBoy'],
            $call->findHandlerLineage(
                $subject,
                'share',
                Boy::class
            )
        );
    }

    public function testFailedLineageLookupReturnsFalse()
    {
        $call = new CovariantCall('share');

        $girl = new Girl;
        $this->assertFalse(
            $call->findHandlerLineage(
                $girl,
                'share',
                Boy::class
            )
        );
    }

    public function testCovariantBaseBodyCalledIfNoHandlerFound()
    {
        $call = new CovariantCall('share');

        $girl = new Girl;
        $boy = new Boy;

        $call->setMethodBody(function (Skier $skier) {
            // Make sure the argument's type is respected.
            $this->assertInstanceOf(Boy::class, $skier);
            $this->assertInstanceOf(Skier::class, $skier);

            return 'called';
        });

        $this->assertEquals(
            'shareBoy',
            $call->methodName('share', 'Boy')
        );

        $call->setCallSubject($girl);

        $this->assertEquals(
            'called',
            $call->execute($boy)
        );
    }

    // TODO test calling a covariant method without arguments
    // throw. Whilst return type covariance only works when the return
    // type isn't type hinted, we're really only concerned with the
    // parameter's type, so it's important it's there.
    //
    // e.g. (new CovariantCall())->execute();

    public function testNonExistingMethod()
    {
        // TODO test declaration of covariant method on a method that
        // doesn't exists (as a poka yoke, just in case somebody uses the
        // object directly.)
        //
        // e.g. (new CovariantCall('someMethodThatIsNotDefined'))->execute($type).

        $this->markTestIncomplete();

        $call = new CovariantCall('nonExistentMethod');
        $call->setCallSubject($this);

        $girl = new Girl();
        $call->execute($girl);
    }
}

class Skier {
    function share(Skier $s) {
        // Faking the covariant method.
    }
}

class Girl extends Skier {
    function shareGirl(Girl $g) {}
}

class Boy extends Skier {
    function shareBoy(Boy $b) {}
}
