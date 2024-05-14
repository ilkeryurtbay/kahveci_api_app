<?php

// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user) {
            // Kullanıcı token'ı ile doğrulandı
            $products = Product::all();
            return response()->json($products, 200);
        } else {
            return response()->json(['message' => 'Token geçerli değil veya kullanıcıya ait değil.'], 401);
        }
    }
}
