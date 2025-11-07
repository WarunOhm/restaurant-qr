<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    $redirect_url = isAdmin() ? 'admin/index.php' : 'staff/index.php';
    redirect($redirect_url);
} else {
    redirect('login.php');
}
