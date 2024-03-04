<?php

class CheckJeremyClassTest extends BrowserKitTestCase
{
    public function testFailure(): void
    {
        // $this->assertClassHasAttribute('setData', 'App\Http\Services\Charts');
        $this->assertTrue(
            method_exists('App\Http\Services\Charts', 'setData'),
            'Class does not have method myFunction'
        );
    }
    //this test passed

    public function testConcreteMethod(): void
    {
        $stub = $this->getMockForAbstractClass('App\Http\Services\Factories\ChartFactory');

        $actual_rejection = [
            'high' => [],
            'critical' => [],
        ];
        $stub->expects($this->any())
            ->method('abstractMethod')
            ->will($this->returnValue(true));

        $this->assertTrue($stub->concreteMethod());

        //this test fails
    }
}
