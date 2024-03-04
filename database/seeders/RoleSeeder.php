<?php

namespace Database\Seeders;

use App\Action;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //this is a super user account it is by default has all permissions
        $superUser = Role::firstOrNew([
            'name' => 'Super user',
        ]);

        $superUser->description = 'A user that has all permissions and access. Take note this role should not be edited or deleted.';

        $superUser->save();

        //enable all permissions for the super user
        $actions = Action::all();
        foreach ($actions as $action) {
            $superUser->actions()->attach($action->id, ['permitted' => true]);
        }

        $superUser->save();

        //apply this user to all admin accounts
        $admins = User::where('account_type', '=', 2)->get();

        foreach ($admins as $admin) {
            $admin->role_id = $superUser->id;
            $admin->save();
        }

        $campaignEditor = Role::firstOrNew([
            'name' => 'Campaign Editor',
        ]);

        $campaignEditor->description = 'Can only edit campaigns';

        /*
        $campaignEditor->actions()->attach(4,['permitted' => true]);
        $campaignEditor->actions()->attach(22,['permitted' => true]);
        $campaignEditor->actions()->attach(23,['permitted' => true]);
        $campaignEditor->actions()->attach(24,['permitted' => true]);
        $campaignEditor->actions()->attach(25,['permitted' => true]);
        $campaignEditor->actions()->attach(26,['permitted' => true]);
        $campaignEditor->actions()->attach(27,['permitted' => true]);
        $campaignEditor->actions()->attach(28,['permitted' => true]);
        $campaignEditor->actions()->attach(29,['permitted' => true]);
        $campaignEditor->actions()->attach(30,['permitted' => true]);
        $campaignEditor->actions()->attach(31,['permitted' => true]);
        $campaignEditor->actions()->attach(32,['permitted' => true]);
        */

        $campaignEditor->save();
    }
}
