<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Http\Requests\Admin\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\UpdateUserRequest;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 部署(division)の情報も一緒に取得 (N+1問題対策)
        $users = User::with('division')->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $divisions = Division::all(); // 部署の選択肢を渡す
        return view('admin.users.create', compact('divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        // Userモデルを使って、新しいユーザーを作成
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // ★パスワードはHash化して保存
            'division_id' => $validated['division_id'] ?? null,
            'is_admin' => $request->boolean('is_admin'), // チェックボックス対応
        ]);

        // 登録後は、ユーザー一覧ページにリダイレクト
        return redirect()->route('admin.users.index')->with('success', '新しいユーザーを登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // 取得したUserオブジェクトをビューに渡すだけ
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $divisions = Division::all(); // 部署の選択肢も渡す
        return view('admin.users.edit', compact('user', 'divisions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // パスワードが入力されている場合のみ、ハッシュ化して更新データに含める
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // パスワードが空の場合は、更新データから除外する
            unset($validated['password']);
        }

        // is_adminの値を設定（チェックボックス対応 空だとデータが送られてこないので）
        $validated['is_admin'] = $request->boolean('is_admin');

        // ユーザー情報を更新
        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'ユーザー情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'ユーザーを削除しました。');
    }
}
