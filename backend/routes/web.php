<?php

use Illuminate\Support\Facades\Route;

// The admin panel lives in the React site now, so old links are sent there.
Route::redirect('/', config('app.frontend_url').'/admin');
Route::redirect('admin/{path?}', config('app.frontend_url').'/admin')->where('path', '.*');
