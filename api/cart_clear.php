<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth.php';
if (!isLoggedIn()) { redirect('/login.php'); }
cartClear();
redirect('/cart.php');
