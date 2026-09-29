<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Services\MetaConversionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NewsletterController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $request->merge(['email' => $email]);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => $validator->errors()->first('email'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = Subscriber::query()->where('email', $email)->exists();

        return response()->json([
            'success' => true,
            'available' => ! $exists,
            'message' => $exists
                ? 'This email is already subscribed.'
                : 'Email is available.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $request->merge(['email' => $email]);

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('subscribers', 'email'),
            ],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already subscribed.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('email'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $subscriber = Subscriber::create([
            'email' => $email,
        ]);

        app(MetaConversionsService::class)->track(
            'Subscribe',
            $request,
            ['content_name' => 'Email newsletter'],
            ['email' => $subscriber->email, 'external_id' => 'subscriber_'.$subscriber->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Thanks for subscribing!',
            'subscriber' => [
                'id' => $subscriber->id,
                'email' => $subscriber->email,
            ],
        ], 201);
    }
}
