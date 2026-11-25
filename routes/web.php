
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\ProfilSekolahController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

// Halaman publik peta
Route::controller(PetaController::class)->group(function () {
    Route::get('/', 'index')->name('peta.index');
    Route::get('/pencarian', 'cari')->name('peta.cari');
    Route::get('/klaster', 'klaster')->name('peta.klaster');
    Route::get('/peta-panas', 'panas')->name('peta.panas');
    Route::get('/wilayah', 'wilayah')->name('peta.wilayah');
    Route::get('/rute', 'rute')->name('peta.rute');
    Route::get('/cek-alamat', 'alamat')->name('peta.alamat');
    Route::get('/data/sekolah', 'geojson')->name('peta.data');
    Route::get('/data/wilayah', 'geojsonWilayah')->name('peta.data.wilayah');
    Route::get('/gabungan', 'gabungan')->name('peta.gabungan');
});


// Halaman profil publik sekolah
Route::get('/sekolah/{sekolah}/profil-publik', [
    ProfilSekolahController::class,
    'publicShow'
])->name('sekolah.profil.public');

// Tamu
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

// Pengguna yang sudah login
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    // Ubah kata sandi untuk semua pengguna
    Route::get('/ubah-kata-sandi', [PasswordController::class, 'edit'])
        ->name('password.edit');

    Route::put('/ubah-kata-sandi', [PasswordController::class, 'update'])
        ->name('password.update');

    // Dinas: kelola sekolah
    Route::middleware('role:dinas')
        ->prefix('dinas')
        ->name('dinas.')
        ->group(function () {
            Route::resource('sekolah', SekolahController::class)
                ->only(['index', 'create', 'store', 'destroy']);
        });

    // Admin: kelola pengguna
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', UserController::class)
                ->except('show');

            Route::patch('/users/{user}/toggle-active', [
                UserController::class,
                'toggleActive'
            ])->name('users.toggle-active');
        });

    // Admin, Dinas, dan Sekolah: ubah data dan profil sekolah
    Route::middleware('role:admin,dinas,sekolah')->group(function () {

        Route::get('sekolah/{sekolah}/edit', [
            SekolahController::class,
            'edit'
        ])->name('sekolah.edit');

        Route::put('sekolah/{sekolah}', [
            SekolahController::class,
            'update'
        ])->name('sekolah.update');

        // Edit profil sekolah
        Route::get('sekolah/{sekolah}/profil', [
            ProfilSekolahController::class,
            'edit'
        ])->name('sekolah.profil.edit');

        // Update profil sekolah
        Route::put('sekolah/{sekolah}/profil', [
            ProfilSekolahController::class,
            'update'
        ])->name('sekolah.profil.update');

    });

});
