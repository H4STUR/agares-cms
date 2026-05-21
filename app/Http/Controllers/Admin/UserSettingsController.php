<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;


class UserSettingsController extends Controller
{
    public function profile(User $user)
    {
        $viewer = auth()->user();

        if (!Setting::bool('public_profiles')) {
            // Private profiles: only the owner or an admin may view
            if (!$viewer) {
                return redirect()->route('login');
            }
            if ($viewer->id !== $user->id && !$viewer->can('view admin panel')) {
                abort(403);
            }
        }

        // Admins see the admin layout unless they explicitly request the public view
        if ($viewer?->can('view admin panel') && !request()->has('view')) {
            return view('pages.admin.users.profile', compact('user'));
        }

        return view('pages.user.profile', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorizeOwnerOrAdmin($user);

        if (auth()->user()->can('view admin panel')) {
            return view('pages.admin.users.settings', compact('user'));
        }

        return view('pages.user.settings', compact('user'));
    }

    public function orders(User $user)
    {
        $this->authorizeOwnerOrAdmin($user);
        return view('pages.user.orders', compact('user'));
    }

    public function favorites(User $user)
    {
        $this->authorizeOwnerOrAdmin($user);
        return view('pages.user.favorites', compact('user'));
    }

    public function tracking(User $user)
    {
        $this->authorizeOwnerOrAdmin($user);
        return view('pages.user.tracking', compact('user'));
    }

    public function invoices(User $user)
    {
        $this->authorizeOwnerOrAdmin($user);
        return view('pages.user.invoices', compact('user'));
    }

    public function returns(User $user)
    {
        $this->authorizeOwnerOrAdmin($user);
        return view('pages.user.returns', compact('user'));
    }

    private function authorizeOwnerOrAdmin(User $user): void
    {
        $viewer = auth()->user();
        if (!$viewer || ($viewer->id !== $user->id && !$viewer->can('manage users'))) {
            abort(403);
        }
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeOwnerOrAdmin($user);

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'description' => 'nullable|string|max:1000',
                'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
                'background_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
                'remove_avatar' => 'nullable|boolean',
                'remove_background' => 'nullable|boolean',
            ]);

            // Delete old avatar if checkbox is checked
            if ($request->filled('remove_avatar') && $user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
                $validated['avatar'] = null;
            }

            // Delete old background if checkbox is checked
            if ($request->filled('remove_background') && $user->background_image && Storage::exists($user->background_image)) {
                Storage::delete($user->background_image);
                $validated['background_image'] = null;
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $avatarPath;
            }

            // Handle background upload
            if ($request->hasFile('background_image')) {
                $bgPath = $request->file('background_image')->store('backgrounds', 'public');
                $validated['background_image'] = $bgPath;
            }

            $user->update($validated);

            DB::commit();

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('An error occurred while updating profile: ' . $e->getMessage())->withInput();
        }
    }


    public function updatePassword(Request $request, User $user)
    {
        $this->authorizeOwnerOrAdmin($user);

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            DB::commit();

            return back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Failed to update password: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);

            $user = $request->user();

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            DB::commit();

            return Redirect::to('/');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Failed to delete account: ' . $e->getMessage());
        }
    }
}
