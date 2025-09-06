<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDivisionRequest;
use App\Http\Requests\Admin\UpdateDivisionRequest;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DivisionController extends Controller
{
    /**
     * 部署の一覧を表示します。
     * 通知先も一緒に取得して、N+1問題を回避します。
     */
    public function index()
    {
        $divisions = Division::with('notificationDestinations')
            ->withCount('users')->latest()->paginate(15);
        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * 新規作成フォームを表示します。
     */
    public function create()
    {
        return view('admin.divisions.create');
    }

    /**
     * 新しい部署と、それに紐づく通知先を、データベースに保存します。
     */
    public function store(StoreDivisionRequest $request)
    {
        $validated = $request->validated();

        // [魔法] トランザクションを開始します。
        // これにより、部署の作成と、通知先の作成が、必ずセットで成功するか、セットで失敗するようになります。
        DB::transaction(function () use ($validated, $request) {
            // 1. まず、親である「部署」を作成します。
            $division = Division::create(['name' => $validated['name']]);

            // 2. 次に、フォームから送信されたメールアドレスの配列をループ処理します。
            if (!empty($request->emails)) {
                foreach ($request->emails as $email) {
                    // 空の入力欄は無視します。
                    if (!empty($email)) {
                        // 3. 部署とのリレーションシップを使って、子である「通知先」を作成します。
                        $division->notificationDestinations()->create(['email' => $email]);
                    }
                }
            }

            $avatarPath = null;
            // もし、リクエストに'logo'という名前のファイルが含まれていたら…
            if ($request->hasFile('logo')) {
                // ファイルを 'public' ディスクの 'logos' フォルダに、ユニークな名前で保存し、そのパスを取得
                $avatarPath = $request->file('logo')->store('logos', 'public');
            }
            // 4. アバター画像のパスが存在する場合、部署のアバター属性を更新
            if ($avatarPath) {
                $division->update(['logo' => $avatarPath]);
            }
        });

        return redirect()->route('admin.divisions.index')->with('success', '新しい部署を登録しました。');
    }

    /**
     * 指定された部署の詳細を表示します。
     */
    public function show(Division $division)
    {
        // 部署に紐づく通知先を、ここで読み込んでおくと、より確実です。
        $division->load('notificationDestinations');
        return view('admin.divisions.show', compact('division'));
    }

    /**
     * 既存の部署を編集するためのフォームを表示します。
     */
    public function edit(Division $division)
    {
        $division->load('notificationDestinations');
        return view('admin.divisions.edit', compact('division'));
    }

    /**
     * 既存の部署と、それに紐づく通知先を、更新します。
     */
    public function update(UpdateDivisionRequest $request, Division $division)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $division) {
            // 1. まず、部署名そのものを更新します。
            $division->update(['name' => $validated['name']]);

            // 2. この部署に紐づく、既存の通知先を、一度「全て」削除します。
            $division->notificationDestinations()->delete();

            // 3. そして、フォームから新しく送信されたメールアドレスを、改めて登録し直します。
            if (!empty($request->emails)) {
                foreach ($request->emails as $email) {
                    if (!empty($email)) {
                        $division->notificationDestinations()->create(['email' => $email]);
                    }
                }
            }

            // 4. ロゴ画像の更新処理
            if ($request->hasFile('logo')) {
                if ($division->logo_path) {
                    Storage::disk('public')->delete($division->logo);
                }
                $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
            }
            $division->update($validated);
        });

        return redirect()->route('admin.divisions.index')->with('success', '部署情報を更新しました。');
    }

    /**
     * 部署を削除します。
     * マイグレーションで onDelete('cascade') を設定しているので、関連する通知先も自動で削除されます。
     */
    public function destroy(Division $division)
    {
        $division->delete();
        return redirect()->route('admin.divisions.index')->with('success', '部署を削除しました。');
    }
}
