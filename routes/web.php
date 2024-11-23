<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BoardingHouseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/city/{slug}', [CityController::class, 'show'])->name('city.show');

Route::get('/find-hotel', [BoardingHouseController::class, 'findHotel'])->name('find-hotel');
Route::get('/boarding-house/{slug}', [BoardingHouseController::class, 'show'])->name('boarding-house.show');
Route::get('/boarding-house/{slug}/rooms', [BoardingHouseController::class, 'rooms'])->name('boarding-house.rooms');

Route::get('/check-booking', [BookingController::class, 'checkBooking'])->name('check-booking');
Route::get('/boarding-house/booking/{slug}', [BookingController::class, 'booking'])->name('booking');
Route::get('/boarding-house/booking/{slug}/information', [BookingController::class, 'information'])->name('booking.information');
Route::post('/boarding-house/booking/{slug}/information/save', [BookingController::class, 'saveInformation'])->name('booking.information.save');

Route::get('/boarding-house/booking/{slug}/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
Route::post('/boarding-house/booking/{slug}/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::get('/booking-success', [BookingController::class, 'success'])->name('booking.success');

Route::post('check-booking', [BookingController::class, 'show'])->name('check-booking.show');
