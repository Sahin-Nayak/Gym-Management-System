<?php
// index.php — Smart entry point
// Logged-in users → dashboard | Guests → public website
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) redirect('pages/admin/dashboard.php');
    elseif (isTrainer()) redirect('pages/trainer/dashboard.php');
    else redirect('pages/member/dashboard.php');
} else {
    redirect('home.php');
}
