<?php
/**
 * Developer Configuration
 * Separate credentials for developer-only access
 */

return [
    // Developer credentials (change these!)
    // Default: username='developer', password='dev@2024!Secure'
    // To change password, generate hash: php -r "echo password_hash('your_new_password', PASSWORD_DEFAULT);"
    'username' => 'developer',
    'password' => '$2y$10$962JsdiFm3j337ATonvZbuZ/qNbNcGZukS.IszmCYV2LH.zWS9Rom', // Hash for: dev@2024!Secure
    
    // Session settings
    'session_name' => 'developer_session',
    'session_lifetime' => 86400, // 24 hours
    
    // Security
    'max_login_attempts' => 5,
    'lockout_duration' => 900, // 15 minutes
    
    // Developer info
    'name' => 'Project Developer',
    'email' => 'developer@s3vgroup.com',
];

