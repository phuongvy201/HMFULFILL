<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
});

Route::get('/pages/contact-us', function () {
    return view('pages.contact-us');
});

Route::get('/pages/catalog-uk', function () {
    return view('pages.catalog-uk');
});

Route::get('/pages/catalog-us', function () {
    return view('pages.catalog-us');
});

Route::get('/pages/catalog-vn', function () {
    return view('pages.catalog-vn');
});

Route::get('/pages/payment-policy', function () {
    return view('pages.payment-policy');
});

Route::get('/pages/privacy-policy', function () {
    return view('pages.privacy-policy');
});

Route::get('/pages/return-refund-policy', function () {
    return view('pages.return-refund-policy');
});

Route::get('/pages/shipping-policy', function () {
    return view('pages.shipping-policy');
});

Route::get('/pages/term-condition', function () {
    return view('pages.term-condition');
});

Route::get('/pages/help-center', function () {
    return view('pages.help-center');
});
