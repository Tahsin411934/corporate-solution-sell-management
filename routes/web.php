<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->controller(\App\Http\Controllers\AccessManagementController::class)->group(function () {
    Route::get('users', 'users')->middleware('can:users.view')->name('users.index');
    Route::get('users/data', 'usersData')->middleware('can:users.view')->name('users.data');
    Route::post('users', 'saveUser')->middleware('can:users.create')->name('users.store');
    Route::put('users/{user}', 'saveUser')->middleware('can:users.update')->name('users.update');
    Route::delete('users/{user}', 'deleteUser')->middleware('can:users.delete')->name('users.destroy');
    Route::get('roles', 'roles')->middleware('can:roles.view')->name('roles.index');
    Route::post('permissions', 'createPermission')->middleware('can:roles.create')->name('permissions.store');
    Route::get('permissions', 'permissions')->middleware('can:roles.view')->name('permissions.index');
    Route::get('permissions/data', 'permissionsData')->middleware('can:roles.view')->name('permissions.data');
    Route::put('permissions/{permission}', 'updatePermission')->middleware('can:roles.update')->name('permissions.update');
    Route::delete('permissions/{permission}', 'deletePermission')->middleware('can:roles.delete')->name('permissions.destroy');
    Route::get('roles/data', 'rolesData')->middleware('can:roles.view')->name('roles.data');
    Route::post('roles', 'saveRole')->middleware('can:roles.create')->name('roles.store');
    Route::put('roles/{role}', 'saveRole')->middleware('can:roles.update')->name('roles.update');
    Route::delete('roles/{role}', 'deleteRole')->middleware('can:roles.delete')->name('roles.destroy');
});
