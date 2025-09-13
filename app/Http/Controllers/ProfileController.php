<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
     public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // バリデーション済みのデータを取得
        $validated = $request->validated();

        // ユーザーインスタンスを取得
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // 既存の画像があれば削除する
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // 新しい画像を 'avatars' フォルダに保存し、パスを取得
            $path = $request->file('avatar')->store('avatars', 'public');

            // バリデーション済みデータに画像のパスを追加
            $validated['avatar_path'] = $path;
        }

        // ユーザー情報を更新
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
