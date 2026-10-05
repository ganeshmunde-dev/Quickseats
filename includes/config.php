<?php
// SMS Gateway Configuration
// Supported values: 'simulated', 'fast2sms', 'twilio'
define('SMS_GATEWAY', 'gmail');
// ==========================================
// Fast2SMS Configuration (https://www.fast2sms.com/)
// ==========================================
define('FAST2SMS_API_KEY', 'YOUR_FAST2SMS_API_KEY');
define('FAST2SMS_SENDER_ID', 'TXTIND'); // Default free sender ID is TXTIND or FSTSMS

// ==========================================
// Twilio Configuration (https://www.twilio.com/)
// ==========================================
define('TWILIO_ACCOUNT_SID', 'YOUR_TWILIO_ACCOUNT_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_TWILIO_AUTH_TOKEN');
define('TWILIO_PHONE_NUMBER', 'YOUR_TWILIO_PHONE_NUMBER');

// ==========================================
// Gmail SMTP Configuration
// ==========================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587); // 587 (TLS) or 465 (SSL)

define('MAIL_USERNAME', 'YOUR_GMAIL_ADDRESS');
define('MAIL_PASSWORD', 'YOUR_GMAIL_APP_PASSWORD');
