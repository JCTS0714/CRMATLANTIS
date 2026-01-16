<?php

namespace App\Http\Controllers;

use App\Models\EmailUnsubscribe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailUnsubscribeController extends Controller
{
    public function unsubscribe(Request $request): Response
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string', 'size:64'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $token = (string) $validated['token'];

        if (!hash_equals($this->tokenForEmail($email), $token)) {
            return response()->view('email.unsubscribe_invalid', [], 422);
        }

        EmailUnsubscribe::query()->updateOrCreate(
            ['email' => $email],
            [
                'unsubscribed_at' => Carbon::now(),
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return response()->view('email.unsubscribed');
    }

    private function tokenForEmail(string $email): string
    {
        $secret = (string) config('app.key');
        return hash_hmac('sha256', strtolower(trim($email)), $secret);
    }
}
