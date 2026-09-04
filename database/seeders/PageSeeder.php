<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title' => 'About Us', 'content' => '<p>Welcome to Sweet Bakes — your trusted online bakery for freshly baked cakes, delivered with love.</p>'],
            ['title' => 'Terms and Conditions', 'content' => '<p>Please read these terms carefully before using our website or placing an order.</p>'],
            ['title' => 'Privacy Policy', 'content' => '<p>We respect your privacy and are committed to protecting your personal data.</p>'],
            ['title' => 'FAQ', 'content' => '<p>Frequently asked questions about ordering, delivery, and payments.</p>'],
            ['title' => 'Cancellation and Refund', 'content' => '<p>Details about our order cancellation and refund policy.</p>'],
            ['title' => 'Contact Us', 'content' => '<p>Get in touch with our support team for any queries.</p>'],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => Str::slug($page['title'])],
                array_merge($page, ['is_active' => true])
            );
        }
    }
}