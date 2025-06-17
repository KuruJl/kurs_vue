<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  

public function run()
{
    User::where('id', 3)->update(['is_admin' => false]);
    echo "Пользователи администраторами\n";
}
}
