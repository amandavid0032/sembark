<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShortUrlController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Newest first -- people generally want to see the URL they just made.
        $q = ShortUrl::query()->with(['company', 'user'])->orderByDesc('id');

        // Visibility rules from the spec:
        //  - SuperAdmin   -> every short URL across every company
        //  - Admin        -> short URLs in their own company
        //  - everyone else-> their own short URLs only
        if ($user->role === 'SuperAdmin') {
            $urls = $q->get();
        } elseif ($user->role === 'Admin') {
            $urls = $q->where('company_id', $user->company_id)->get();
        } else {
            $urls = $q->where('user_id', $user->id)->get();
        }

        return view('short-urls.index', compact('urls'));
    }

    public function store(Request $request)
    {
        // ShortUrlPolicy@create -- only Admin/Member/Sales/Manager may create.
        if ($request->user()->cannot('create', ShortUrl::class)) {
            return back()->withErrors([
                'role' => 'You are not authorized to create short URLs.',
            ]);
        }

        $request->validate([
            'original_url' => 'required|url',
        ]);

        // Pick a unique short code. Collisions on 8 random chars are very
        // unlikely but we retry just to be safe.
        do {
            $code = Str::random(8);
        } while (ShortUrl::where('short_code', $code)->exists());

        $short = ShortUrl::create([
            'original_url' => $request->original_url,
            'short_code'   => $code,
            'company_id'   => $request->user()->company_id,
            'user_id'      => $request->user()->id,
        ]);

        Log::info('short url created', [
            'id'   => $short->id,
            'code' => $short->short_code,
            'by'   => $short->user_id,
        ]);

        return back()->with('success', 'Short URL created successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $shortUrl = ShortUrl::findOrFail($id);

        // ShortUrlPolicy@delete handles "own only" vs "company-wide" vs SuperAdmin.
        Gate::authorize('delete', $shortUrl);

        $shortUrl->delete();

        return back()->with('success', 'Short URL deleted successfully!');
    }

    // Public route -- /s/{code} resolves without authentication, per the spec.
    public function resolve(Request $request, $code)
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();
        return redirect($shortUrl->original_url);
    }
}
