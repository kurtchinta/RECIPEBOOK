<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the registration data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Default role ID (e.g., 3 = Viewer/User in your system)
        $defaultRoleId = 1;

        // Create the new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $defaultRoleId,
        ]);

        // Trigger Registered event
        event(new Registered($user));

        // Log the user in
        Auth::login($user);

        // Redirect based on role
        return $this->redirectToDashboard($user);
    }

    /**
     * Redirect to the appropriate dashboard based on user role.
     */
    private function redirectToDashboard(User $user): RedirectResponse
    {
        switch ($user->role_id) {
            case 1: // Admin
                return redirect()->route('admin');
            case 2: // Chef
                return redirect()->route('chef');
            case 3: // Viewer/User
            default:
                return redirect()->route('user');
        }
    }
}
