<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // Los usernames en la tabla usuario son simples (sin @dominio)
        // Ejemplos: admin, carlos.estrada, cristian.reta, etc.
        $username = $request->username;
        
        // Si el usuario ingresó un email, extraer solo la parte local
        if (str_contains($username, '@')) {
            $username = explode('@', $username)[0];
        }

        // Intentar autenticación con username (campo en la tabla usuario)
        $credentials = [
            'username' => $username,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Verificar que el usuario esté activo
            $user = Auth::user();
            if (!$user->isActive()) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Su cuenta está bloqueada o inactiva. Contacte al administrador.'
                ]);
            }

            return redirect()->intended(route('turnos.index'));
        }

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
