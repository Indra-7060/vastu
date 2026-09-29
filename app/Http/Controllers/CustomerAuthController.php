<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MetaConversionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('frontend.pages.login');
    }

    public function showRegister(): View
    {
        return view('frontend.pages.signup');
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('role', 'customer')
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($credentials['password'], $user->password)) {
            $message = 'These credentials do not match our records.';

            if ($request->expectsJson() || $request->ajax()) {
                throw ValidationException::withMessages(['email' => $message]);
            }

            return back()->withInput($request->only('email'))->withErrors(['email' => $message]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        app(\App\Services\CartService::class)->mergeSessionIntoUser($user);

        $redirect = route('account', 'overview');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Signed in successfully.',
                'redirect' => $redirect,
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect()->intended($redirect);
    }

    public function register(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'is_active' => true,
            'login_provider' => 'email',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        app(\App\Services\CartService::class)->mergeSessionIntoUser($user);

        $names = preg_split('/\s+/', trim($user->name), 2) ?: [];
        app(MetaConversionsService::class)->track(
            'CompleteRegistration',
            $request,
            ['content_name' => 'Customer account', 'status' => true],
            [
                'email' => $user->email,
                'first_name' => $names[0] ?? null,
                'last_name' => $names[1] ?? null,
                'external_id' => $user->id,
            ]
        );

        $redirect = route('account', 'overview');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully.',
                'redirect' => $redirect,
                'user' => $this->userPayload($user),
            ]);
        }

        return redirect($redirect);
    }

    public function checkEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $exists = User::query()
            ->where('email', $data['email'])
            ->when(Auth::check(), fn ($q) => $q->where('id', '!=', Auth::id()))
            ->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'This email is already registered.' : 'Email is available.',
        ]);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        // Only end the customer session; an admin login in the same browser stays intact.
        Auth::guard('web')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('home'),
            ]);
        }

        return redirect()->route('home');
    }

    private function userPayload(User $user): array
    {
        $parts = preg_split('/\s+/', trim($user->name), 2);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'firstName' => $parts[0] ?? $user->name,
            'lastName' => $parts[1] ?? '',
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ];
    }
}
