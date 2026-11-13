<?php
require_once 'core/core.php';
// Destroy the session to log the customer out
session_destroy();
// Send the user back to the home page
redirect('index.php');
