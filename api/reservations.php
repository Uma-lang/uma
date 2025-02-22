<?php
// エラー表示設定（デバッグ用）
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// データベース接続ファイルの読み込み
require 'db.php';

try {
    //echo "データベース接続開始<br>";
    // データベース接続
    $mysqli = getDbConnection();
    //echo "データベース接続成功<br>";

    // POSTデータの取得
    $your_name = trim($_POST['your_name'] ?? '');
    $your_email = trim($_POST['your_email'] ?? '');
    $your_phone = trim($_POST['your_phone'] ?? '');
    $checkin_date = trim($_POST['checkin_date'] ?? '');
    $checkout_date = trim($_POST['checkout_date'] ?? '');
    $guest_count = (int)($_POST['guest_count'] ?? 0);
    $room_type = trim($_POST['room_type'] ?? '');
    $requests = trim($_POST['requests'] ?? '');

    //echo "POSTデータ取得完了<br>";

    // バリデーション用
    $errors = [];

    // バリデーション
    if ($your_name === '') {
        $errors[] = '名前が空。';
    }
    if (!filter_var($your_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'メールアドレスが不正。';
    }
    if ($your_phone === '') {
        $errors[] = '電話番号が空。';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkin_date) || !strtotime($checkin_date)) {
        $errors[] = 'チェックイン日が不正。';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkout_date) || !strtotime($checkout_date)) {
        $errors[] = 'チェックアウト日が不正。';
    }
    if (strtotime($checkout_date) <= strtotime($checkin_date)) {
        $errors[] = 'チェックアウト日がチェックイン日より前(同日含む)はダメ。';
    }
    if ($guest_count <= 0) {
        $errors[] = '宿泊人数は1人以上にして。';
    }
    $valid_room_types = ['学生部屋', '指導官室', '乾燥室', '服務室'];
    if (!in_array($room_type, $valid_room_types, true)) {
        $errors[] = '有効な部屋タイプを選んで。';
    }

    //echo "バリデーション完了<br>";

    // バリデーションエラーがあれば表示して終了
    if (!empty($errors)) {
        echo '<h2>予約エラー</h2>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        echo '</ul>';
        echo '<p><a href="/webapp/reservation.html">戻る</a></p>';
        exit;
    }

    // --- 部屋数チェック用の関数 ---
    function getConfirmedRooms(mysqli $mysqli, string $room_type, string $checkin_date, string $checkout_date): int {
        //echo "部屋数確認クエリ開始<br>";
        $sql = "
            SELECT COALESCE(SUM(room_count), 0) AS confirmed_rooms
            FROM reservations
            WHERE room_type = ?
              AND NOT (checkout_date <= ? OR checkin_date >= ?)
        ";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("空き状況確認クエリの準備に失敗: " . $mysqli->error);
        }
        $stmt->bind_param("sss", $room_type, $checkin_date, $checkout_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        //echo "部屋数確認クエリ完了: confirmed_rooms = " . ($row['confirmed_rooms'] ?? 0) . "<br>";
        return (int)($row['confirmed_rooms'] ?? 0);
    }

    function getTotalRooms(mysqli $mysqli, string $room_type): int {
        //echo "総部屋数取得クエリ開始<br>";
        $sql = "SELECT total_rooms FROM room_types WHERE room_type = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("総部屋数確認クエリの準備に失敗: " . $mysqli->error);
        }
        $stmt->bind_param("s", $room_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        //echo "総部屋数取得クエリ完了: total_rooms = " . ($row['total_rooms'] ?? 0) . "<br>";
        return (int)($row['total_rooms'] ?? 0);
    }

    //echo "部屋数取得開始<br>";
    // 予約済みの部屋数を取得
    $confirmed_rooms = getConfirmedRooms($mysqli, $room_type, $checkin_date, $checkout_date);

    // 部屋の総数を取得
    $total_rooms = getTotalRooms($mysqli, $room_type);

    // 今回の予約で必要とする部屋数を指定（例として1部屋固定にしてる）
    $room_count = 1;

    // 予約済みの部屋数 + 今回予約部屋数 が 総数を超える場合は予約不可
    //echo "部屋数チェック開始<br>";
    if (($confirmed_rooms + $room_count) > $total_rooms) {
        echo '<h2>予約エラー</h2>';
        echo '<ul><li>指定の日付でこの部屋タイプは満室。別の日付か別タイプにしてください。</li></ul>';
        echo '<p><a href="/webapp/reservation.html">戻る</a></p>';
        exit;
    }
    //echo "部屋数チェック完了<br>";

    // ここから下は問題なければ予約を実行
    //echo "予約挿入開始<br>";
    $sql = "
        INSERT INTO reservations
        (your_name, your_email, your_phone, checkin_date, checkout_date, guest_count, room_type, requests, room_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("クエリの準備に失敗: " . $mysqli->error);
    }

    $stmt->bind_param(
        "sssssissi",
        $your_name,
        $your_email,
        $your_phone,
        $checkin_date,
        $checkout_date,
        $guest_count,
        $room_type,
        $requests,
        $room_count
    );

    if ($stmt->execute()) {
        echo <<<HTML
        <html>
        <head>
            <meta charset="UTF-8">
            <title>予約完了</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    margin: 50px;
                }
                h2 {
                    color: green;
                }
            </style>
        </head>
        <body>
            <h2>予約完了</h2>
            <p>予約内容を確認して連絡いたします。</p>
            <p><a href="/webapp/index.php">ホームに戻る</a></p>
        </body>
        </html>
        HTML;
    } else {
        echo '<p>予約処理でエラー: ' . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p><a href="/webapp/reservation.html">戻る</a></p>';
    }
    $stmt->close();
    //echo "予約挿入完了<br>";

    // 接続を閉じる
    $mysqli->close();
    //echo "データベース接続閉鎖<br>";

} catch (Exception $e) {
    echo '<p>エラー発生: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><a href="/webapp/reservation.html">戻る</a></p>';
}
?>
