<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\SiteContentController;
use App\Http\Controllers\Web\SiteSettingController;
use App\Http\Controllers\Web\UserController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

$design = static fn (string $page) => response()->file(public_path('original-design/'.$page.'-standalone.html'), [
    'Cache-Control' => 'public, max-age=600',
]);

// The published design is identical for every visitor: it does not need a session
// cookie, and can be safely cached by browsers and public web-server caches.
Route::withoutMiddleware([
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
])->group(function () use ($design) {
    Route::get('/', fn () => $design('home'))->name('home');
    Route::get('/layanan', fn () => $design('services'))->name('services');
    Route::get('/tentang-kami', fn () => $design('about'))->name('about');
    Route::get('/galeri', fn () => $design('gallery'))->name('gallery');
    Route::get('/detail-proyek', fn () => redirect()->route('gallery'))->name('projects.sample');
    Route::get('/proyek/{slug}', function (string $slug) use ($design) {
        Project::where('slug', $slug)->where('is_published', true)->firstOrFail();

        // Publication can change at any time; do not cache the slug check.
        return response()->file(public_path('original-design/project-standalone.html'), ['Cache-Control' => 'no-store']);
    })->name('projects.show');
    Route::get('/kontak', fn () => $design('contact'))->name('contact');
});

Route::get('/login', [AuthController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware(['guest', 'throttle:5,1'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/home', fn () => redirect()->route(auth()->user()->adminHomeRoute()))->middleware('auth');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::middleware('admin.access:projects')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/proyek/baru', [AdminController::class, 'create'])->name('projects.create');
        Route::post('/proyek', [AdminController::class, 'store'])->name('projects.store');
        Route::get('/proyek/{project}/edit', [AdminController::class, 'edit'])->name('projects.edit');
        Route::put('/proyek/{project}', [AdminController::class, 'update'])->name('projects.update');
        Route::delete('/proyek/{project}', [AdminController::class, 'destroy'])->name('projects.destroy');
        Route::post('/proyek/{project}/foto', [AdminController::class, 'addImage'])->name('images.store');
        Route::delete('/foto/{image}', [AdminController::class, 'removeImage'])->name('images.destroy');
    });
    Route::middleware('admin.access:content')->group(function () {
        Route::get('/konten', [SiteContentController::class, 'index'])->name('content.index');
        Route::post('/konten', [SiteContentController::class, 'store'])->name('content.store');
        Route::post('/konten/gambar', [SiteContentController::class, 'upload'])->name('content.upload');
        Route::delete('/konten/{content}', [SiteContentController::class, 'destroy'])->name('content.destroy');
    });
    Route::middleware('admin.access:settings')->group(function () {
        Route::get('/pengaturan', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [SiteSettingController::class, 'update'])->name('settings.update');
    });
    Route::middleware('admin.access:users')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::get('/pengguna/baru', [UserController::class, 'create'])->name('users.create');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::get('/pengguna/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware('admin.access:messages')->group(function () {
        Route::get('/pesan', [AdminController::class, 'messages'])->name('messages.index');
        Route::delete('/pesan/{message}', [AdminController::class, 'deleteMessage'])->name('messages.destroy');
    });
});
