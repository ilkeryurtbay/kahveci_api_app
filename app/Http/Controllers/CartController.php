<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

use Illuminate\Support\Facades\Response;

class CartController extends Controller
{
    public function createCart(Request $request)
    {
        $user = Auth::user();

        if (!$user->cart) {
            Cart::create(['user_id' => $user->id]);
            return response()->json(['message' => 'Sepet başarıyla oluşturuldu'], 201);
        }

        return response()->json(['message' => 'Kullanıcı zaten bir sepete sahip'], 400);
    }

    public function addItemToCart(Request $request)
    {


        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
        }

        $product = Product::findOrFail($productId);

        if ($product->stock_quantity < $quantity) {
            return response()->json(['message' => 'Stokta yeterli ürün bulunmamaktadır'], 400);
        }

        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return response()->json(['message' => 'Ürün sepete eklendi'], 200);
    }


    public function removeItemFromCart(Request $request)
    {
        $userId = Auth::id(); // Şu anki oturum açmış kullanıcının ID'sini al

        // Kullanıcıyı bul
        $user = User::find($userId);

        if (!$user) {
            // Kullanıcı bulunamadı
            return response()->json(['message' => 'Kullanıcı bulunamadı'], 404);
        }

        // Kullanıcının sepetini al
        $cart = $user->cart;

        if (!$cart) {
            // Kullanıcının sepeti bulunamadı
            return response()->json(['message' => 'Kullanıcıya ait bir sepet bulunamadı'], 404);
        }

        // İsteğin içerisinden ürün ID'sini al
        $productId = $request->product_id;

        // Ürünü bulmak için cartItems ilişkisini kullanabiliriz
        $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $productId)->first();

        if (!$cartItem) {
            // Belirtilen ürün sepetinizde bulunamadı
            return response()->json(['message' => 'Belirtilen ürün sepetinizde bulunamadı'], 404);
        }

        // Ürünün miktarını azalt veya ürünü sepetten kaldır
        if ($cartItem->quantity > 1) {
            // Ürün miktarını azalt
            $cartItem->quantity -= 1;
            $cartItem->save();
        } else {
            // Ürün miktarı 1 ise ürünü sepetten kaldır
            $cartItem->delete();
        }

        return response()->json(['message' => 'Ürün sepetten başarıyla kaldırıldı veya miktarı azaltıldı'], 200);
    }



    public function updateCartItemQuantity(Request $request)
{
    $userId = Auth::id(); // Şu anki oturum açmış kullanıcının ID'sini al

    // Kullanıcıyı bul
    $user = User::find($userId);

    if (!$user) {
        // Kullanıcı bulunamadı
        return response()->json(['message' => 'Kullanıcı bulunamadı'], 404);
    }

    // Kullanıcının sepetini al
    $cart = $user->cart;

    if (!$cart) {
        // Kullanıcının sepeti bulunamadı
        return response()->json(['message' => 'Kullanıcıya ait bir sepet bulunamadı'], 404);
    }

    // İsteğin içerisinden ürün ID'sini ve miktarı al
    $productId = $request->product_id;
    $quantity = $request->quantity;

    // Ürünü bulmak için cartItems ilişkisini kullanabiliriz
    $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $productId)->first();
    $product = Product::findOrFail($productId);

    if ($product->stock_quantity < $quantity) {
        return response()->json(['message' => 'Stokta yeterli ürün bulunmamaktadır'], 400);
    }

    if (!$cartItem) {
        // Belirtilen ürün sepetinizde bulunamadı
        return response()->json(['message' => 'Belirtilen ürün sepetinizde bulunamadı'], 404);
    }

    if ($quantity <= 0) {
        // Geçersiz miktar
        return response()->json(['message' => 'Miktar geçersiz'], 400);
    }

    // Ürün miktarını güncelle
    $cartItem->quantity = $quantity;
    $cartItem->save();

    return response()->json(['message' => 'Ürün miktarı başarıyla güncellendi'], 200);
}



    public function getCart(Request $request)
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Kullanıcıya ait bir sepet bulunamadı'], 404);
        }

        $totalAmount = 0;
        foreach ($cart->items as $item) {
            $product = $item->product;
            $totalAmount += $product->price * $item->quantity;
        }

        $shippingCost = ($totalAmount >= 500) ? 0 : 54.99;

        $discount = 0;
        $reward = null;

        // indirimleri burada kontrol ediyoruz
        if ($totalAmount > 3000) {
            $discount = $totalAmount * 0.25;
            $reward = '1 KG kahve kazandınız';
        } elseif ($totalAmount > 2000 && $totalAmount <= 3000) {
            $discount = $totalAmount * 0.20;
        } elseif ($totalAmount > 1500 && $totalAmount <= 2000) {
            $discount = $totalAmount * 0.15;
        } elseif ($totalAmount > 1000 && $totalAmount <= 1500) {
            $discount = $totalAmount * 0.10;
        }

        $totalAmount -= $discount;

        $couponCode = $request->input('couponCode');

        // Kupon kodlarını burada kontrol ediyoruz
        if ($couponCode) {
            if (strlen($couponCode) < 13 || strlen($couponCode) > 15) {
                return response()->json(['message' => 'Lütfen 13-15 karakter arasında bir Kupon kodu giriniz'], 400);
            }

            if (substr($couponCode, 0, 3) !== 'TTN') {
                return response()->json(['message' => 'Geçersiz bir kupon kodu girdiniz'], 400);
            }

            // kupon kodu algoritmasında T harfi kuralı
            $remainingPart = substr($couponCode, 3);
            $tCount = substr_count($remainingPart, 'T');

            if ($tCount < 3) {
                return response()->json(['message' => 'Geçersiz bir kupon kodu girdiniz'], 400);
            }

            // Geçerli kupon kodu mesajımız
            $responseMessage = 'Kupon kodunuz geçerli, bir sonraki alışverişiniz bizden.';
        } else {
            // No coupon code provided
            $responseMessage = 'Kupon kodu girilmedi';
        }

        return response()->json([
            'items' => $cart->items,
            'totalAmount' => $totalAmount,
            'shippingCost' => $shippingCost,
            'discount' => $discount,
            'reward' => $reward,
            'couponCode' => $responseMessage
        ], 200);
    }


    public function cartConfirmOrder (Request $request)
    {

        // Şu anki oturum açmış kullanıcıyı al
        $user = Auth::user();

        // Kullanıcının sepetini ve sepet öğelerini alma işlemi
        $cart = $user->cart;
        $cartItems = $cart->items;

        // Sipariş toplam tutarını al
        $totalAmount = $request->totalAmount;



        // Sipariş onaylandıktan sonra mail gönderme işlemi

        $mail = new OrderConfirmation($user, $totalAmount);
        // Mail gönderme işlemi

        $mail= Mail::to($user->email)->send($mail);

        // Mail gönderimini kontrol et
        if ($mail) {
            return response()->json(['message' => 'Sipariş onaylandı, ve mail gönderildi.'], 200);
        } else {
            return response()->json(['message' => 'Mail gönderimi sırasında bir hata oluştu.'], 500);
        }
    }





}
