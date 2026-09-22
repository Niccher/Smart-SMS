<?php

namespace App\Database\Seeds;

use CodeIgniter\Shield\Models\UserModel;
use Faker\Factory;

class SuperAdminSeeder extends \CodeIgniter\Database\Seeder
{
    public function run()
    {
        $email    = env('SUPERADMIN_EMAIL', 'superadmin@analyzer.com');
        $password = env('SUPERADMIN_PASSWORD', 'SuperAdmin@2024!');
        $username = env('SUPERADMIN_USERNAME', 'superadmin');

        /** @var UserModel $users */
        $users = auth()->getProvider();

        $existing = $users->where('username', $username)->first();
        if ($existing !== null) {
            echo "Superadmin '{$username}' already exists. Skipping." . PHP_EOL;

            return;
        }

        $user = new \CodeIgniter\Shield\Entities\User([
            'username' => $username,
            'active'   => true,
        ]);

        $user->email    = $email;
        $user->password = $password;

        $users->save($user);

        $user = $users->findById($users->getInsertID());
        $user->addGroup('superadmin');

        echo "Superadmin '{$username}' created with group 'superadmin'." . PHP_EOL;
    }
}
