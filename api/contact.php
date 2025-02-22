<?php
// デバッグ用の設定
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


// 必要なファイルの読み込み
require 'db.php';

try {
    // データベース接続
    $mysqli = getDbConnection();

    // フォームデータの取得
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $item1 = $_POST['item1'] ?? '';
        $item2 = $_POST['item2'] ?? '';
        $item3 = $_POST['item3'] ?? '';

        // データのサニタイズ
        $item1 = htmlspecialchars($item1, ENT_QUOTES, 'UTF-8');
        $item2 = htmlspecialchars($item2, ENT_QUOTES, 'UTF-8');
        $item3 = htmlspecialchars($item3, ENT_QUOTES, 'UTF-8');

        // データベースに保存
        $stmt = $mysqli->prepare("INSERT INTO inquiries (name, email, message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $item1, $item2, $item3);

            if ($stmt->execute()) {
                echo "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>送信完了</title>
                    <script>
                        let countdown = 5;
                        function startCountdown() {
                            const counter = document.getElementById('countdown');
                            const interval = setInterval(() => {
                                counter.textContent = countdown;
                                countdown--;
                                if (countdown < 0) {
                                    clearInterval(interval);
                                    window.location.href = '/webapp/finish.html';
                                }
                            }, 1000);
                        }
                    </script>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            padding: 50px;
                        }
                        #countdown {
                            font-size: 2em;
                            font-weight: bold;
                            color: red;
                        }
                    </style>
                </head>
                <body onload='startCountdown()'>
                    <h1>お問い合わせありがとうございました！</h1>
                    <p>5秒後にお問い合わせページに戻ります。</p>
                    <div id='countdown'>5</div>
                </body>
                </html>";
            } else {
                echo "データの保存中にエラーが発生しました: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "準備中にエラーが発生しました: " . $mysqli->error;
        }
    }
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage();
}

// データベース接続を閉じる
$mysqli->close();
?>
