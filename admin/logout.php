<?php require dirname(__DIR__).'/bootstrap.php'; unset($_SESSION['admin']); session_regenerate_id(true); redirect('admin/login');
