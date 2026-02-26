<?php

use App\Http\Controllers\CollocationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/join/{token}', [InvitationController::class, 'show'])->name('invitation.join');
Route::get('/join/{token}/accept', [InvitationController::class, 'accept'])
    ->middleware('auth')->name('invitation.accept');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [ExpenseController::class, 'getExpensePerUser'])->name('dashboard');

    Route::get('/collocations', [CollocationController::class, 'index'])->name('collocation.index');
    Route::get('/collocations/create', [CollocationController::class, 'create'])->name('collocation.create');
    Route::post('/collocations', [CollocationController::class, 'store'])->name('collocation.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('member')->group(function () {

        // Route::get('/collocations', [InvitationController::class, 'show'])->name('invitation.show');
        Route::get('/collocations/{collocation}', [CollocationController::class, 'show'])->name('collocation.show');
        Route::get('/collocations/{collocation}/members', [CollocationController::class, 'members'])->name('collocation.members');
        Route::delete('/collocations/{collocation}/leave', [CollocationController::class, 'leave'])->name('collocation.leave');

        Route::get('/collocations/{collocation}/expenses', [ExpenseController::class, 'index'])->name('expense.index');
        Route::get('/collocations/{collocation}/expenses/create', [ExpenseController::class, 'create'])->name('expense.create');
        Route::post('/collocations/{collocation}/expenses', [ExpenseController::class, 'store'])->name('expense.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expense.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expense.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
        Route::get('/collocations/{collocation}/payments', [PaymentController::class, 'index'])->name('payment.index');
        Route::post('/collocations/{collocation}/payments', [PaymentController::class, 'store'])->name('payment.store');
        Route::post('/payments/{payment}/complete', [PaymentController::class, 'complete'])->name('payment.complete');
        Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    });

    Route::middleware('owner')->group(function () {

        Route::get('/collocations/{collocation}/edit', [CollocationController::class, 'edit'])->name('collocation.edit');
        Route::put('/collocations/{collocation}', [CollocationController::class, 'update'])->name('collocation.update');
        Route::post('/collocations/{collocation}/cancel', [CollocationController::class, 'cancel'])->name('collocation.cancel');

        Route::delete('/collocations/{collocation}/members/{user}', [CollocationController::class, 'removeMember'])->name('collocation.removeMember');

        Route::get('/collocations/{collocation}/invite', [InvitationController::class, 'create'])->name('invitation.create');
        Route::post('/collocations/{collocation}/invite', [InvitationController::class, 'store'])->name('invitation.store');
    });
});

require __DIR__ . '/auth.php';
