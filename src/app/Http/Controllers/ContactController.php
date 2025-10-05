<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('contact', compact('categories'));
    }

    public function confirm(ContactRequest $request)
    {
        $contacts = $request->all();
        $category = Category::find($request->category_id);
        return view('confirm', compact('contacts', 'category'));
    }

   public function store(ContactRequest $request)
    {
        if ($request->has('back')) {
            return redirect('/')->withInput();
        }

        $request['tel'] = $request->tel_1 . $request->tel_2 . $request->tel_3;
        Contact::create(
            $request->only([
                'category_id',
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'building',
                'detail'
            ])
        );

        return view('thanks');
    }

    // 管理画面
    public function admin() {
        $contacts = Contact::with('category')->paginate(7);
        $categories = Category::all();
        $csvData = Contact::all();
         return view('admin', compact('contacts', 'categories', 'csvData'));
    }

    // 検索処理
    public function search(Request $request) 
    {
        // フォームの「リセットボタン」が押された場合,reset という名前の input が送られているかチェック
        if ($request->has('reset')) {

            // /admin にリダイレクトして、前の入力値を保持する（withInput()）
            return redirect('/admin')->withInput();
        }

        // Contact モデルを使って 検索用のクエリビルダーを作る
        $query = Contact::query();

        $query = getSearchQuery($request, $query);

        // 絞り込まれた条件の結果を 1ページ7件ずつに分けて取得
        $contacts = $query->paginate(7);

        // 同じ $query を使って 条件に一致する全件データを取得。paginate() は「ページ分だけ」しか取れないので、CSV用には get() で全件取得
        $csvData = $query->get();

        // <select> に表示するための全カテゴリを取得
        $categories = Category::all();
        // Blade にデータを渡して表示する
        return view('admin', compact('contacts', 'categories', 'csvData'));

    }

    // 検索条件を $query にセットするための条件を書く。DBへの問い合わせはまだ行われない
    // getSearchQuery()の理由「検索条件のクエリビルダーを返すだけの補助メソッド」 だから
    public function getSearchQuery($request, $query) 
    {
        // if(!empty(...)) は「キーワードが空じゃない場合だけ検索条件を追加する」という意味
        if(!empty($request->keyword)) {
            $query->where(function ($q) use ($request){
                // first_name にキーワードを含む
                $q->where('first_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%');
                    // % は 部分一致 を意味するワイルドカード
                    // %keyword% → 前後に何があっても一致する
                    // orWhere は「いずれか1つでも一致すればOK」という意味
            });
        }

        if (!empty($request->gender)) {
            $query->where('gender', '=', $request->gender);
        }

        if (!empty($request->category_id)) {
            $query->where('category_id', '=', $request->category_id);
        }

        if (!empty($request->data)) {
            $query->whereDate('created_at', '=', $request->date);
        }

        return $query;
    }

    // 削除処理
    public function delete(Request $request)
    {
        // find() は 主キー（通常は id）で1件のレコードを取得する
        // 今回は $request->id に対応するレコードを 1件だけ DBから取り出し存在しない場合は null が返る
        Contact::find($request->id)->delete();
        // 削除後に管理画面にリダイレクト。ユーザーが削除後も管理画面を見られるようにする
        return redirect('/admin');
    }

    public function export(Request $request)
    {
        // Contact モデルから クエリビルダーを作る
        // $query は「どういう条件でデータを取るか」を構築するためのオブジェクト
        $query = Contact::query();

        // $this は 現在のクラスのインスタンス=ContactController
        // getSearchQuery() を呼び出して、検索条件を $query にセットして返す」という意味。
        // $request → フォームから送られてきた検索条件（キーワード、性別、カテゴリなど）
        // $query → まだ条件を加える前の Eloquentクエリビルダー
        // getSearchQuery() 内で $query に検索条件を追加：
        $query = $this->getSearchQuery($request, $query);

        // $csvData が CSVに出力する対象データ
        //get() の意味：$query に組み込まれた条件で 実際にDBに問い合わせ
        // 条件に合致する全レコードを取得 結果は コレクション（Collection） として返る
        // ->toArray() の意味：コレクションを 単純な配列 に変換する
        $csvData = $query->get()->toArray();

        // Exceで開いたときに1行目 が「列名（ヘッダー）」になる。$csvHeader はまさにこの 1行目の列名 を配列で定義している部分です。
        $csvHeader = [
            'id', 'category_id', 'first_name', 'last_name', 'gender', 'email', 'tell', 'address', 'building', 'detail', 'created_at', 'updated_at'
        ];

        // StreamedResponse：Laravel に 最初から用意されているクラス
        // StreamedResponse クラスの 新しいインスタンス（オブジェクト） を作る
        // function () use ($csvHeader, $csvData)：無名関数（クロージャ）名前のない関数をその場で定義して使う
        // function () { ... }だけだと $csvHeader や $csvData が中で使えないので use で渡している
        // $csvHeader → CSVの1行目（列名）$csvData → DBから取得した検索結果の配列（エクスポートするデータ）
        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            // fopen() は「ファイルを開く」PHPの関数
            // 'php://output' → 「ファイルではなくブラウザに直接出力する」特別なパス
            // 'w' → 書き込みモード
            // $createCsvFile は 「CSVを書き込むための仮想ファイル」 で、書き込むとそのままブラウザに送られる
            $createCsvFile = fopen('php://output', 'w');

            // mb_convert_variables()：文字コードを変換するPHP関数
            // Excelで開くときに日本語が文字化けしないように：'UTF-8' → 'SJIS-win' に変換
            // 対象は $csvHeader（列名）だけ
            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);

            // fputcsv()：配列を CSV形式の1行として書き込む
            // $createCsvFile に書き込む → この場合は php://output なのでブラウザに直接送られる　$csvHeader 配列の要素がカンマ区切りで1行になる
            fputcsv($createCsvFile, $csvHeader);


            // $csvData は 条件に一致した全レコードの配列（get()->toArray()で取得済み）foreach で 1件ずつ取り出す
            foreach ($csvData as $csv) {

                // 目的：CSVに書き込むとき、タイムゾーンや形式を整えて日付を変換
                // Date::make($csv['created_at']) → Carbonインスタンスに変換
                // setTimezone('Asia/Tokyo') → 日本時間に変換
                // format('Y/m/d H:i:s') → "2025/10/05 16:30:00" のような文字列に変換
                $csv['created_at'] = Carbon::parse($csv['created_at'])->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s');
                $csv['updated_at'] = Carbon::parse($csv['updated_at'])->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s');

                mb_convert_variables('SJIS-win', 'UTF-8', $csv);
                
                fputcsv($createCsvFile, $csv);
                // fputcsv($createCsvFile, $csv);$csv 配列の値を CSV形式の1行 にして $createCsvFile に書き込む
                // $createCsvFile は php://output なので、書き込むたびに ブラウザに送信される
            }
            // fopen('php://output', 'w') で開いたストリームを 閉じる
            fclose($createCsvFile);


            // . 200：HTTPステータスコード
            // ブラウザやサーバーに「リクエストは成功しました」と伝える
            // 200 は「成功」の意味で、普通のページ表示やファイルダウンロードでも使う
        }, 200, [
            // Content-Type：ブラウザに 送信されるデータの種類 を知らせる
            // 'text/csv' → 「これは CSV ファイルです」とブラウザに認識させる
            // これでブラウザが「テキストとして表示する」か「CSVとして扱う」かを判断する
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts.csv"',
            // Content-Disposition：ファイルの扱い方をブラウザに指示する
            // 'attachment; filename="contacts.csv"' → 「ダウンロード用ファイル」として扱い、ダウンロード時の ファイル名を contacts.csv にする
        ]);

        // ブラウザに CSVファイルをダウンロードさせる
        // foreach 内で書き込まれたデータが 1行ずつ送られる（ストリーム配信）
        return $response;
    }
}