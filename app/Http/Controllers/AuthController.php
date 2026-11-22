<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BuyBoxMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private readonly BuyBoxMatchingService $matchingService
    ) {}

    /** Sign-in and sign-up live in the site-wide modal; these URLs just open it. */
    public function showLogin(): RedirectResponse
    {
        return redirect()->route('home', ['auth' => 'login']);
    }

    public function showRegister(): RedirectResponse
    {
        return redirect()->route('home', ['auth' => 'register']);
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:160'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return $this->failed($request, 'login', 'That email and password do not match.');
        }

        $request->session()->regenerate();

        return $this->succeeded($request, Auth::user(), 200);
    }

    public function register(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:200'],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => Str::lower(trim($data['email'])),
            'password' => $data['password'], // hashed by the model cast
            'membership_tier' => 'Free account',
            'member_since' => now()->format('F Y'),
            'nda_status' => 'No NDA on file',
        ]);

        $this->matchingService->getOrCreateBuyBox($user);

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->succeeded($request, $user, 201);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function succeeded(Request $request, User $user, int $status): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                'redirect' => route('dashboard'),
            ], $status);
        }

        return redirect()->intended(route('dashboard'));
    }

    private function failed(Request $request, string $mode, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => ['email' => [$message]]], 422);
        }

        return back()
            ->withErrors(['email' => $message])
            ->withInput($request->only('email', 'name') + ['_auth_mode' => $mode]);
    }
}
