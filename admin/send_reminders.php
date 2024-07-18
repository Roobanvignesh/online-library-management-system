<?php
session_start();
error_reporting(0);
require_once '../vendor/autoload.php';
include('includes/config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$response = array(); // Initialize response array

if(strlen($_SESSION['alogin']) == 0) {
    $response['status'] = 'error';
    $response['message'] = 'User not logged in';
} else {
    // Your database connection setup here
    // Assuming $dbh is your database connection object

    // Fetch records from the database
    $sql = "SELECT tblstudents.FullName, tblstudents.EmailId, tblbooks.BookName, tblbooks.ISBNNumber, tblissuedbookdetails.IssuesDate, tblissuedbookdetails.ReturnDate, tblissuedbookdetails.id AS rid FROM tblissuedbookdetails JOIN tblstudents ON tblstudents.StudentId = tblissuedbookdetails.StudentId JOIN tblbooks ON tblbooks.id = tblissuedbookdetails.BookId ORDER BY tblissuedbookdetails.id DESC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // Check if there are any records
    if ($query->rowCount() > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Reminders sent successfully';
        $response['details'] = array();

        // Iterate through each record
        foreach ($results as $result) {
            // Calculate difference in days between current date and return date
            $returnDate = new DateTime($result->ReturnDate);
            $currentDate = new DateTime();
            $diff = $currentDate->diff($returnDate)->days;

            // Check if book is not returned and return date is nearing within 3 days
            if ($result->ReturnDate == "" || $diff <= 3) {
                // Create a new PHPMailer instance
                $mail = new PHPMailer;
                
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'finalyearprojectiv@gmail.com'; // Your Gmail email address
                $mail->Password = 'project@1234'; // Your Gmail password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                // Sender and recipient settings
                $mail->setFrom('finalyearprojectiv@gmail.com', 'From Depak'); // Sender's email address and name
                $mail->addAddress($result->EmailId, $result->FullName); // Recipient's email address and name

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Book Return Reminder';
                $mail->Body = 'Dear ' . $result->FullName . ',<br><br>';
                if ($result->ReturnDate == "") {
                    $mail->Body .= 'This is a reminder that you have not returned the book "' . $result->BookName . '". Please return it as soon as possible.<br><br>';
                } else {
                    $mail->Body .= 'This is a reminder that the return date for the book "' . $result->BookName . '" is nearing. Please return it within the next 3 days.<br><br>';
                }
                $mail->Body .= 'Thank you.<br>Library Management System';

                // Send email
                if ($mail->send()) {
                    $response['details'][] = array(
                        'email' => $result->EmailId,
                        'status' => 'sent'
                    );
                } else {
                    $response['details'][] = array(
                        'email' => $result->EmailId,
                        'status' => 'failed',
                        'error' => $mail->ErrorInfo
                    );
                }
                $mail->smtpClose();
            }
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No records found';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
