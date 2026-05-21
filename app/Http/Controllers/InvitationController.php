<?php

namespace App\Http\Controllers;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

// Flow:
//  1. Admin/SuperAdmin POST /invitations  -> we save a row + email a link.
//  2. Invitee clicks the link -> GET /invitations/accept/{token}.
//  3. Invitee submits name+password -> POST /invitations/accept/{token},
//     which creates the user and logs them in.
class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // SuperAdmin sees all invitations; everyone else only sees their own company's.
        $base = Invitation::with(['company', 'acceptedUser']);
        if ($user->role !== 'SuperAdmin') {
            $base->where('company_id', $user->company_id);
        }

        $pending  = (clone $base)->whereNull('accepted_at')->orderBy('id')->get();
        $accepted = (clone $base)->whereNotNull('accepted_at')->orderByDesc('accepted_at')->get();

        // Companies dropdown is only meaningful for SuperAdmin (who picks which
        // company the new Admin belongs to).
        $companies = $user->role === 'SuperAdmin'
            ? Company::orderBy('id')->get()
            : collect();

        return view('invitations.index', compact('pending', 'accepted', 'companies'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // For invitations we only block re-sending while one is still pending.
        // Already-accepted invitations are kept as history but shouldn't block.
        $rules = [
            'email' => [
                'required', 'email',
                'unique:users,email',
                \Illuminate\Validation\Rule::unique('invitations', 'email')->whereNull('accepted_at'),
            ],
            'role'  => 'required|string|in:Admin,Member,Sales,Manager',
        ];

        if ($user->role === 'SuperAdmin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $request->validate($rules);

        // Member/Sales/Manager.
        if ($user->cannot('create', [Invitation::class, $request->role])) {
            return back()
                ->withErrors(['role' => 'You are not authorized to invite a user with this role.'])
                ->withInput();
        }

        $companyId = $user->role === 'SuperAdmin'
            ? (int) $request->company_id
            : $user->company_id;

        $invitation = Invitation::create([
            'company_id' => $companyId,
            'email'      => $request->email,
            'role'       => $request->role,
            'token'      => Str::random(32),
            'invited_by' => $user->id,
        ]);

        // Send the email. We eager-load company so the email view can show it.
        $invitation->load('company');

        // TODO: queue this in production -- right now it blocks the request.
        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        Log::info('invitation sent', [
            'email' => $invitation->email,
            'role'  => $invitation->role,
            'by'    => $user->id,
        ]);

        // var_dump($invitation->toArray());
        // die();

        return back()->with('success', 'Invitation email sent to ' . $invitation->email . '.');
    }

    public function showAccept(Request $request, string $token)
    {
        $invitation = Invitation::with('company')->where('token', $token)->firstOrFail();

        // If someone (e.g. the inviter testing the link) is already logged in,
        // kick them out so they can sign up as the new user.
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // var_dump($invitation->toArray());
        // die();

        return view('invitations.accept', compact('invitation'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        // If this invite was already accepted, the link is dead.
        if ($invitation->isAccepted()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This invitation has already been accepted. Please log in.']);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (User::where('email', $invitation->email)->exists()) {
            // We don't delete the invitation here -- it gets marked accepted below
            // only when a user is actually created. Just bounce them to login.
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'An account with this email already exists. Please log in.']);
        }

        $user = User::create([
            'name'       => $request->name,
            'email'      => $invitation->email,
            'password'   => Hash::make($request->password),
            'role'       => $invitation->role,
            'company_id' => $invitation->company_id,
        ]);

        // Mark invite as accepted (kept for history; don't delete).
        $invitation->update([
            'accepted_at'      => now(),
            'accepted_user_id' => $user->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Welcome aboard, ' . $user->name . '!');
    }
}
