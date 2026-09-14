<?php
/**
 * =========================================================================
 * logout.php  —  Log Out Handler
 * =========================================================================
 *
 * PURPOSE:
 *   This file logs the current user out by destroying their PHP session,
 *   then redirects them back to the login page.
 *
 * HOW TO TRIGGER IT:
 *   Link any button or anchor to this file:
 *       <a href="logout.php">Log Out</a>
 *
 * WHY WE DO IT THIS WAY:
 *   PHP sessions store data (like user_id, user_role) across page loads.
 *   To log out, we must CLEAR and DESTROY that session data so the next
 *   request has no user info attached to it.
 * =========================================================================
 */

// Load functions.php — this also calls session_start() so $_SESSION is accessible
require_once 'includes/functions.php';

// Step 1: Clear all session variables (user_id, user_name, user_role, csrf_token, etc.)
//         Setting $_SESSION to an empty array wipes all stored data from memory.
$_SESSION = [];  // Clear memory — removes all user data from the session

// Step 2: Destroy the session on the server.
//         This deletes the session file on the server and invalidates the session cookie.
session_destroy();  // Removes the session file from server storage

// Step 3: Redirect the user back to the login page.
//         After logout, the browser is sent to login.php via an HTTP redirect header.
header('Location: login.php'); // Sends HTTP redirect header to browser

// Step 4: Stop any further PHP code from running after the redirect header is sent.
exit; // Stops further execution of the script
