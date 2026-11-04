<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';

start_admin_session();
require_valid_post();

$_SESSION = [];
session_regenerate_id(true);
session_destroy();

redirect('/admin/login.php');
