<?php
// delete.php

session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: admin_dashboard.php');
    exit();
}

require 'api/db.php';

if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = intval($_GET['id']);

    $mysqli = getDbConnection();

    if ($type === 'reservation') {
        $stmt = $mysqli->prepare("DELETE FROM reservations WHERE id = ?");
    } elseif ($type === 'inquiry') {
        $stmt = $mysqli->prepare("DELETE FROM inquiries WHERE id = ?");
    } else {
        die("不正なリクエストです。");
    }

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header('Location: admin_dashboard.php');
        exit();
    } else {
        die("クエリの準備に失敗しました。");
    }
} else {
    die("必要なパラメータが不足しています。");
}
?>
