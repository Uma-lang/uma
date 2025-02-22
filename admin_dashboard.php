<?php
// admin_dashboard.php

session_start();

$admin_username = 'admin_user';
$admin_password = 'sdfheu-ealkjdfb-sfsoidf';

if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
            $_SESSION['logged_in'] = true;
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $error = "ユーザー名またはパスワードが正しくありません。";
        }
    }
    // ログインフォームを表示
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>管理者ログイン</title>
        <link rel="stylesheet" href="css/dash_style.css">
    </head>
    <body>
        <h2>管理者ログイン</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <input type="submit" value="ログイン">
        </form>
    </body>
    </html>
    <?php
    exit();
}

require 'api/db.php';

// データベース接続
$mysqli = getDbConnection();

// 予約データの取得
$reservations_query = "SELECT * FROM reservations ORDER BY created_at DESC";
$reservations_result = $mysqli->query($reservations_query);

// 問い合わせデータの取得
$inquiries_query = "SELECT * FROM inquiries ORDER BY created_at DESC";
$inquiries_result = $mysqli->query($inquiries_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>ホテル管理者用予約管理</title>
    <link rel="stylesheet" href="css/dash_style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .logout {
            float: right;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>ホテル管理者用予約管理</h1>
    <a href="logout.php" class="logout">ログアウト</a>

    <h2>予約一覧</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>名前</th>
            <th>Email</th>
            <th>電話番号</th>
            <th>チェックイン日</th>
            <th>チェックアウト日</th>
            <th>ゲスト数</th>
            <th>部屋タイプ</th>
            <th>リクエスト</th>
            <th>作成日時</th>
            <th>操作</th>
        </tr>
        <?php while($row = $reservations_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['your_name']); ?></td>
            <td><?php echo htmlspecialchars($row['your_email']); ?></td>
            <td><?php echo htmlspecialchars($row['your_phone']); ?></td>
            <td><?php echo htmlspecialchars($row['checkin_date']); ?></td>
            <td><?php echo htmlspecialchars($row['checkout_date']); ?></td>
            <td><?php echo htmlspecialchars($row['guest_count']); ?></td>
            <td><?php echo htmlspecialchars($row['room_type']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['requests'])); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><a href="delete.php?type=reservation&id=<?php echo $row['id']; ?>" onclick="return confirm('本当に削除しますか？');">削除</a></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>問い合わせ一覧</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>名前</th>
            <th>Email</th>
            <th>メッセージ</th>
            <th>作成日時</th>
            <th>操作</th>
        </tr>
        <?php while($row = $inquiries_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td><a href="delete.php?type=inquiry&id=<?php echo $row['id']; ?>" onclick="return confirm('本当に削除しますか？');">削除</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
