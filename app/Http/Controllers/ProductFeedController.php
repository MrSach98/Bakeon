<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductFeedController extends Controller
{
    private function verifyAccess(Request $request, SiteSetting $settings): bool
    {
        return $settings->feed_enabled && $request->query('token') === $settings->feed_token;
    }

    /**
     * Google Merchant Center / Google Ads Shopping feed (XML - RSS 2.0 format).
     * URL: /feed/google-shopping.xml?token=xxxx
     */
    public function googleShopping(Request $request)
    {
        $settings = SiteSetting::current();

        if (! $this->verifyAccess($request, $settings)) {
            abort(403, 'Invalid or disabled feed access.');
        }

        $products = Product::with(['primaryImage', 'images', 'weightVariants', 'category'])
            ->where('status', 'active')
            ->get();

        $items = '';

        foreach ($products as $product) {
            $variant = $product->defaultVariant();
            if (! $variant) continue;

            $image = $product->primaryImage ?? $product->images->first();
            if (! $image) continue; // Google requires an image

            $price = $variant->discount_price ?? $variant->price;
            $stock = $variant->stock ?? 0;
            $availability = $stock > 0 ? 'in stock' : 'out of stock';

            $items .= '<item>';
            $items .= '<g:id>' . e($product->id) . '</g:id>';
            $items .= '<g:title>' . e($product->name) . '</g:title>';
            $items .= '<g:description>' . e(strip_tags($product->short_description ?? $product->description ?? $product->name)) . '</g:description>';
            $items .= '<g:link>' . e(url('/' . $product->slug)) . '</g:link>';
            $items .= '<g:image_link>' . e(asset($image->image_path)) . '</g:image_link>';
            $items .= '<g:condition>new</g:condition>';
            $items .= '<g:availability>' . $availability . '</g:availability>';
            $items .= '<g:price>' . number_format($price, 2, '.', '') . ' ' . $settings->feed_currency . '</g:price>';
            $items .= '<g:brand>' . e($settings->store_name ?? 'Sweet Bakes') . '</g:brand>';
            $items .= '<g:google_product_category>Food, Beverages &amp; Tobacco &gt; Food Items &gt; Baked Goods &gt; Cakes</g:google_product_category>';
            $items .= '<g:product_type>' . e($product->category->name ?? 'Cakes') . '</g:product_type>';
            $items .= '<g:identifier_exists>no</g:identifier_exists>';
            $items .= '</item>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
<title>' . e($settings->store_name ?? 'Sweet Bakes') . ' Product Feed</title>
<link>' . e(url('/')) . '</link>
<description>Product catalog feed for Google Shopping / Ads</description>
' . $items . '
</channel>
</rss>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Facebook / Instagram Ads Catalog feed (CSV format — widely supported,
     * simpler than XML for Meta Commerce Manager).
     * URL: /feed/facebook-catalog.csv?token=xxxx
     */
    public function facebookCatalog(Request $request)
    {
        $settings = SiteSetting::current();

        if (! $this->verifyAccess($request, $settings)) {
            abort(403, 'Invalid or disabled feed access.');
        }

        $products = Product::with(['primaryImage', 'images', 'weightVariants', 'category'])
            ->where('status', 'active')
            ->get();

        $headers = [
            'id', 'title', 'description', 'availability', 'condition',
            'price', 'link', 'image_link', 'brand', 'product_type',
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($products as $product) {
            $variant = $product->defaultVariant();
            if (! $variant) continue;

            $image = $product->primaryImage ?? $product->images->first();
            if (! $image) continue;

            $price = $variant->discount_price ?? $variant->price;
            $stock = $variant->stock ?? 0;
            $availability = $stock > 0 ? 'in stock' : 'out of stock';

            $row = [
                $product->id,
                $this->csvEscape($product->name),
                $this->csvEscape(strip_tags($product->short_description ?? $product->description ?? $product->name)),
                $availability,
                'new',
                number_format($price, 2, '.', '') . ' ' . $settings->feed_currency,
                url('/' . $product->slug),
                asset($image->image_path),
                $this->csvEscape($settings->store_name ?? 'Sweet Bakes'),
                $this->csvEscape($product->category->name ?? 'Cakes'),
            ];

            $csv .= implode(',', $row) . "\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'inline; filename="facebook-catalog.csv"',
        ]);
    }

    private function csvEscape(string $value): string
    {
        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }
}