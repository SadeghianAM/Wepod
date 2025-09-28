<?php
// فایل: database.php

try {
    // مسیر فایل دیتابیس
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.db');

    // تنظیم حالت نمایش خطا برای راحتی در دیباگ
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // فعال کردن کلیدهای خارجی
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    // در صورت بروز خطا در اتصال، برنامه متوقف و پیام نمایش داده می‌شود
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

return $pdo;
