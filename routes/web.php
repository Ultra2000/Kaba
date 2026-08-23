<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalogue
Route::get('/explorer', [ListingController::class, 'index'])->name('listings.index');
Route::get('/livres/{listing}', [ListingController::class, 'show'])->name('listings.show');

// Vendeurs
Route::get('/vendeurs', [SellerController::class, 'index'])->name('sellers.index');
Route::get('/vendeurs/{user}', [SellerController::class, 'show'])->name('sellers.show');

// Publication + actions de confiance (connecté)
Route::middleware('auth')->group(function () {
    Route::get('/publier', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/livres', [ListingController::class, 'store'])->name('listings.store');

    Route::get('/favoris', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favoris/{listing}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('/vendeurs/{user}/suivre', [SellerController::class, 'follow'])->name('sellers.follow');
    Route::post('/vendeurs/{user}/avis', [ReviewController::class, 'store'])->name('reviews.store');

    Route::post('/livres/{listing}/signaler', [ReportController::class, 'storeForListing'])->name('reports.listing');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/lire', [NotificationController::class, 'markAllRead'])->name('notifications.read');

    // Messagerie
    Route::get('/messagerie', [\App\Http\Controllers\ConversationController::class, 'index'])->name('messages.index');
    Route::post('/messagerie/demarrer/{listing}', [\App\Http\Controllers\ConversationController::class, 'start'])->name('messages.start');
    Route::get('/messagerie/{conversation}', [\App\Http\Controllers\ConversationController::class, 'show'])->name('messages.show');
    Route::post('/messagerie/{conversation}', [\App\Http\Controllers\ConversationController::class, 'storeMessage'])->name('messages.store');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Espace administration
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');

    Route::get('/signalements', [\App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('reports');
    Route::post('/signalements/{report}/resoudre', [\App\Http\Controllers\Admin\AdminController::class, 'resolveReport'])->name('reports.resolve');
    Route::post('/signalements/{report}/ignorer', [\App\Http\Controllers\Admin\AdminController::class, 'dismissReport'])->name('reports.dismiss');

    Route::get('/annonces', [\App\Http\Controllers\Admin\AdminController::class, 'listings'])->name('listings');
    Route::post('/annonces/{listing}/valider', [\App\Http\Controllers\Admin\AdminController::class, 'approveListing'])->name('listings.approve');
    Route::post('/annonces/{listing}/masquer', [\App\Http\Controllers\Admin\AdminController::class, 'toggleListing'])->name('listings.toggle');
    Route::delete('/annonces/{listing}', [\App\Http\Controllers\Admin\AdminController::class, 'destroyListing'])->name('listings.destroy');

    Route::get('/utilisateurs', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
    Route::post('/utilisateurs/{user}/verifier', [\App\Http\Controllers\Admin\AdminController::class, 'toggleUserVerified'])->name('users.verify');

    Route::get('/categories', [\App\Http\Controllers\Admin\AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [\App\Http\Controllers\Admin\AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category:id}', [\App\Http\Controllers\Admin\AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category:id}', [\App\Http\Controllers\Admin\AdminController::class, 'destroyCategory'])->name('categories.destroy');
});

require __DIR__.'/auth.php';
