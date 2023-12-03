<?php

namespace App\Http\Controllers;

use App\Events\TapStatusEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'card_uid' => ['nullable'],
            'email' => [Rule::requiredIf(!$request->card_uid), 'email'],
            'password' => [Rule::requiredIf(!$request->card_uid)],
        ]);

        if ($request->card_uid) {
            $card_id = $request->card_uid;
            $token = $request->token;

            $salt = "7b1ef72b-ebe9-4ca6-b7eb-6d7f6e18f343";

            $hash = hash('sha256', $card_id . $salt, true);
            $texthash = strtoupper(bin2hex($hash));


            // Ambil user berdasarkan RFID UID
            $user = User::where('card_uid', $card_id)->first();

            if ($user) {
                if (hash_equals($token, $texthash)) {
                    auth('web')->loginUsingId($user->id);
                    return response()->json(['authenticated' => true]);
                }
            }
            return response()->json(['authenticated' => false]);
        } else {
            // Attempt to log the user in
            if (auth()->attempt($request->only('email', 'password'))) {
                // Redirect to the dashboard
                return redirect()->route('dashboard');
            }

            // Redirect back to the login page
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }
    }

    public function logout()
    {
        auth('web')->logout();
        return redirect()->route('login');
    }

    public function rfid_login(Request $request)
    {
        $card_id = $_POST["card_id"];
        $token = $_POST["token"];

        $salt = "7b1ef72b-ebe9-4ca6-b7eb-6d7f6e18f343";

        $hash = hash('sha256', $card_id . $salt, true);
        $texthash = strtoupper(bin2hex($hash));


        // Ambil user berdasarkan RFID UID
        $user = User::where('card_uid', $card_id)->first();

        if ($user) {
            if (hash_equals($token, $texthash)) {
                event(new TapStatusEvent(['card_id' => $card_id, 'token' => $token, 'status' => true]));
                return response()->json(['authenticated' => true]);
            }
        }
        event(new TapStatusEvent(['status' => false]));
        return response()->json(['authenticated' => false]);
    }
}
