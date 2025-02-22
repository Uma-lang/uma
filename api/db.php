<?php
// データベース接続情報
$host = 'uma-database.cbo2e84ws3kt.ap-northeast-1.rds.amazonaws.com';
$dbname = 'webapp';
$user = '';
$password = '';

// データベース接続関数
function getDbConnection() {
    global $host, $dbname, $user, $password;

    // mysqliを使用して接続
    $mysqli = new mysqli($host, $user, $password, $dbname);

    // 接続エラーチェック
    if ($mysqli->connect_error) {
        die("データベース接続に失敗しました: " . $mysqli->connect_error);
    }

    // 文字セットを utf8mb4 に設定
    if (!$mysqli->set_charset("utf8mb4")) {
        die("文字セットの設定エラー: " . $mysqli->error);
    }
    return $mysqli;
}
?>
