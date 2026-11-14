<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(User::class, 'users', function (array $row) {
            $row['password'] = Hash::make($row['password']);

            return $row;
        });
    }
}
