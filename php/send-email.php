<?php
// Load Composer autoloader for PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection details
$host = "localhost";
$dbname = "sport";
$username = "root";
$password = "";

$response = [
    'success' => false,
    'message' => '',
    'confirmationCode' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sanitize inputs
        $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $whatsapp = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING);
        $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
        $gameUID = filter_input(INPUT_POST, 'U-ID', FILTER_SANITIZE_STRING);
        $inGameName = filter_input(INPUT_POST, 'In-Game-Name', FILTER_SANITIZE_STRING);
        $level = filter_input(INPUT_POST, 'rank', FILTER_SANITIZE_NUMBER_INT);
        $tournamentType = filter_input(INPUT_POST, 'tournamentType', FILTER_SANITIZE_STRING);
        $gameName = filter_input(INPUT_POST, 'GameName', FILTER_SANITIZE_STRING);
        $additionalInfo = filter_input(INPUT_POST, 'additionalInfo', FILTER_SANITIZE_STRING);
        $paymentId = filter_input(INPUT_POST, 'paymentId', FILTER_SANITIZE_STRING);
        $paymentName = filter_input(INPUT_POST, 'paymentName', FILTER_SANITIZE_STRING);
        $paymentRemark = filter_input(INPUT_POST, 'paymentRemark', FILTER_SANITIZE_STRING);

        if (empty($fullName) || empty($email) || empty($whatsapp) || empty($country) ||
            empty($gameUID) || empty($inGameName) || empty($level) || empty($tournamentType) ||
            empty($gameName) || empty($paymentId) || empty($paymentName)) {
            throw new Exception("All required fields must be filled out.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if ((int)$level < 60) {
            throw new Exception("Your level must be at least 60 to participate.");
        }

        $confirmationCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $paymentProofPath = "";
        if (isset($_FILES['paymentProof']) && $_FILES['paymentProof']['error'] === 0) {
            $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
            $filename = $_FILES['paymentProof']['name'];
            $filetype = $_FILES['paymentProof']['type'];
            $filesize = $_FILES['paymentProof']['size'];

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed)) {
                throw new Exception("Invalid file format. Use JPG, JPEG, PNG, or GIF.");
            }

            if ($filesize > 5 * 1024 * 1024) {
                throw new Exception("File size must not exceed 5MB.");
            }

            if (!file_exists("uploads")) {
                mkdir("uploads", 0777, true);
            }

            $newfilename = "payment_" . $confirmationCode . "_" . time() . "." . $ext;
            $paymentProofPath = "uploads/" . $newfilename;

            if (!move_uploaded_file($_FILES['paymentProof']['tmp_name'], $paymentProofPath)) {
                throw new Exception("Error uploading payment proof.");
            }
        } else {
            throw new Exception("Payment proof is required.");
        }

        $stmt = $conn->prepare("INSERT INTO esports_registrations (
            full_name, email, contact, country, game_uid, in_game_name, current_level,
            tournament_type, game_name, additional_info,
            payment_id, payment_name, payment_remark, payment_proof_image
        ) VALUES (
            :fullName, :email, :contact, :country, :gameUID, :inGameName, :level,
            :tournamentType, :gameName, :additionalInfo,
            :paymentId, :paymentName, :paymentRemark, :paymentProofImage
        )");

        $stmt->execute([
            ':fullName' => $fullName,
            ':email' => $email,
            ':contact' => $whatsapp,
            ':country' => $country,
            ':gameUID' => $gameUID,
            ':inGameName' => $inGameName,
            ':level' => $level,
            ':tournamentType' => $tournamentType,
            ':gameName' => $gameName,
            ':additionalInfo' => $additionalInfo,
            ':paymentId' => $paymentId,
            ':paymentName' => $paymentName,
            ':paymentRemark' => $paymentRemark,
            ':paymentProofImage' => $paymentProofPath
        ]);

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sendwebmaile@gmail.com'; // Replace with your email
        $mail->Password = 'cpynjudlpvgtugmu';   // Replace with your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@ezaygaming.com', 'EzayGaming | Esports Tournament');
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = 'eSports Tournament Registration Confirmation';
        $mail->Body = "<h2>Hi $fullName,</h2>
            <p>You have successfully registered for <strong>$gameName</strong> - <strong>$tournamentType</strong>.</p>
            <p><strong>Confirmation Code:</strong> $confirmationCode</p>
            <p>We'll contact you on WhatsApp at $whatsapp soon.</p>
            <p><small>This is an automated email. Please do not reply.</small></p>";

        $mail->send();

        $response['success'] = true;
        $response['message'] = "Registration successful. Confirmation email sent.";
        $response['confirmationCode'] = $confirmationCode;

    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
} else {
    $response['message'] = "Invalid request method.";
}

header('Content-Type: application/json');
echo json_encode($response);
?>
