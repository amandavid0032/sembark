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
        $q = Invitation::with('company')->orderBy('id');
        if ($user->role === 'SuperAdmin') {
            $invitations = $q->get();
        } else {
            $invitations = $q->where('company_id', $user->company_id)->get();
        }

        // Companies dropdown is only meaningful for SuperAdmin (who picks which
        // company the new Admin belongs to).
        $companies = $user->role === 'SuperAdmin'
            ? Company::orderBy('id')->get()
            : collect();

        return view('invitations.index', compact('invitations', 'companies'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'email' => 'required|email|unique:users,email|unique:invitations,email',
            'role'  => 'required|string|in:Admin,Member,Sales,Manager',
        ];

        // SuperAdmin must pick a company from the dropdown; Admin's company is implicit.
        if ($user->role === 'SuperAdmin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $request->validate($rules);

        // Policy check: SuperAdmin can only invite Admin, Admin can only invite
        // Member/Sales/Manager. See InvitationPolicy@create.
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

        return view('invitations.accept', compact('invitation'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Edge case: someone already signed up with this email between the time
        // the invite was sent and now. Burn the invitation and send them to login.
        if (User::where('email', $invitation->email)->exists()) {
            $invitation->delete();
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

        // One-shot token -- delete the invitation so the same link can't be reused.
        $invitation->delete();

        Auth::login($user);
        $request->session()->regenerate();

        // dd($user); // <-- handy for debugging when accept was failing silently.

        return redirect('/dashboard')->with('success', 'Welcome aboard, ' . $user->name . '!');
    }
}
