
OBARADAI  HOTEL
予約管理システム

 
OBARADAI HOTEL
1.	概要説明	
2.	仕様	
a.	Homeページ(index.php)	
b.	Informationページ(info.html)	
c.	Planページ(plan.html)	
d.	Accessページ(access.html)	
e.	Reservationページ(reservation.html)	
f.	Contactページ(contact.html)	
g.	admin_dashboard.php	
h.	/api内の諸機能	
•	calbdar.php	
•	calendar_gakusei.php(他のプランについても同様)	
•	reservation.php	
•	reservation.php	
•	db.php	
3.	データベース構築手順	
a.	Inquiriesテーブル	
b.	reservationsテーブル	
c.	room_typeテーブル	
4.	具体例	
5.	備考	

 
1.	概要説明
OBARADAI HOTELのユーザが使うホームページは大きく分けて六つのページで構成されています。
Home: ホテルの紹介と沿革、カレンダーとともに予約状況を表示
Information: ホテルの公共スペース、宿泊部屋の写真を掲載
Plan: 宿泊部屋ごとのプラン紹介
Access: ホテルへのアクセスとホテル内の案内を紹介
Reservation: 宿泊の予約フォーム
Contact: 質問などをホテル側へ問い合わせることができる
さらに、管理者用のページとして現在の予約状況と問い合わせ内容が表示される。

https://ilovejohoko.com/webapp
↑
ここからOBARADAI HOTELのホームページに飛べます
 
2.	仕様
a.	Homeページ(index.php)
•	画面の右側1/4に現在の月とその次の月のカレンダーとともに、ホテルの予約状況を表示している。
•	ホテルの予約状況を表示するために、/api/calneder.phpでデータベーシスから予約状況を取得している。
•	画面上部には画像のスライドショーがある。
•	画面右側にはホテルの紹介、ホテルの主なイベントごと、例えば社長挨拶、開校祭、ホテルのツアー情報が掲載されているサイトへのリンクが紹介されている。また、その下には小原台ホテルの遠隔が紹介されている。
b.	Informationページ(info.html)
•	ホテル内の施設の紹介と各部屋の紹介が掲載されている。
•	このページは特にこれといったphpを用いた機能はない。

c.	Planページ(plan.html)
•	宿泊プランごとの紹介とそのプランへのリンクを掲載
•	プランそれぞれ個別のページでは料金と収容人数を含んだテーブルとHomeページと同じくカレンダーと現在の予約状況を表示している。
•	プランそれぞれのカレンダーでは/api/calnder_(プラン名).phpでカレンダーを表示している。

d.	Accessページ(access.html)
•	ホテルの住所、周辺の地図、各部署の内線番号、周辺の施設
•	このページに特にこれといったphpを用いた機能はない。

e.	Reservationページ(reservation.html)
•	予約の内容をフォームで送信する
•	/api/reservations.phpでフォームを送信し、データベースに挿入

f.	Contactページ(contact.html)
•	問い合わせをフォームで送信する
•	/api/contact.phpでフォームを送信し、データベースに挿入

g.	admin_dashboard.php
1. 管理者認証（ログイン）
ユーザ名とパスワードは以下の通りです。

まず session_start(); を呼び出し、セッションを開始。
その後、$_SESSION['logged_in'] をチェックし、管理者がログインしているかどうかを確認。
もしログインしていない場合、$_SERVER['REQUEST_METHOD'] == 'POST' により、ログインフォームの送信を受け取る。
ユーザー名とパスワードを admin_username（iwak）と admin_password（iwak）と比較し、一致すれば $_SESSION['logged_in'] = true; を設定して認証を通過る。
認証後は header('Location: admin_dashboard.php'); exit(); により、管理画面へリダイレクト。
認証に失敗すると、エラーメッセージ「ユーザー名またはパスワードが正しくありません。」を表示。
この仕組みにより、未認証のユーザーは管理画面にアクセスできず、ログインフォームを経由しないと閲覧できないようになっている。

2. 予約データの取得と表示
管理者ログイン後、データベースに接続するために require 'api/db.php'; を実行し、getDbConnection(); を使ってデータベース接続を確立。
その後、reservations テーブルから最新の予約データを取得するために以下のSQLを実行。
SELECT * FROM reservations ORDER BY created_at DESC
予約データは created_at（作成日時）の降順（新しいものから順に表示）で取得。
fetch_assoc() を使って、1件ずつ配列として取得し、HTMLの <table> 内で表示。
テーブルの各列には、予約情報（ID、名前、メール、電話番号、チェックイン・チェックアウト日、宿泊人数、部屋タイプ、リクエスト、作成日時）が表示。
3. 問い合わせデータの取得と表示
inquiries テーブルから最新の問い合わせデータを取得するために以下のSQLを実行。
SELECT * FROM inquiries ORDER BY created_at DESC
予約データと同様、created_at（作成日時）の降順で取得。
問い合わせデータの各行には、ID、名前、メールアドレス、メッセージ、作成日時が表示。

4. 予約や問い合わせの削除機能
各データ行には削除リンクがついており、クリックすると delete.php にリクエストが送信される。
予約の削除リンク：
<a href="delete.php?type=reservation&id=<?php echo $row['id']; ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
問い合わせの削除リンク：
<a href="delete.php?type=inquiry&id=<?php echo $row['id']; ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
delete.php?type=reservation&id=1 のように、削除対象の type（予約または問い合わせ）と id（削除するデータのID）をURLパラメータとして送信。
onclick="return confirm('本当に削除しますか？');" によって、削除前に確認ダイアログが表示されるため、誤操作を防ぐことができる。

5. ログアウト機能
ダッシュボードの右上に logout.php へのリンクがあり、クリックするとログアウトできます。
<a href="logout.php" class="logout">ログアウト</a>
logout.php では session_destroy(); を実行し、ログイン情報を消去して管理画面から強制的にログアウトさせる。


h.	/api内の諸機能
•	calbdar.php
1. データベース接続
equire 'db.php'; で getDbConnection() を呼び出して、データベースに接続
2. 部屋の情報を定義
各部屋タイプ（学生部屋・指導官室・乾燥室・服務室）と、それぞれの総数を $roomTypes 配列で定義
3. 表示する月の計算
現在の year と month を取得し、次の月 (nextMonth) も計算
4. 日付範囲の決定
今月と次月の 開始日 ($startDate) と 終了日 ($endDate) を取得
5. データベースから予約情報を取得
reservations テーブルから 該当する2ヶ月間の予約データ を取得。
6. 空き状況の計算
初期化: $availability 配列に、すべての日付の空き部屋数を 最大部屋数 で初期化。
予約データを処理: 各予約をループし、チェックイン日からチェックアウト日の前日まで 各日の空き部屋数を減算。
部屋の上限を超えてマイナスにならないように調整。
7. カレンダーのHTMLを生成
generateCalendar() 関数で、指定された year と month のカレンダーを作成。
日ごとの空き状況を判定:
⚪︎ (空室あり) → 残り部屋数が 総数の75%以上
△ (残り少し) → 総数の25%〜75%
× (満室) → 総数の25%以下
HTML/CSSのクラス (available, limited, full) を適用し、視覚的に空き状況を表現。
8. 最終出力
$calendarHtml に 今月と来月のカレンダーHTMLを生成 し、echo で出力。
•	calendar_gakusei.php(他のプランについても同様)
1. データベース接続
require 'db.php'; を使い、getDbConnection() を呼び出してデータベースに接続。
2. 対象の部屋タイプを設定
学生部屋 のみを対象とし、最大10部屋ある設定になっている。
3. 表示する月の決定
URLパラメータ year、month を取得し、指定がない場合は現在の年月を使用。
4. データベースから学生部屋の予約情報を取得
reservations テーブルから 学生部屋の予約情報を取得 し、該当月の予約を抽出。
SQLで checkin_date と checkout_date を使って、予約が該当月と被るかを判定。
5. 日ごとの空き状況を計算
初期化: $availability 配列に全日程の空き部屋数を 最大10 で初期化。
予約データをループ処理し、各日の空き部屋数を減算。
チェックアウト日は空きとしてカウントしない（チェックアウト日は次の人が利用可能）。
6. カレンダーのHTMLを生成
plan_gakusei.php へ移動できる年月選択フォームを設置。
generateCalendar() を使わず、直接ループ処理でHTMLを作成。
視覚的な空き状況表示:
空きあり (⚪︎) → 4部屋以上
わずか (△) → 1〜3部屋
満室 (×) → 0部屋
•	reservation.php
1.  db.php を読み込み、データベース接続を確立

2. POSTリクエストからユーザーの入力データ（氏名、メールアドレス、電話番号、チェックイン・チェックアウト日、宿泊人数、部屋タイプ、リクエスト）を取得し、空白や無効な値を防ぐためにバリデーションを行う。バリデーションでは、氏名や電話番号が空でないこと、メールアドレスが正しい形式であること、チェックイン・チェックアウト日が適切であること、宿泊人数が1人以上であること、指定された部屋タイプが有効なものであることなどをチェック。

3.予約可能な部屋数を確認するために、関数 getConfirmedRooms を使って reservations テーブルから、指定された期間にすでに予約されている部屋数を取得。同時に、関数 getTotalRooms を使って room_types テーブルから、その部屋タイプの総部屋数を取得。この2つの関数の結果を比較し、既存の予約数と総部屋数を照らし合わせ、新たに予約できるかどうかを判断。もし予約済みの部屋数と新たな予約分を加えた数が総部屋数を超えてしまう場合、満室としてエラーメッセージを表示し、予約を受け付けないようにする。

4. 予約が可能な場合、データベースに予約情報を挿入。これには mysqli の prepare を用いてSQLインジェクション対策を施した INSERT クエリを実行。

5. 予約が正常に完了すると、予約完了のHTMLを表示し、ユーザーに成功を知らせます。一方、エラーが発生した場合は適切なエラーメッセージを出力し、予約フォームへ戻る。

6. スクリプトはエラーハンドリングも行っており、データベース接続やSQLクエリの失敗時に Exception をキャッチし、適切なメッセージを表示して予期しない動作を防ぐ。
•	reservation.php
1. require 'db.php'; によって、データベース接続のための外部ファイルを読み込み、getDbConnection() を実行して接続を確立し、関数は mysqli のオブジェクトを返し、データベース操作を可能にする。
2. $_SERVER['REQUEST_METHOD'] === 'POST' によって、スクリプトがPOSTリクエストで実行された場合のみ処理を、$_POST を使って item1（氏名）、item2（メールアドレス）、item3（メッセージ）を取得。未入力の場合は空文字列を設定。
3. 取得したデータは htmlspecialchars() を使ってサニタイズ。これにより、HTMLタグがエスケープされ、XSS（クロスサイトスクリプティング）攻撃を防ぐことができる。
4. 関数 prepare を使用してSQLインジェクション対策を行いながら、データベースに保存。INSERT INTO inquiries (name, email, message) VALUES (?, ?, ?) というSQLを用いて、取得した item1（氏名）、item2（メールアドレス）、item3（メッセージ）を bind_param("sss", $item1, $item2, $item3); でバインドし、実行。
5. データの挿入が成功すると、HTMLを出力し、送信完了ページを表示。画面には「お問い合わせありがとうございました！」というメッセージが表示。
6. 送信完了ページには JavaScript の setInterval() を使ったカウントダウンが組み込まれている。ページが表示されると、5秒間のカウントダウンが開始され、終了すると window.location.href = '/webapp/finish.html'; によって、指定のページ (finish.html) へリダイレクトされる。
7. データベース接続やSQL実行時にエラーが発生した場合、Exception をキャッチし、エラーメッセージを表示する。また、SQLの prepare や execute に失敗した場合にも適切なエラーメッセージを表示。
8. 最後に $mysqli->close(); を実行し、データベース接続を閉じてリソースを解放。
•	db.php
1. グローバル変数の使用
global $host, $dbname, $user, $password; を使用し、関数内でスクリプトの冒頭で定義されたデータベース接続情報を利用。
2. mysqli を使ってデータベースに接続
new mysqli($host, $user, $password, $dbname); を実行し、データベース接続を確立。
3. 接続エラーチェック
if ($mysqli->connect_error) を使い、接続に失敗した場合は die("データベース接続に失敗しました: " . $mysqli->connect_error); を実行し、エラーメッセージを表示してスクリプトを終了させる。
4. 文字セットを utf8mb4 に設定
if (!$mysqli->set_charset("utf8mb4")) によって、データベースの文字セットを utf8mb4 に設定
5. 接続情報を返す
return $mysqli; により、接続済みの mysqli オブジェクトを返す。これにより、呼び出し元のスクリプトで getDbConnection() を実行すると、データベースに接続された mysqli インスタンスを取得できる。

3.	データベース構築手順
a.	Inquiriesテーブル
問い合わせフォームの内容を格納するためのテーブル
CREATE TABLE inquiries (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
このコマンドで作成する。

INSERT INTO inquiries (name, email, message)
VALUES ('山田太郎', 'taro.yamada@example.com', '問い合わせ内容のテストです。');
このコマンドでレコード登録する。

b.	reservationsテーブル
予約フォームの内容を格納するためのテーブル
CREATE TABLE reservations (
    id INT NOT NULL AUTO_INCREMENT,
    your_name VARCHAR(255) NOT NULL,
    your_email VARCHAR(255) NOT NULL,
    your_phone VARCHAR(50) NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE DEFAULT NULL,
    guest_count INT NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    requests TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    room_count INT DEFAULT 1,
    PRIMARY KEY (id)
);
このコマンドで作成する。
INSERT INTO reservations (your_name, your_email, your_phone, checkin_date, checkout_date, guest_count, room_type, requests, room_count)
VALUES ('山田太郎', 'taro.yamada@example.com', '090-1234-5678', '2024-02-10', '2024-02-15', 2, '学生部屋', '静かな部屋を希望', 1);
このコマンドでレコード登録する。

c.	room_typeテーブル
宿泊部屋のタイプと収容人数を格納するためのテーブル
CREATE TABLE room_types (
    room_type VARCHAR(255) NOT NULL,
    total_rooms INT NOT NULL,
    PRIMARY KEY (room_type)
);
このコマンドで作成する。
INSERT INTO room_types (room_type, total_rooms)
VALUES ('学生部屋', 10);
このコマンドでレコード登録する。
 
4.	具体例

スライドショーの下にメインの六つのページを選択できる。
左のカレンダーが、ホテルの予約状況。右側がホテルについての紹介。

ホテルの公共施設と宿泊部屋の写真と説明。

宿泊プランの選択

ホテルへのアクセス方法と周辺地図。
予約送信フォーム

質問等の問い合わせフォーム

管理者ダッシュボードの画面
 
5.	備考
•	現在1つの予約で1部屋しか確保しない仕様になっているため、複数の部屋を同時に予約できるように拡張する。
•	予約確認メールを送信する機能を追加する。
•	API化してフロントエンドから利用しやすくすることで、より利便性の高い予約システムにする。

