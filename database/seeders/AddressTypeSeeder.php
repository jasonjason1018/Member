<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AddressType;

class AddressTypeSeeder extends Seeder
{
    public function run()
    {
        // 添加"Residential Address"類型
        AddressType::create([
            'name' => 'Residential Address',
            'is_active' => true
        ]);

        // 添加"Correspondence Address"類型
        AddressType::create([
            'name' => 'Correspondence Address',
            'is_active' => true
        ]);
    }
}
