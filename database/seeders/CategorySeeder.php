<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // name => [ subcategory => [child categories...] ]
        $tree = [
            'Birthday Cakes' => [
                'Kids Birthday' => ['Cartoon Theme', 'Superhero Theme'],
                'Adult Birthday' => [],
                'First Birthday' => [],
            ],
            'Anniversary Cakes' => [
                'Wedding Anniversary' => ['25th Anniversary', '50th Anniversary'],
                'Engagement Cakes' => [],
            ],
            "Valentine's Day Cakes" => [
                'Heart Shape Cakes' => [],
                'Romantic Cakes' => [],
            ],
            'Designer Cakes' => [
                'Fondant Cakes' => [],
                'Theme Cakes' => [],
            ],
            'Bento Cakes' => [
                'Relationship' => ['Mom', 'Dad', 'Wife', 'Husband', 'Girlfriend', 'Friend'],
                'Birthday' => [],
            ],
            'Photo Cakes' => [],
            'Wedding Cakes' => [
                'Tiered Cakes' => [],
            ],
            'Occasion Cakes' => [
                "Mother's Day" => [],
                "Father's Day" => [],
                'Farewell' => [],
                'Retirement' => [],
                'Baby Shower' => [],
            ],
            'Cookies' => [
                'Chocolate Chip Cookies' => [],
                'Assorted Cookie Jars' => [],
            ],
        ];

        foreach ($tree as $categoryName => $subcategories) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'is_active' => true]
            );

            foreach ($subcategories as $subName => $childNames) {
                $subcategory = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryName . '-' . $subName)],
                    [
                        'parent_id' => $category->id,
                        'name' => $subName,
                        'is_active' => true,
                    ]
                );

                foreach ($childNames as $i => $childName) {
                    Category::firstOrCreate(
                        ['slug' => Str::slug($categoryName . '-' . $subName . '-' . $childName)],
                        [
                            'parent_id' => $subcategory->id,
                            'name' => $childName,
                            'sort_order' => $i,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}