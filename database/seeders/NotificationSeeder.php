<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        // مفيش model للـ notifications فبنستخدم DB مباشرة
        foreach ($this->readJson('notifications') as $row) {
            $row['data'] = json_encode($row['data']);
            $row['created_at'] = now();
            $row['updated_at'] = now();

            DB::table('notifications')->insert($row);
        }
    }
}
