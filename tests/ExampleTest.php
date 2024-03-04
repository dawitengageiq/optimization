<?php

class ExampleTest extends BrowserKitTestCase
{
    /**
     * A basic functional test example.
     */
    public function testBasicExample(): void
    {
        $this->visit('auth/login')
            ->type('ariel@engageiq.com', 'email')
            ->type('12345', 'password')
            ->press('Login')
            ->seePageIs('admin/dashboard');
    }
}
