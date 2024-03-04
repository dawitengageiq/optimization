<?php

use App\User;

class AdminVisitPagesTest extends BrowserKitTestCase
{
    public function testAdminLogin(): void
    {
        $this->visit('/auth/login')
            ->type('ariel@engageiq.com', 'email')
            ->type('12345', 'password')
            ->press('Login')
            ->seePageIs('/admin/dashboard');
    }

    public function testVisitPages(): void
    {
        //get the admin user depends on your record
        $user = User::firstOrCreate([
            'email' => 'ariel@engageiq.com',
        ]);

        $this->actingAs($user)
            ->visit('/admin/home')
            ->see('User: '.$user->first_name);

        $this->visit('/admin')
            ->see('User: '.$user->first_name);
    }
}
