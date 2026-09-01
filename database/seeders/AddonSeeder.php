<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            ['name' => 'Birthday Cap', 'price' => 69],
            ['name' => 'Greeting Card', 'price' => 49],
            ['name' => 'Teddy Bear', 'price' => 299],
            ['name' => 'Chocolate Box', 'price' => 199],
            ['name' => 'Flowers Bouquet', 'price' => 349],
            ['name' => 'Candles Pack', 'price' => 59],
            ['name' => 'Party Balloons', 'price' => 99],
        ];

        foreach ($addons as $a) {
            Addon::firstOrCreate(
                ['slug' => Str::slug($a['name'])],
                ['name' => $a['name'], 'price' => $a['price'], 'stock' => 100, 'is_active' => true]
            );
        }
    }
}