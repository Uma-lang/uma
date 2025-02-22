<?php
// api/calendar_two_months.php

// エラー表示設定（デバッグ用）
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// データベース接続ファイルの読み込み
require 'db.php';

// 部屋タイプごとの総数を定義
$roomTypes = [
    '学生部屋' => [
        'name' => '学生部屋',
        'total' => 10
    ],
    '指導官室' => [
        'name' => '指導官室',
        'total' => 4
    ],
    '乾燥室' => [
        'name' => '乾燥室',
        'total' => 4
    ],
    '服務室' => [
        'name' => '服務室',
        'total' => 4
    ]
];

// 全体の部屋数を計算
$totalRooms = 0;
foreach ($roomTypes as $type) {
    $totalRooms += $type['total'];
}

// 表示する月と年を取得（デフォルトは現在の月）
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// 次の月の計算
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear += 1;
}

// 月の初日と最終日を計算
$firstDayOfMonth1 = date('Y-m-01', strtotime("$year-$month-01"));
$lastDayOfMonth1 = date('Y-m-t', strtotime($firstDayOfMonth1));

$firstDayOfMonth2 = date('Y-m-01', strtotime("$nextYear-$nextMonth-01"));
$lastDayOfMonth2 = date('Y-m-t', strtotime($firstDayOfMonth2));

// 日付範囲を設定
$startDate = $firstDayOfMonth1;
$endDate = $lastDayOfMonth2;

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
    $availability = []; // ['YYYY-MM-DD' => available_rooms]

    // 初期化: 全ての日の空き数を総部屋数で初期化
    $currentDate = strtotime($startDate);
    $endDateTimestamp = strtotime($endDate);

    while ($currentDate <= $endDateTimestamp) {
        $dateStr = date('Y-m-d', $currentDate);
        $availability[$dateStr] = $totalRooms;
        $currentDate = strtotime('+1 day', $currentDate);
    }

    // 各予約を処理して空き状況を更新
    foreach ($reservations as $reservation) {
        // 各予約の部屋タイプに応じて総部屋数を減算
        $roomType = $reservation['room_type'];
        if (!isset($roomTypes[$roomType])) {
            continue; // 不明な部屋タイプはスキップ
        }

        $roomCount = 1; // 1予約が1部屋を占有すると仮定

        $checkin = strtotime($reservation['checkin_date']);
        $checkout = isset($reservation['checkout_date']) ? strtotime($reservation['checkout_date']) : strtotime('+1 day', $checkin); // チェックアウト日がない場合は1日後

        // 各日をループして空き状況を減らす
        $currentDate = max($checkin, strtotime($startDate));
        $lastDate = min($checkout - 1, $endDateTimestamp); // チェックアウト日は含めない

        while ($currentDate <= $lastDate) {
            $dateStr = date('Y-m-d', $currentDate);
            if (isset($availability[$dateStr])) {
                $availability[$dateStr] -= $roomCount;
                if ($availability[$dateStr] < 0) {
                    $availability[$dateStr] = 0; // 負の値にならないように
                }
            }
            $currentDate = strtotime('+1 day', $currentDate);
        }
    }

} catch (Exception $e) {
    error_log($e->getMessage()); // エラーメッセージをログに記録
    echo '<p>予期せぬエラーが発生しました。後ほど再度お試しください。</p>';
    exit;
}

// データベース接続を閉じる
$mysqli->close();

// カレンダーの生成
// 月1の情報
$firstDayWeek1 = date('w', strtotime($firstDayOfMonth1));
$totalDays1 = date('t', strtotime($firstDayOfMonth1));

// 月2の情報
$firstDayWeek2 = date('w', strtotime($firstDayOfMonth2));
$totalDays2 = date('t', strtotime($firstDayOfMonth2));

// カレンダーHTMLを生成
function generateCalendar($year, $month, $firstDayWeek, $totalDays, $availability, $totalRooms) {
    $html = '';

    // 月の表示
    $monthName = date('n月', strtotime("$year-$month-01"));
    $html .= '<div class="calendar-month">';
    $html .= '<div class="calendar-header">';
    $html .= '<h2>' . htmlspecialchars($year . '年 ' . $monthName . 'の空き状況') . '</h2>';
    $html .= '</div>';
    $html .= '<div class="calendar-grid">';

    // 曜日ヘッダー
    $daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
    foreach ($daysOfWeek as $day) {
        $html .= "<div class=\"calendar-day-header\">$day</div>";
    }

    // 空白セル（最初の週）
    for ($i = 0; $i < $firstDayWeek; $i++) {
        $html .= "<div class=\"calendar-day\"></div>";
    }

    // 各日を表示
    for ($d = 1; $d <= $totalDays; $d++) {
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $availableRooms = isset($availability[$dateStr]) ? $availability[$dateStr] : $totalRooms;

        // 空き状況のクラスを決定
        if ($availableRooms > ($totalRooms * 0.75)) {
            $statusClass = 'available';
            $statusText = "⚪︎";
        } elseif ($availableRooms > ($totalRooms * 0.25)) {
            $statusClass = 'limited';
            $statusText = "△";
        } else {
            $statusClass = 'full';
            $statusText = "×";
        }

        $html .= "<div class=\"calendar-day $statusClass\">";
        $html .= "<div class=\"date-number\">$d</div>";
        $html .= "<div class=\"availability $statusClass status-text\">$statusText</div>";
        $html .= "</div>";
    }

    // 空白セル（最後の週）
    $lastDayWeek = date('w', strtotime("$year-$month-$totalDays"));
    for ($i = $lastDayWeek + 1; $i <= 6; $i++) {
        $html .= "<div class=\"calendar-day\"></div>";
    }

    $html .= '</div>'; // .calendar-grid
    $html .= '</div>'; // .calendar-month

    return $html;
}

$calendarHtml = generateCalendar($year, $month, $firstDayWeek1, $totalDays1, $availability, $totalRooms);
$calendarHtml .= generateCalendar($nextYear, $nextMonth, $firstDayWeek2, $totalDays2, $availability, $totalRooms);

// 出力
echo '<div class="calendar-container">';
echo $calendarHtml;
echo '</div>';
?>
