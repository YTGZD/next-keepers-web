<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEarlyAccessRequest;
use App\Models\EarlyAccessSignup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EarlyAccessController extends Controller
{
    public function store(StoreEarlyAccessRequest $request): JsonResponse|RedirectResponse
    {
        // Bots fill the hidden "website" field. Pretend it worked and store nothing.
        if (filled($request->input('website'))) {
            return $this->success($request);
        }

        $data = $request->validated();

        // The same email signing up again updates the record instead of creating a duplicate,
        // and the response is identical so the form never reveals who is already on the list.
        EarlyAccessSignup::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? null,
                'interest' => $data['interest'],
                'source' => $this->cleanSource($request->query('utm_source') ?? $request->input('utm_source')),
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'consented_at' => now(),
            ],
        );

        return $this->success($request);
    }

    private function success(StoreEarlyAccessRequest $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'You are on the list.'], 201);
        }

        return redirect(route('home').'#early-access')->with('signup', 'success');
    }

    private function cleanSource(mixed $value): ?string
    {
        return is_string($value) && $value !== ''
            ? mb_substr(preg_replace('/[^A-Za-z0-9._-]/', '', $value), 0, 80)
            : null;
    }
}
