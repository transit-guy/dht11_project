<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require 'vendor/autoload.php';

$hostname = "localhost";
$username = "root";
$password = "";
$database = "sensor_db";

// Establish a connection to the database
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "";

// Check if the UID is set in the POST request
if (isset($_POST["uid"])) {
    $uid = $_POST["uid"];

    // Store the UID in the dht11 table
    $stmt_dht11 = $conn->prepare("INSERT INTO dht11 (uid) VALUES (?)");
    $stmt_dht11->bind_param("s", $uid);
    if ($stmt_dht11->execute()) {
        echo "\nUID stored in dht11 table successfully";

        // Check if UID exists in the students table
        $stmt_students = $conn->prepare("SELECT name, email FROM students WHERE uid = ?");
        $stmt_students->bind_param("s", $uid);
        $stmt_students->execute();
        $stmt_students->store_result();

        if ($stmt_students->num_rows > 0) {
            $stmt_students->bind_result($name, $email);
            $stmt_students->fetch();

            // Check if there is an existing log entry without return_time
            $stmt_check_log = $conn->prepare("SELECT id FROM logs WHERE uid = ? AND return_time IS NULL");
            $stmt_check_log->bind_param("s", $uid);
            $stmt_check_log->execute();
            $stmt_check_log->store_result();

            if ($stmt_check_log->num_rows > 0) {
                // If an entry exists, update the return_time
                $stmt_check_log->bind_result($log_id);
                $stmt_check_log->fetch();
                $stmt_update_log = $conn->prepare("UPDATE logs SET return_time = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt_update_log->bind_param("i", $log_id);
                if ($stmt_update_log->execute()) {
                    echo "\nReturn time updated successfully";
                } else {
                    echo "Error: " . $stmt_update_log->error;
                }
                $stmt_update_log->close();
            } else {
                // If no entry exists, create a new log entry with default deadline_time at 8:00 PM
                $current_time = new DateTime();
                $deadline_time = clone $current_time;
                $deadline_time->setTime(22, 0); // 20:00 is 8:00 PM

                $stmt_logs = $conn->prepare("INSERT INTO logs (uid, name, exit_time, deadline_time) VALUES (?, ?, CURRENT_TIMESTAMP, ?)");
                $stmt_logs->bind_param("sss", $uid, $name, $deadline_time->format('Y-m-d H:i:s'));
                if ($stmt_logs->execute()) {
                    echo "\nNew record created in logs table successfully";

                    // Send email with form link
                    $mail = new PHPMailer(true);
                    try {
                        //Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
                        $mail->SMTPAuth = true;
                        $mail->Username = 'kaushikkumar2373@gmail.com'; // SMTP username
                        $mail->Password = 'rcit sqmo jqvc hfby'; // SMTP password or App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        //Recipients
                        $mail->setFrom('kaushikkumar2373@gmail.com', 'Kaushik');
                        $mail->addAddress($email, $name); // Add a recipient

                        // Content
                        $mail->isHTML(true); // Set email format to HTML
                        $mail->Subject = 'Form to Fill Out Reason for Leaving';
                        $mail->Body = 'Please fill out the form: <a href="http://localhost/form.php?uid=' . $uid . '">Form Link</a>';

                        $mail->send();
                        echo 'Email has been sent';
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    echo "Error: " . $stmt_logs->error;
                }
                $stmt_logs->close();
            }
            $stmt_check_log->close();
        } else {
            echo "Error: UID not found in the students table";
        }
        $stmt_students->close();
    } else {
        echo "Error: " . $stmt_dht11->error;
    }
    $stmt_dht11->close();
}

// Close the database connection
$conn->close();
?>
