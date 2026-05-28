<?php
require_once 'functions.php';

// Détruit la session
session_unset();
session_destroy();

// Redirection
rediriger('index.php');