<?php
// calendar.php

// エラー表示設定（デバッグ用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続ファイルの読み込み
require 'db.php';

$roomTypes = [
    '服務室' => [
        'name' => '服務室',
        'total' => 4
    ],
];

// 表示する月と年を取得（デフォルトは現在の月）
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// 月の初日と最終日を計算
$firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
$lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));

// 日付範囲を設定
$startDate = $firstDayOfMonth;
$endDate = $lastDayOfMonth;

try {
    // データベース接続
    $mysqli = getDbConnection();

    // 予約データを取得
    $sql = "SELECT * FROM reservations WHERE
            (checkin_date <= ? AND (checkout_date >= ? OR checkout_date IS NULL))";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ss", $endDate, $startDate);
        $stmt->execute();
        $result = $stmt->get_result();

        // 予約データを配列に格納
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        $stmt->close();
    } else {
        throw new Exception("クエリの準備中にエラーが発生しました: " . $mysqli->error);
    }

    // 日ごとの予約数を計算
    $availability = [];

    foreach ($roomTypes as $typeKey => $typeInfo) {
        $availability[$typeKey] = [];
        $currentDate = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);

        while ($currentDate <= $endDateTimestamp) {
            $dateStr = date('Y-m-d', $currentDate);
            $availability[$typeKey][$dateStr] = $typeInfo['total']; // 初期値は総数
            $currentDate = strtotime('+1 day', $currentDate);
        }
    }

    // 各予約を処理して空き状況を更新
    foreach ($reservations as $reservation) {
        $roomType = $reservation['room_type'];
        if (!isset($roomTypes[$roomType])) {
            continue; // 不明な部屋タイプはスキップ
        }

        $checkin = strtotime($reservation['checkin_date']);
        $checkout = isset($reservation['checkout_date']) ? strtotime($reservation['checkout_date']) : strtotime('+1 day', $checkin); // チェックアウト日がない場合は1日後

        // 各日をループして空き状況を減らす
        $currentDate = max($checkin, strtotime($startDate));
        $lastDate = min($checkout - 1, strtotime($endDate));

        while ($currentDate <= $lastDate) {
            $dateStr = date('Y-m-d', $currentDate);
            if (isset($availability[$roomType][$dateStr])) {
                $availability[$roomType][$dateStr]--;
            }
            $currentDate = strtotime('+1 day', $currentDate);
        }
    }

    // デバッグ用に空き状況を確認
    /*
    echo '<pre>';
    print_r($availability);
    echo '</pre>';
    */

} catch (Exception $e) {
    echo '<p>エラーが発生しました: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}

$mysqli->close();

// カレンダーの生成
$firstDayWeek = date('w', strtotime($firstDayOfMonth)); // 0 (日曜日) 〜 6 (土曜日)
$totalDays = date('t', strtotime($firstDayOfMonth));
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>部屋の空き状況カレンダー</title>
    <link rel="stylesheet" href="/webapp/css/each_calendar_style.css">
</head>
<body>
    <h1>部屋の空き状況カレンダー</h1>

    <!-- 月の選択フォーム -->
    <form method="get" action="plan_hukumu.php">
        <label for="year">年:</label>
        <select name="year" id="year">
            <?php
            $currentYear = date('Y');
            for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++) {
                $selected = ($y == $year) ? 'selected' : '';
                echo "<option value=\"$y\" $selected>$y</option>";
            }
            ?>
        </select>

        <label for="month">月:</label>
        <select name="month" id="month">
            <?php
            for ($m = 1; $m <= 12; $m++) {
                $m_padded = str_pad($m, 2, '0', STR_PAD_LEFT);
                $selected = ($m == $month) ? 'selected' : '';
                echo "<option value=\"$m\" $selected>$m</option>";
            }
            ?>
        </select>

        <input type="submit" value="表示">
    </form>

    <!-- カレンダーの表示 -->
    <?php foreach ($roomTypes as $typeKey => $typeInfo): ?>
        <div class="calendar-month">
            <div class="calendar-header">
                <h2><?php echo htmlspecialchars($typeInfo['name']); ?>部屋の空き状況（<?php echo $year . '年 ' . $month . '月'; ?>）</h2>
            </div>
            <div class="calendar-grid">
                <!-- 曜日ヘッダー -->
                <?php
                $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
                foreach ($daysOfWeek as $day) {
                    echo "<div class=\"calendar-day-header\">$day</div>";
                }

                // 空白セル（最初の週）
                for ($i = 0; $i < $firstDayWeek; $i++) {
                    echo "<div class=\"calendar-day\"></div>";
                }

                // 各日を表示
                for ($d = 1; $d <= $totalDays; $d++) {
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                    $availableRooms = $availability[$typeKey][$dateStr] ?? $typeInfo['total'];

                    // 空き状況のクラスを決定
                    if ($availableRooms > 3) {
                        $statusClass = 'available';
                        $statusText = "空きあり ($availableRooms/{$typeInfo['total']})";
                    } elseif ($availableRooms > 0) {
                        $statusClass = 'limited';
                        $statusText = "残り$availableRooms/{$typeInfo['total']}";
                    } else {
                        $statusClass = 'full';
                        $statusText = "満室";
                    }

                    echo "<div class=\"calendar-day $statusClass\">";
                    echo "<div class=\"date-number\">$d</div>";
                    echo "<div class=\"availability\">$statusText</div>";
                    echo "</div>";
                }

                // 空白セル（最後の週）
                $lastDayWeek = date('w', strtotime($lastDayOfMonth));
                for ($i = $lastDayWeek + 1; $i <= 6; $i++) {
                    echo "<div class=\"calendar-day\"></div>";
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>
