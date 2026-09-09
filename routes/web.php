<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlavorController;
use App\Http\Controllers\Admin\WeightController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\DeliveryOptionController;
use App\Http\Controllers\Admin\OccasionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceablePincodeController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\AdminBlogController;

use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProductFeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderPlacementController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\TrackOrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BlogController;

use App\Http\Controllers\Account\MyOrdersController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\MyReviewsController;
use App\Http\Controllers\Account\DashboardController;
// Route::get('/', function () {
//     return view('welcome');
// });
 Route::get('/', [HomeController::class, 'index'])->name('home');


Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/product/{productId}/reviewable-items', [ReviewController::class, 'reviewableItems'])->name('reviews.reviewable-items');
Route::get('/track-order', [TrackOrderController::class, 'show'])->name('track-order.show');
Route::post('/track-order', [TrackOrderController::class, 'search'])->name('track-order.search');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/add-addon', [CartController::class, 'addAddon'])->name('cart.add-addon');
    Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.update-quantity');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
});
Route::middleware('customer.auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/apply-coupon', [OrderPlacementController::class, 'applyCoupon'])->name('checkout.apply-coupon');
    Route::post('/checkout/remove-coupon', [OrderPlacementController::class, 'removeCoupon'])->name('checkout.remove-coupon');
    Route::post('/checkout/place-order', [OrderPlacementController::class, 'place'])->name('checkout.place');
});

Route::middleware('customer.auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [MyOrdersController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [MyOrdersController::class, 'show'])->name('orders.show');

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.set-default');
    Route::get('/reviews', [MyReviewsController::class, 'index'])->name('reviews.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});
Route::get('/order-confirmation/{orderNumber}', [OrderPlacementController::class, 'confirmation'])->name('order.confirmation');
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('razorpay.webhook');
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/send-otp', [CustomerAuthController::class, 'sendOtp'])->name('auth.send-otp');
    Route::post('/auth/verify-otp', [CustomerAuthController::class, 'verifyOtp'])->name('auth.verify-otp');
    Route::post('/auth/complete-signup', [CustomerAuthController::class, 'completeSignup'])->name('auth.complete-signup');
});

Route::middleware(['throttle:20,1', 'customer.auth'])->group(function () {
    Route::post('/payment/razorpay/create-order', [RazorpayController::class, 'createOrder'])->name('razorpay.create-order');
    Route::post('/payment/razorpay/verify', [RazorpayController::class, 'verifyAndPlaceOrder'])->name('razorpay.verify');
});
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/auth/logout', [CustomerAuthController::class, 'logout'])->name('auth.logout');
Route::post('/auth/logout', [CustomerAuthController::class, 'logout'])->name('auth.logout');
Route::get('/{slug}', [CategoryPageController::class, 'show'])->name('category.show');
Route::get('product/{slug}', [ProductDetailController::class, 'show'])->name('product.show');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
        Route::get('register', [AdminAuthController::class, 'showRegister'])->name('register');
        Route::post('register', [AdminAuthController::class, 'register'])->name('register.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/data', [CategoryController::class, 'data'])->name('categories.data');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('categories/{category}/fetch', [CategoryController::class, 'fetch'])->name('categories.fetch');
        Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::get('categories/{category}/children', [CategoryController::class, 'children'])->name('categories.children');

        Route::get('flavors', [FlavorController::class, 'index'])->name('flavors.index');
        Route::get('flavors/data', [FlavorController::class, 'data'])->name('flavors.data');
        Route::post('flavors', [FlavorController::class, 'store'])->name('flavors.store');
        Route::put('flavors/{flavor}', [FlavorController::class, 'update'])->name('flavors.update');
        Route::delete('flavors/{flavor}', [FlavorController::class, 'destroy'])->name('flavors.destroy');
        Route::get('flavors/{flavor}/fetch', [FlavorController::class, 'fetch'])->name('flavors.fetch');
        Route::post('flavors/{flavor}/toggle-status', [FlavorController::class, 'toggleStatus'])->name('flavors.toggle-status');

        Route::get('weights', [WeightController::class, 'index'])->name('weights.index');
        Route::get('weights/data', [WeightController::class, 'data'])->name('weights.data');
        Route::post('weights', [WeightController::class, 'store'])->name('weights.store');
        Route::put('weights/{weight}', [WeightController::class, 'update'])->name('weights.update');
        Route::delete('weights/{weight}', [WeightController::class, 'destroy'])->name('weights.destroy');
        Route::get('weights/{weight}/fetch', [WeightController::class, 'fetch'])->name('weights.fetch');
        Route::post('weights/{weight}/toggle-status', [WeightController::class, 'toggleStatus'])->name('weights.toggle-status');

        Route::get('addons', [AddonController::class, 'index'])->name('addons.index');
        Route::get('addons/data', [AddonController::class, 'data'])->name('addons.data');
        Route::post('addons', [AddonController::class, 'store'])->name('addons.store');
        Route::put('addons/{addon}', [AddonController::class, 'update'])->name('addons.update');
        Route::delete('addons/{addon}', [AddonController::class, 'destroy'])->name('addons.destroy');
        Route::get('addons/{addon}/fetch', [AddonController::class, 'fetch'])->name('addons.fetch');
        Route::post('addons/{addon}/toggle-status', [AddonController::class, 'toggleStatus'])->name('addons.toggle-status');

        Route::get('delivery-options', [DeliveryOptionController::class, 'index'])->name('delivery-options.index');
        Route::get('delivery-options/data', [DeliveryOptionController::class, 'data'])->name('delivery-options.data');
        Route::post('delivery-options', [DeliveryOptionController::class, 'store'])->name('delivery-options.store');
        Route::put('delivery-options/{deliveryOption}', [DeliveryOptionController::class, 'update'])->name('delivery-options.update');
        Route::delete('delivery-options/{deliveryOption}', [DeliveryOptionController::class, 'destroy'])->name('delivery-options.destroy');
        Route::get('delivery-options/{deliveryOption}/fetch', [DeliveryOptionController::class, 'fetch'])->name('delivery-options.fetch');
        Route::post('delivery-options/{deliveryOption}/toggle-status', [DeliveryOptionController::class, 'toggleStatus'])->name('delivery-options.toggle-status');


        Route::get('occasions', [OccasionController::class, 'index'])->name('occasions.index');
        Route::get('occasions/data', [OccasionController::class, 'data'])->name('occasions.data');
        Route::post('occasions', [OccasionController::class, 'store'])->name('occasions.store');
        Route::put('occasions/{occasion}', [OccasionController::class, 'update'])->name('occasions.update');
        Route::delete('occasions/{occasion}', [OccasionController::class, 'destroy'])->name('occasions.destroy');
        Route::get('occasions/{occasion}/fetch', [OccasionController::class, 'fetch'])->name('occasions.fetch');
        Route::post('occasions/{occasion}/toggle-status', [OccasionController::class, 'toggleStatus'])->name('occasions.toggle-status');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/data', [ProductController::class, 'data'])->name('products.data');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('products/{productImage}/delete-image', [ProductController::class, 'deleteImage'])->name('products.delete-image');

        Route::get('pincodes', [ServiceablePincodeController::class, 'index'])->name('pincodes.index');
        Route::get('pincodes/data', [ServiceablePincodeController::class, 'data'])->name('pincodes.data');
        Route::post('pincodes', [ServiceablePincodeController::class, 'store'])->name('pincodes.store');
        Route::put('pincodes/{pincode}', [ServiceablePincodeController::class, 'update'])->name('pincodes.update');
        Route::delete('pincodes/{pincode}', [ServiceablePincodeController::class, 'destroy'])->name('pincodes.destroy');
        Route::get('pincodes/{pincode}/fetch', [ServiceablePincodeController::class, 'fetch'])->name('pincodes.fetch');
        Route::post('pincodes/{pincode}/toggle-status', [ServiceablePincodeController::class, 'toggleStatus'])->name('pincodes.toggle-status');

        Route::get('pincodes/export', [ServiceablePincodeController::class, 'export'])->name('pincodes.export');
        Route::post('pincodes/import', [ServiceablePincodeController::class, 'import'])->name('pincodes.import');
        Route::get('pincodes/sample-template', [ServiceablePincodeController::class, 'downloadSampleTemplate'])->name('pincodes.sample-template');

        Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('coupons/data', [CouponController::class, 'data'])->name('coupons.data');
        Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('coupons/{coupon}/fetch', [CouponController::class, 'fetch'])->name('coupons.fetch');
        Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');

        Route::get('settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');

        Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('banners/data', [BannerController::class, 'data'])->name('banners.data');
        Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
        Route::put('banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
        Route::get('banners/{banner}/fetch', [BannerController::class, 'fetch'])->name('banners.fetch');
        Route::post('banners/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/online', [OrderController::class, 'online'])->name('orders.online');
        Route::get('orders/cod', [OrderController::class, 'cod'])->name('orders.cod');
        Route::get('orders/data', [OrderController::class, 'data'])->name('orders.data');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('orders/{order}/update-notes', [OrderController::class, 'updateAdminNotes'])->name('orders.update-notes');

        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('reviews/data', [AdminReviewController::class, 'data'])->name('reviews.data');
        Route::get('reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
        Route::post('reviews/{review}/update-status', [AdminReviewController::class, 'updateStatus'])->name('reviews.update-status');

        Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('pages/data', [AdminPageController::class, 'data'])->name('pages.data');
        Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
        Route::post('pages', [AdminPageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::delete('pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');
        Route::post('pages/{page}/toggle-status', [AdminPageController::class, 'toggleStatus'])->name('pages.toggle-status');

        Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('newsletter/data', [NewsletterController::class, 'data'])->name('newsletter.data');
        Route::get('newsletter/export', [NewsletterController::class, 'export'])->name('newsletter.export');
        Route::delete('newsletter/{subscriber}', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');

        Route::get('blogs', [AdminBlogController::class, 'index'])->name('blogs.index');
        Route::get('blogs/data', [AdminBlogController::class, 'data'])->name('blogs.data');
        Route::get('blogs/create', [AdminBlogController::class, 'create'])->name('blogs.create');
        Route::post('blogs', [AdminBlogController::class, 'store'])->name('blogs.store');
        Route::get('blogs/{blog}/edit', [AdminBlogController::class, 'edit'])->name('blogs.edit');
        Route::put('blogs/{blog}', [AdminBlogController::class, 'update'])->name('blogs.update');
        Route::delete('blogs/{blog}', [AdminBlogController::class, 'destroy'])->name('blogs.destroy');
        Route::post('blogs/{blog}/toggle-status', [AdminBlogController::class, 'toggleStatus'])->name('blogs.toggle-status');
    });
    
   
});
Route::get('/feed/google-shopping.xml', [ProductFeedController::class, 'googleShopping'])->name('feed.google');
Route::get('/feed/facebook-catalog.csv', [ProductFeedController::class, 'facebookCatalog'])->name('feed.facebook');

