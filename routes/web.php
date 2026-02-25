<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ExpenseController::class, 'getExpensePerUser'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/addgroup', function(){
            return view('group.add-group');
    })->name('add.group');
     Route::get('/addcollocation', function(){
            return view('collocation.add-collocation');
    })->name('add.collocation');
    });

require __DIR__.'/auth.php';
