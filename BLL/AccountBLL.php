<?php

// gọi các lớp liên quan
// require('../DAL/connectionDatabase.php');
// require('../DAL/AbstractionDAL.php');
// require('../DTO/AccountDTO.php');
// require('../DAL/AccountDAL.php');

// các thư viện liên quan gửi nhận mail.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require('../PHPMailer-master/PHPMailer-master/src/PHPMailer.php');
require('../PHPMailer-master/PHPMailer-master/src/Exception.php');
require('../PHPMailer-master/PHPMailer-master/src/SMTP.php');

class AccountBLL
{
       private $AccountDAL;

       function __construct()
       {
              $this->AccountDAL = new AccountDAL();
       }

       // Kiểm tra và làm sạch dữ liệu đầu vào
       private function validateInput($input, $type = 'string', $maxLength = null)
       {
              $input = trim($input);
              switch ($type) {
                     case 'email':
                            return filter_var($input, FILTER_VALIDATE_EMAIL)
                            ? $input : null;
                     case 'string':
                            return (!is_null($maxLength) && strlen($input) > $maxLength)
                            ? null : htmlspecialchars(strip_tags($input));
                     case 'int':
                            return filter_var($input, FILTER_VALIDATE_INT)
                            ? (int)$input : null;
                     default:
                            return htmlspecialchars(strip_tags($input));
              }
       }

       // Check login
       function checkLogin()
       {
              if (session_status() == PHP_SESSION_NONE) {
                     session_start();
              }
              $result = array();
              if (isset($_SESSION['result']) && $_SESSION['result'] == "success") {
                     $obj = array(
                            "userName" => $_SESSION['username'],
                            "passWord" => $_SESSION['passWord'],
                            "dateCreate" => $_SESSION['dateCreate'],
                            "accountStatus" => $_SESSION['accountStatus'],
                            "name" => $_SESSION['name'],
                            "address" => $_SESSION['address'],
                            "email" => $_SESSION['email'],
                            "phoneNumber" => $_SESSION['phoneNumber'],
                            "birth" => $_SESSION['birth'],
                            "sex" => $_SESSION['sex'],
                            "codePermission" => $_SESSION['codePermission'],
                            "result" => "success"
                     );
                     $result[] = $obj;
              } else {
                     $_SESSION['result'] = "notFound";
                     $obj = array("result" => "notFound");
                     $result[] = $obj;
              }
              return $result;
       }

       // Check username
       function checkUserName($userName)
       {
              $userName = $this->validateInput($userName, 'string', 50);
              if (is_null($userName)) {
                     return array(array("result" => "invalidInput"));
              }
              $user = $this->AccountDAL->getobj($userName);
              $result = array();
              if ($user == null) {
                     $obj = array("result" => "notFound");
                     array_push($result, $obj);
              } else {
                     $obj = array("result" => "success");
                     array_push($result, $obj);
              }
              return $result;
       }

       // Khôi phục mật khẩu
       function resetPass($userName, $email)
       {
              $userName = $this->validateInput($userName, 'string', 50);
              $email = $this->validateInput($email, 'email');
              if (is_null($userName) || is_null($email)) {
                     return array("mess" => "invalidInput");
              }

              $user = $this->AccountDAL->getobj($userName);
              if ($user != null && $email == $user->getEmail()) {
                     $random_number = rand(1000, 9999);
                     $mail = new PHPMailer(true);
                     try {
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'truongphuc056@gmail.com';
                            $mail->Password = 'ppgkknqsnqztvuuf';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;
                            $mail->setFrom('truongphuc056@gmail.com', 'MINIMAL');
                            $mail->addAddress($email, $userName);
                            $mail->Subject = 'Email sending authentication code from MINIMAL store';
                            $mail->Body = "This is your authentication code: $random_number";
                            if ($mail->send()) {
                                   return array(
                                          "username" => $userName,
                                          "email" => $email,
                                          "codeReset" => $random_number,
                                          "mess" => "success"
                                   );
                            } else {
                                   return array("mess" => "failus to send mail");
                            }
                     } catch (Exception $e) {
                            return array("mess" => "failus to send mail ! Exception");
                     }
              } else {
                     return array("mess" => is_null($user) ? "notFound" : "wrongMail");
              }
       }

       // Login
       function login($userName, $passWord)
       {
              $userName = $this->validateInput($userName, 'string', 50);
              $passWord = $this->validateInput($passWord, 'string', 255);
              if (is_null($userName) || is_null($passWord)) {
                     return array(array("result" => "invalidInput"));
              }

              $user = $this->AccountDAL->getobj($userName);
              $result = array();
              if ($user != null && $user->getPassword() === $passWord) {
                     if ($user->getAccountStatus() === '1') {
                            if (session_status() == PHP_SESSION_NONE) {
                                   session_start();
                            }
                            $_SESSION['username'] = $userName;
                            $_SESSION['passWord'] = $passWord;
                            $_SESSION['result'] = "success";
                            $obj = array(
                                   "userName" => $userName,
                                   "passWord" => $passWord,
                                   "result" => "success"
                            );
                            array_push($result, $obj);
                     } else {
                            array_push($result, array("result" => "block"));
                     }
              } else {
                     array_push($result, array("result" => $user ? "wrongPass" : "notFound"));
              }
              return $result;
       }

       // Thêm một tài khoản vào database
       function addAccount($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission)
       {
              $userName = $this->validateInput($userName, 'string', 50);
              $passWord = $this->validateInput($passWord, 'string', 255);
              $email = $this->validateInput($email, 'email');
              if (is_null($userName) || is_null($passWord) || is_null($email)) {
                     return array(array("result" => "invalidInput"));
              }

              $obj = new AccountDTO($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission);
              $check = $this->AccountDAL->addobj($obj);
              return array(array("result" => $check ? "success" : "false"));
       }

       // Sửa một tài khoản
       function updateAccount($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission)
       {
              $userName = $this->validateInput($userName, 'string', 50);
              $passWord = $this->validateInput($passWord, 'string', 255);
              $email = $this->validateInput($email, 'email');
              if (is_null($userName) || is_null($passWord) || is_null($email)) {
                     return array("mess" => "invalidInput");
              }

              $obj = new AccountDTO($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission);
              $check = $this->AccountDAL->upadateObj($obj);
              return array("mess" => $check ? "success" : "failus");
       }
}
