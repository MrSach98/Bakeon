<?php

namespace Database\Seeders;

use App\Models\DeliveryOption;
use App\Models\Flavor;
use App\Models\Occasion;
use App\Models\Weight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CakeMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $flavors = [
            'Chocolate', 'Vanilla', 'Red Velvet', 'Black Forest', 'Butterscotch',
            'Coffee', 'Strawberry', 'Pineapple', 'Mango', 'Blueberry', 'Fruit',
            'Nutella Hazelnut', 'Belgian Chocolate', 'Tiramisu', 'Ferrero Rocher',
            'Kit-Kat', 'Oreo', 'Rasmalai', 'Pista', 'Jeera', 'Almond', 'Coconut', 'Multigrain',
        ];
        foreach ($flavors as $name) {
            Flavor::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true]);
        }

        $weights = [
            ['label' => '0.5 Kg', 'value_kg' => 0.5, 'serves' => 4],
            ['label' => '1 Kg', 'value_kg' => 1, 'serves' => 8],
            ['label' => '1.5 Kg', 'value_kg' => 1.5, 'serves' => 12],
            ['label' => '2 Kg', 'value_kg' => 2, 'serves' => 16],
            ['label' => '3 Kg', 'value_kg' => 3, 'serves' => 24],
            ['label' => '4 Kg', 'value_kg' => 4, 'serves' => 32],
            ['label' => '5 Kg', 'value_kg' => 5, 'serves' => 40],
        ];
        foreach ($weights as $i => $w) {
            Weight::firstOrCreate(['label' => $w['label']], $w + ['sort_order' => $i, 'is_active' => true]);
        }

        $occasions = [
            'Birthday', 'Anniversary', "Valentine's Day", 'Wedding', 'Engagement',
            'Farewell', 'Retirement', 'Baby Shower', "Mother's Day", "Father's Day",
            "Women's Day", 'Housewarming', 'Congratulations',
        ];
        foreach ($occasions as $name) {
            Occasion::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true]);
        }

        $deliveryOptions = [
            ['name' => 'Standard Delivery', 'slug' => 'standard', 'extra_charge' => 0],
            ['name' => 'Same Day Delivery', 'slug' => 'same-day', 'order_cutoff_time' => '18:00:00', 'extra_charge' => 0],
            ['name' => 'Midnight Delivery', 'slug' => 'midnight', 'order_cutoff_time' => '20:00:00', 'delivery_window_start' => '00:00:00', 'delivery_window_end' => '01:00:00', 'extra_charge' => 149],
            ['name' => 'Fixed Time Delivery', 'slug' => 'fixed-time', 'extra_charge' => 99],
            ['name' => 'Express Delivery (2-4 hrs)', 'slug' => 'express', 'extra_charge' => 199],
        ];
        foreach ($deliveryOptions as $d) {
            DeliveryOption::firstOrCreate(['slug' => $d['slug']], $d + ['is_active' => true]);
        }
    }
}