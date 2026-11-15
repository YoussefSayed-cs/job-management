<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Shagalni\Shared\Models\User;

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
