<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\GroupShopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Locale switch
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['fr', 'en'])) {
        return redirect()->back()->withCookie(cookie()->forever('locale', $locale));
    }
    return redirect()->back();
})->name('locale.switch');

// Auth
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
    Route::get('/mot-de-passe-oublie', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/deconnexion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Account
Route::prefix('mon-compte')->name('account.')->middleware('auth')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/commandes', [AccountController::class, 'orders'])->name('orders');
    Route::get('/commandes/{reference}', [AccountController::class, 'orderShow'])->name('orders.show');
    Route::get('/commandes/{reference}/fichiers', function (string $reference) {
        $order = \App\Models\Order::where('reference', $reference)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        return view('account.order-upload', compact('order'));
    })->name('orders.upload');
    Route::get('/commandes/{reference}/facture', [AccountController::class, 'downloadInvoice'])->name('orders.invoice');
    Route::get('/factures', [AccountController::class, 'invoices'])->name('invoices');
    Route::get('/factures/{invoice}/telecharger', [AccountController::class, 'downloadInvoiceById'])->name('invoices.download');
    // TODO 2b: route /devis supprimée (système devis dégagé)
    Route::get('/adresses', [AccountController::class, 'addresses'])->name('addresses');
    Route::get('/profil', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profil', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [AccountController::class, 'updatePassword'])->name('profile.password');
    Route::get('/export', [AccountController::class, 'exportData'])->name('export');
    Route::delete('/supprimer', [AccountController::class, 'deleteAccount'])->name('delete');
});

Route::prefix('catalogue')->name('catalogue.')->group(function () {
    Route::get('/', [CatalogueController::class, 'index'])->name('index');
});

// Redirections 301 anciennes URLs
Route::get('/catalogue/{category:slug}', fn (\App\Models\Category $category) => redirect()->route('catalogue.category', $category, 301));
Route::get('/produit/{product:slug}', function (\App\Models\Product $product) {
    $category = $product->category?->parent ?? $product->category;
    return $category
        ? redirect()->route('catalogue.product', [$category, $product], 301)
        : abort(404);
});

// Redirection 301 ancien format produit


// TODO 2b: routes /mon-devis, /devis (GET+POST), /bat/* supprimées
//   - système devis dégagé (Q1: Cart/CartItem reconstruction)
//   - workflow BAT supprimé (Q2: pas de marquage)
//   - DemandeDevisController supprimé (Q6)

// Cart (auth requise — pas de guest cart sur grossiste B2B)
Route::get('/panier', fn () => view('cart.index'))->middleware('auth')->name('cart');

// Legal pages
Route::get('/politique-de-confidentialite', fn () => view('legal.privacy'))->name('legal.privacy');
Route::get('/mentions-legales', fn () => view('legal.terms'))->name('legal.terms');
Route::get('/conditions-generales-de-vente', fn () => view('legal.cgv'))->name('legal.cgv');

Route::get('/commande/checkout', function () {
    if (auth()->check()) {
        return view('checkout.index');
    }
    return view('checkout.auth');
})->name('payment.checkout');
Route::get('/commande/{order}/virement', function (string $order) {
    $order = \App\Models\Order::where('reference', $order)->firstOrFail();
    return view('checkout.virement', compact('order'));
})->name('checkout.virement');

Route::post('/commande/{order}/virement/confirmer', function (string $order) {
    $order = \App\Models\Order::where('reference', $order)->where('user_id', auth()->id())->firstOrFail();
    $order->update([
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    // Mail 1: confirmation commande (client)
    app(\App\Services\NotificationService::class)->sendOrderConfirmation($order);
    // Mail 3: attente de virement (client)
    app(\App\Services\NotificationService::class)->sendWireTransferPending($order);
    // Mail 8: notification admin nouvelle commande
    app(\App\Services\NotificationService::class)->notifyAdminNewOrder($order);

    // TODO 2b: branche has_marking supprimée (pas de marquage sur grossiste)
    return redirect()->route('account.orders')->with('success', 'Commande confirmee ! Merci d\'effectuer le virement bancaire. Votre commande sera traitee des reception du paiement.');
})->middleware('auth')->name('checkout.virement.confirm');

Route::post('/commande/{order}/virement/annuler', function (string $order) {
    $order = \App\Models\Order::where('reference', $order)->where('user_id', auth()->id())->firstOrFail();

    // Restore quote to draft
    if ($order->quote_id) {
        \App\Models\Quote::where('id', $order->quote_id)->update(['status' => 'draft', 'accepted_at' => null]);
    }

    // Delete the order
    \App\Models\OrderItem::where('order_id', $order->id)->delete();
    $order->delete();

    // TODO 2b: redirect cible 'mon-devis' supprimée → home temporairement
    return redirect()->route('home')->with('info', 'Commande annulee.');
})->middleware('auth')->name('checkout.virement.cancel');

Route::get('/commande/{order}/paiement', function (string $order) {
    $order = \App\Models\Order::where('reference', $order)->firstOrFail();
    $formToken = session('systempay_form_token');
    if (! $formToken) {
        return redirect()->route('payment.checkout')->with('checkout-error', 'Session de paiement expiree.');
    }
    $amount = str_contains($order->customer_notes ?? '', 'Acompte')
        ? round((float) $order->total_ttc / 2, 2)
        : (float) $order->total_ttc;
    $publicKey = app(\App\Services\SystempayService::class)->getPublicKey();
    return view('checkout.systempay', compact('order', 'formToken', 'publicKey', 'amount'));
})->middleware('auth')->name('checkout.systempay');

Route::post('/paiement/checkout-legacy', [PaymentController::class, 'checkout'])->name('payment.checkout.legacy');
Route::match(['get', 'post'], '/paiement/succes/{order}', [PaymentController::class, 'success'])->name('payment.success');
Route::match(['get', 'post'], '/paiement/annule/{order}', [PaymentController::class, 'cancel'])->name('payment.cancel');
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

// Systempay IPN (callback serveur)
Route::post('/systempay/ipn', function (\Illuminate\Http\Request $request) {
    $service = app(\App\Services\SystempayService::class);

    $krAnswer = $request->input('kr-answer');
    $krHash = $request->input('kr-hash');

    if ($krAnswer && $krHash) {
        if (! $service->verifyIpnHash($krAnswer, $krHash)) {
            return response('Invalid hash', 403);
        }
        $data = json_decode($krAnswer, true);
        if ($data) {
            $service->handleIpn($data);
        }
    }

    return response('OK', 200);
})->name('systempay.ipn');

// TODO 2b: routes /commande/bat/* supprimées (workflow BAT dégagé)

Route::prefix('boutique')->name('group-shop.')->group(function () {
    Route::get('/{groupShop}', [GroupShopController::class, 'show'])->name('show');
    Route::post('/{groupShop}/auth', [GroupShopController::class, 'authenticate'])->name('authenticate');
    Route::get('/{groupShop}/confirmation', [GroupShopController::class, 'confirmation'])->name('confirmation');
});

// Public order tracking (no auth required)
// TODO 2b: routes /devis/{reference}/pdf et /devis/{reference}/voir supprimées
Route::get('/commande/{orderNumber}/suivi', [App\Http\Controllers\Front\OrderTrackingController::class, 'show'])->name('order.tracking');

// TODO 2b: route /techniques-de-marquage supprimée (TechniqueController dégagé)

// Nouvelles URLs SEO : produit sous categorie
Route::get('/{categorySlug}/{productSlug}', function (string $categorySlug, string $productSlug) {
    $category = \App\Models\Category::where('slug', $categorySlug)->first();
    if (! $category) {
        abort(404);
    }
    $product = \App\Models\Product::where('slug', $productSlug)->where('is_active', true)->first();
    if (! $product) {
        abort(404);
    }
    return app(\App\Http\Controllers\CatalogueController::class)->product($category, $product);
})->name('catalogue.product')->where(['categorySlug' => '[a-z0-9\-]+', 'productSlug' => '[a-z0-9\-]+']);

// Catch-all : categorie SEO — MUST BE LAST
// TODO 2b: lookup MarkingTechnique supprimé (techniques dégagées)
Route::get('/{slug}', function (string $slug) {
    $category = \App\Models\Category::where('slug', $slug)->where('is_active', true)->first();
    if ($category) {
        return app(\App\Http\Controllers\CatalogueController::class)->category($category);
    }

    abort(404);
})->name('catalogue.category')->where('slug', '[a-z0-9\-]+');

