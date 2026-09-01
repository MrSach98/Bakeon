<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Category;
use App\Models\DeliveryOption;
use App\Models\Flavor;
use App\Models\Occasion;
use App\Models\Product;
use App\Models\ProductWeight;
use App\Models\Weight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $flavors = Flavor::all()->keyBy(fn ($f) => Str::slug($f->name));
        $weights = Weight::orderBy('sort_order')->get();
        $addons = Addon::all();
        $occasions = Occasion::all()->keyBy(fn ($o) => Str::slug($o->name));
        $deliveryOptions = DeliveryOption::all();

        if ($weights->isEmpty()) {
            $this->command->warn('Run CakeMasterDataSeeder first — weights are empty.');
            return;
        }

        // Helper to resolve category / subcategory / child by name path
        $findCategory = function (string $categoryName, ?string $subName = null, ?string $childName = null) {
            $category = Category::whereNull('parent_id')->where('name', $categoryName)->first();
            if (! $category) {
                return [null, null, null];
            }

            $subcategory = null;
            $child = null;

            if ($subName) {
                $subcategory = Category::where('parent_id', $category->id)->where('name', $subName)->first();

                if ($subcategory && $childName) {
                    $child = Category::where('parent_id', $subcategory->id)->where('name', $childName)->first();
                }
            }

            return [$category, $subcategory, $child];
        };

        $products = [
            ['name' => 'Choco Truffle Delight Cake', 'path' => ['Birthday Cakes', 'Kids Birthday', null], 'flavors' => ['chocolate'], 'occasions' => ['birthday'], 'price' => 549, 'egg_type' => 'both'],
            ['name' => 'Cartoon Theme Birthday Cake', 'path' => ['Birthday Cakes', 'Kids Birthday', 'Cartoon Theme'], 'flavors' => ['vanilla'], 'occasions' => ['birthday'], 'price' => 649, 'egg_type' => 'eggless'],
            ['name' => 'Superhero Squad Cake', 'path' => ['Birthday Cakes', 'Kids Birthday', 'Superhero Theme'], 'flavors' => ['chocolate'], 'occasions' => ['birthday'], 'price' => 699, 'egg_type' => 'eggless'],
            ['name' => 'Red Velvet Symphony Cake', 'path' => ['Anniversary Cakes', 'Wedding Anniversary', null], 'flavors' => ['red-velvet'], 'occasions' => ['anniversary'], 'price' => 649, 'egg_type' => 'both'],
            ['name' => 'Silver Jubilee 25th Anniversary Cake', 'path' => ['Anniversary Cakes', 'Wedding Anniversary', '25th Anniversary'], 'flavors' => ['vanilla'], 'occasions' => ['anniversary'], 'price' => 899, 'egg_type' => 'both'],
            ['name' => 'Golden 50th Anniversary Cake', 'path' => ['Anniversary Cakes', 'Wedding Anniversary', '50th Anniversary'], 'flavors' => ['butterscotch'], 'occasions' => ['anniversary'], 'price' => 999, 'egg_type' => 'both'],
            ['name' => 'Black Forest Classic Cake', 'path' => ['Birthday Cakes', 'Adult Birthday', null], 'flavors' => ['black-forest'], 'occasions' => ['birthday'], 'price' => 499, 'egg_type' => 'eggless'],
            ['name' => 'Butterscotch Bliss Cake', 'path' => ['Birthday Cakes', 'Adult Birthday', null], 'flavors' => ['butterscotch'], 'occasions' => ['birthday'], 'price' => 499, 'egg_type' => 'both'],
            ['name' => 'Little One First Birthday Cake', 'path' => ['Birthday Cakes', 'First Birthday', null], 'flavors' => ['vanilla'], 'occasions' => ['birthday'], 'price' => 549, 'egg_type' => 'eggless'],
            ['name' => 'Heart Shaped Red Velvet Cake', 'path' => ["Valentine's Day Cakes", 'Heart Shape Cakes', null], 'flavors' => ['red-velvet'], 'occasions' => ['valentines-day'], 'price' => 699, 'egg_type' => 'eggless'],
            ['name' => 'Love Struck Strawberry Cake', 'path' => ["Valentine's Day Cakes", 'Romantic Cakes', null], 'flavors' => ['strawberry'], 'occasions' => ['valentines-day'], 'price' => 599, 'egg_type' => 'eggless'],
            ['name' => 'Belgian Chocolate Fondant Gateau', 'path' => ['Designer Cakes', 'Fondant Cakes', null], 'flavors' => ['belgian-chocolate'], 'occasions' => ['birthday'], 'price' => 799, 'egg_type' => 'both'],
            ['name' => 'Nutella Hazelnut Theme Cake', 'path' => ['Designer Cakes', 'Theme Cakes', null], 'flavors' => ['nutella-hazelnut'], 'occasions' => ['birthday'], 'price' => 849, 'egg_type' => 'eggless'],
            ['name' => "Sweet Surprise for Mom Bento Cake", 'path' => ['Bento Cakes', 'Relationship', 'Mom'], 'flavors' => ['chocolate'], 'occasions' => ["mothers-day"], 'price' => 399, 'egg_type' => 'eggless'],
            ['name' => 'Coffee Bento for Dad', 'path' => ['Bento Cakes', 'Relationship', 'Dad'], 'flavors' => ['coffee'], 'occasions' => ["fathers-day"], 'price' => 399, 'egg_type' => 'eggless'],
            ['name' => 'Romantic Bento for Wife', 'path' => ['Bento Cakes', 'Relationship', 'Wife'], 'flavors' => ['strawberry'], 'occasions' => ['anniversary'], 'price' => 429, 'egg_type' => 'eggless'],
            ['name' => 'Personalized Photo Birthday Cake', 'path' => ['Photo Cakes', null, null], 'flavors' => ['chocolate', 'vanilla'], 'occasions' => ['birthday'], 'price' => 599, 'egg_type' => 'eggless', 'photo_cake' => true],
            ['name' => 'Three Tier Wedding Elegance Cake', 'path' => ['Wedding Cakes', 'Tiered Cakes', null], 'flavors' => ['vanilla', 'chocolate'], 'occasions' => ['wedding'], 'price' => 1499, 'egg_type' => 'both'],
            ['name' => 'Jeera Butter Cookies Jar', 'path' => ['Cookies', 'Assorted Cookie Jars', null], 'flavors' => ['jeera'], 'occasions' => [], 'price' => 249, 'egg_type' => 'eggless'],
            ['name' => 'Choco Chip Classic Cookies', 'path' => ['Cookies', 'Chocolate Chip Cookies', null], 'flavors' => ['chocolate'], 'occasions' => [], 'price' => 199, 'egg_type' => 'eggless'],
        ];

        foreach ($products as $index => $data) {
            [$category, $subcategory, $child] = $findCategory(...$data['path']);

            if (! $category) {
                $this->command->warn("Skipped '{$data['name']}' — category '{$data['path'][0]}' not found. Run CategorySeeder first.");
                continue;
            }

            $product = Product::updateOrCreate(
                ['sku' => 'CAKE-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory?->id,
                    'child_category_id' => $child?->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) . '-' . ($index + 1),
                    'short_description' => "A delicious {$data['name']} freshly baked with premium ingredients.",
                    'description' => "Celebrate every moment with our {$data['name']}. Made fresh with high-quality ingredients and delivered with care.",
                    'base_price' => $data['price'],
                    'discount_price' => $data['price'] > 600 ? $data['price'] - 50 : null,
                    'egg_type' => $data['egg_type'],
                    'is_photo_cake' => $data['photo_cake'] ?? false,
                    'is_message_enabled' => true,
                    'message_char_limit' => 30,
                    'meta_title' => $data['name'] . ' | Order Online | Sweet Bakes',
                    'meta_description' => "Order {$data['name']} online with same day and midnight delivery.",
                    'meta_keywords' => strtolower(str_replace(' ', ', ', $data['name'])) . ', online cake delivery',
                    'is_featured' => $index % 4 === 0,
                    'is_bestseller' => $index % 3 === 0,
                    'status' => 'active',
                ]
            );

            // Reset variants each run so re-seeding doesn't duplicate them
            $product->weightVariants()->delete();

            $variantWeights = $weights->take(3);
            foreach ($variantWeights as $i => $weight) {
                $multiplier = 1 + ($weight->value_kg - $variantWeights->first()->value_kg);
                $variantPrice = round($data['price'] * max($multiplier, 1), -1);

                ProductWeight::create([
                    'product_id' => $product->id,
                    'weight_id' => $weight->id,
                    'egg_type' => $data['egg_type'] === 'egg' ? 'egg' : 'eggless',
                    'price' => $variantPrice,
                    'discount_price' => $variantPrice > 600 ? $variantPrice - 50 : null,
                    'stock' => 50,
                    'is_default' => $i === 0,
                    'is_active' => true,
                ]);

                if ($data['egg_type'] === 'both') {
                    ProductWeight::create([
                        'product_id' => $product->id,
                        'weight_id' => $weight->id,
                        'egg_type' => 'egg',
                        'price' => $variantPrice,
                        'discount_price' => $variantPrice > 600 ? $variantPrice - 50 : null,
                        'stock' => 50,
                        'is_default' => false,
                        'is_active' => true,
                    ]);
                }
            }

            $flavorSync = [];
            foreach ($data['flavors'] as $j => $flavorSlug) {
                if ($flavor = $flavors->get($flavorSlug)) {
                    $flavorSync[$flavor->id] = ['price_modifier' => 0, 'is_default' => $j === 0];
                }
            }
            $product->flavors()->sync($flavorSync);

            $occasionIds = collect($data['occasions'])
                ->map(fn ($slug) => $occasions->get($slug)?->id)
                ->filter()
                ->toArray();
            $product->occasions()->sync($occasionIds);

            if ($addons->count()) {
                $product->addons()->sync($addons->random(min(3, $addons->count()))->pluck('id')->toArray());
            }

            if ($deliveryOptions->count()) {
                $product->deliveryOptions()->sync($deliveryOptions->pluck('id')->toArray());
            }
        }

        $this->command->info('Products seeded successfully.');
    }
}