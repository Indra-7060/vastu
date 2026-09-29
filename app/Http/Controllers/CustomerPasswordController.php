<?php

namespace App\Http\Controllers;

use App\Mail\CustomerPasswordResetMail;
use App\Models\PasswordResetAttempt;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerPasswordController extends Controller
{
    private const MAX_ATTEMPTS_PER_DAY = 2;

    public function showForgotForm(): View
    {
        return view('frontend.pages.forgot-password');
    }

    public function sendResetLink(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $email = strtolower(trim($data['email']));

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->where('is_active', true)
            ->first();

        if (! $user) {
            $message = 'This email is not registered with us.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['email' => [$message]],
                ], 422);
            }

            return back()->withInput(['email' => $email])->withErrors(['email' => $message]);
        }

        $attempts = PasswordResetAttempt::countForEmailInLastDay($email);
        if ($attempts >= self::MAX_ATTEMPTS_PER_DAY) {
            $message = 'You have reached the limit of 2 password reset emails in 24 hours. Please try again later.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['email' => [$message]],
                ], 429);
            }

            return back()->withInput(['email' => $email])->withErrors(['email' => $message]);
        }

        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new CustomerPasswordResetMail($user, $token));
        PasswordResetAttempt::record($email, $request->ip());

        $remaining = self::MAX_ATTEMPTS_PER_DAY - PasswordResetAttempt::countForEmailInLastDay($email);
        $message = 'A password reset link has been sent to your email.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'remaining_attempts' => max(0, $remaining),
            ]);
        }

        return back()->with('success', $message);
    }

    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = (string) $request->query('email', '');

        return view('frontend.pages.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function reset(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 6 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $email = strtolower(trim($data['email']));

        $user = User::query()
            ->where('email', $email)
            ->where('role', 'customer')
            ->where('is_active', true)
            ->first();

        if (! $user) {
            $message = 'This reset link is invalid or has expired.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['email' => [$message]],
                ], 422);
            }

            return back()->withInput($request->only('email'))->withErrors(['email' => $message]);
        }

        $status = Password::broker()->reset(
            [
                'email' => $email,
                'password' => $data['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $message = __($status);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['email' => [$message]],
                ], 422);
            }

            return back()->withInput($request->only('email'))->withErrors(['email' => $message]);
        }

        $message = 'Your password has been reset. Please sign in.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('success', $message);
    }
}
