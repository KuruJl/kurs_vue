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
    User::where('id', 1)->update(['is_admin' => true]);
    echo "Пользователи назначены администраторами\n";
}
}
