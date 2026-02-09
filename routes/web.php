<?php

use Illuminate\Support\Facades\Route;

// Frontend movido a otra carpeta
// Las APIs están en routes/api.php

Route::get('/', function () {
    return response()->json(['status' => 'ok', 'message' => 'CV Generator API is running']);
});
