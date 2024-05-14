<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Kullanıcıyı kaydet
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);


        return response()->json(['message' => 'Kullanıcı bilgileriniz başarı ile kayıt edilmiştir, lütfen Login işlemlerine devam ediniz'], 201);
    }

    public function login(Request $request)
    {
        // Kullanıcı giriş bilgilerini doğrula
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            // Doğrulama başarılı ise token oluştur
            $user = Auth::user();
            $tokenName = $user->name . '_Token';
            $token = $user->createToken($tokenName)->plainTextToken;


            return response()->json(['message' => 'Başarılı şekilde giriş yaptınız. Lütfen Baerer token verinizi ürünleri görüntülemek için gerekli olan Auth uzantsında kullanabilmek için kaydediniz. Baerer token\'ınız : ' . $token], 200);
        } else {
            // Doğrulama başarısız ise hata döndür
            return response()->json(['error' => 'Kullanıcı bulunamadı yada Hatalı giriş yaptınız'], 401);
        }
    }
}
