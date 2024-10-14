<?php
$smtp_server = '';  // SMTP server
$smtp_port = '';                            // SMTP port
$username = '';  // SMTP username
$password = '';                    // SMTP password

$to = 'inayatullahkks@gmail.com';
$subject = 'Test HTML Email Subject';
$message = '<html><body>';
$message .= '<h1>This is a test email</h1>';
$message .= '<p>This is a test email body in <strong>HTML</strong>.</p>';
$message .= '</body></html>';

// Create a socket connection
$connection = fsockopen($smtp_server, $smtp_port);

if (!$connection) {
    die("Failed to connect to SMTP server.");
}

// Read server response
$response = fgets($connection, 512);
if (strpos($response, '220') === false) {
    die("Error: " . $response);
}

// Send HELO command
fputs($connection, "HELO $smtp_server\r\n");
$response = fgets($connection, 512);

// Authenticate
fputs($connection, "AUTH LOGIN\r\n");
$response = fgets($connection, 512);

fputs($connection, base64_encode($username) . "\r\n");
$response = fgets($connection, 512);

fputs($connection, base64_encode($password) . "\r\n");
$response = fgets($connection, 512);

// Set the sender
fputs($connection, "MAIL FROM: <$username>\r\n");
$response = fgets($connection, 512);

// Set the recipient
fputs($connection, "RCPT TO: <$to>\r\n");
$response = fgets($connection, 512);

// Send data
fputs($connection, "DATA\r\n");
$response = fgets($connection, 512);

// Set headers for HTML email
fputs($connection, "Subject: $subject\r\n");
fputs($connection, "From: $username\r\n");
fputs($connection, "To: $to\r\n");
fputs($connection, "MIME-Version: 1.0\r\n");
fputs($connection, "Content-Type: text/html; charset=UTF-8\r\n");
fputs($connection, "Content-Transfer-Encoding: 8bit\r\n");  // Ensure correct encoding
fputs($connection, "\r\n");  // This empty line separates headers from the body

// Send the HTML message body
fputs($connection, "$message\r\n.\r\n");
$response = fgets($connection, 512);

// Close connection
fputs($connection, "QUIT\r\n");
fclose($connection);

echo "Email sent successfully!";

?>
