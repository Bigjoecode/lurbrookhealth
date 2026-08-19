<?php
declare(strict_types=1);

$sessionPath = __DIR__ . '/data/sessions';
if (!is_dir($sessionPath)) mkdir($sessionPath, 0775, true);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_save_path($sessionPath);
    session_start();
}

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/migrations.php';

if (!is_dir(dirname($config['db_path']))) {
    mkdir(dirname($config['db_path']), 0775, true);
}

$db = new PDO('sqlite:' . $config['db_path']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec('PRAGMA journal_mode = WAL');

$db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS products (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 name TEXT NOT NULL,
 slug TEXT NOT NULL UNIQUE,
 category TEXT NOT NULL,
 sku TEXT NOT NULL UNIQUE,
 description TEXT NOT NULL,
 details TEXT NOT NULL DEFAULT '',
 price REAL NOT NULL,
 compare_price REAL,
 stock INTEGER NOT NULL DEFAULT 0,
 image TEXT NOT NULL,
 gallery TEXT NOT NULL DEFAULT '[]',
 featured INTEGER NOT NULL DEFAULT 0,
 active INTEGER NOT NULL DEFAULT 1,
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS orders (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 order_number TEXT NOT NULL UNIQUE,
 paypal_order_id TEXT,
 customer_name TEXT NOT NULL,
 customer_email TEXT NOT NULL,
 customer_phone TEXT,
 address TEXT NOT NULL,
 city TEXT NOT NULL,
 postcode TEXT NOT NULL,
 items TEXT NOT NULL,
 subtotal REAL NOT NULL,
 shipping REAL NOT NULL DEFAULT 0,
 total REAL NOT NULL,
 payment_status TEXT NOT NULL DEFAULT 'pending',
 status TEXT NOT NULL DEFAULT 'new',
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS pages (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 slug TEXT NOT NULL UNIQUE,
 title TEXT NOT NULL,
 excerpt TEXT NOT NULL DEFAULT '',
 content TEXT NOT NULL,
 active INTEGER NOT NULL DEFAULT 1,
 updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS settings (
 setting_key TEXT PRIMARY KEY,
 setting_value TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS messages (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 name TEXT NOT NULL,
 email TEXT NOT NULL,
 phone TEXT,
 subject TEXT NOT NULL,
 message TEXT NOT NULL,
 status TEXT NOT NULL DEFAULT 'unread',
 created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);

seed_database($db, $config);
run_migrations($db);
