<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function create()
    {
        return view('admin.products.add-product');
    }
}
