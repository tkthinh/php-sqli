<?php

// Include required classes
require('../DAL/connectionDatabase.php');
require('../DAL/AbstractionDAL.php');
require('../DTO/AccountDTO.php');
require('../DAL/AccountDAL.php');

class AccountBLL
{
       private $AccountDAL;

       function __construct()
       {
              $this->AccountDAL = new AccountDAL();
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
              $user = $this->AccountDAL->getobj($userName);
              $result = array();
              if ($user == null) {
                     $obj = array(
                            "result" => "notFound"
                     );
                     array_push($result, $obj);
                     return $result;
              } else {
                     $obj = array(
                            "result" => "success"
                     );
                     array_push($result, $obj);
                     return $result;
              }
       }

       // Password reset
       function resetPass($userName, $email)
       {
              $user = $this->AccountDAL->getobj($userName);
              if ($user != null) {
                     if ($email == $user->getEmail()) {
                            $random_number = rand(1000, 9999);
                     } else {
                            return array(
                                   "mess" => "wrongMail"
                            );
                     }
              } else {
                     return array(
                            "mess" => "notFound"
                     );
              }
       }

       // Login function
       function login($userName, $passWord)
       {
              $user = $this->AccountDAL->getobj($userName);
              $result = array();

              if ($user != null) {
                     $hashedPassword = $user->getPassword();
       
                     // Verify password using password_verify
                     if (password_verify($passWord, $hashedPassword)) {
                            $dateCreate = $user->getDateCreate();
                            $accountStatus = $user->getAccountStatus();
                            $name = $user->getName();
                            $address = $user->getAddress();
                            $email = $user->getEmail();
                            $phoneNumber = $user->getPhoneNumber();
                            $birth = $user->getBirth();
                            $sex = $user->getSex();
                            $codePermission = $user->getCodePermission();
                            if ($accountStatus === '1') {
                                   if (session_start()) {
                                          $_SESSION['username'] = $userName;
                                          $_SESSION['dateCreate'] = $dateCreate;
                                          $_SESSION['accountStatus'] = $accountStatus;
                                          $_SESSION['name'] = $name;
                                          $_SESSION['address'] = $address;
                                          $_SESSION['email'] = $email;
                                          $_SESSION['phoneNumber'] = $phoneNumber;
                                          $_SESSION['birth'] = $birth;
                                          $_SESSION['sex'] = $sex;
                                          $_SESSION['codePermission'] = $codePermission;
                                          $_SESSION['result'] = "success";
                                   }
                                   $obj = array(
                                          "userName" => $userName,
                                          "dateCreate" => $dateCreate,
                                          "accountStatus" => $accountStatus,
                                          "name" => $name,
                                          "address" => $address,
                                          "email" => $email,
                                          "phoneNumber" => $phoneNumber,
                                          "birth" => $birth,
                                          "sex" => $sex,
                                          "codePermission" => $codePermission,
                                          "result" => "success"
                                   );
                                   array_push($result, $obj);
                                   return $result;
                            } else {
                                   $obj = array(
                                          "result" => "block"
                                   );
                                   array_push($result, $obj);
                                   return $result;
                            }
                     } else {
                            $obj = array(
                                   "result" => "wrongPass",
                                   "username" => $user->getUsername(),
                                   "email" => $user->getEmail()
                            );
                            array_push($result, $obj);
                            return $result;
                     }
              } else {
                     $obj = array(
                            "result" => "notFound"
                     );
                     array_push($result, $obj);
                     return $result;
              }
       }

       // Add a new account to the database
       function addAccount($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission)
       {
              // Hash password using password_hash
              $hashedPassword = password_hash($passWord, PASSWORD_DEFAULT);
              $obj = new AccountDTO($userName, $hashedPassword, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission);
              $result = array();
              $check = $this->AccountDAL->addobj($obj);
              if ($check == true) {
                     $obj = array(
                            "result" => "success"
                     );
                     array_push($result, $obj);
                     return $result;
              } else {
                     $obj = array(
                            "result" => "false"
                     );
                     array_push($result, $obj);
                     return $result;
              }
       }
}

// Menu
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $check = new AccountBLL();
       $function = $_POST['function'];

       switch ($function) {
              case 'login':
                     $userName = $_POST['userName'];
                     $passWord = $_POST['passWord'];
                     $temp = $check->login($userName, $passWord);
                     echo json_encode($temp);
                     break;
              case 'checkLogin':
                     $temp = $check->checkLogin();
                     echo json_encode($temp);
                     break;
              case 'logout_whenExitPage':
                     $temp = $check->logout_whenExitPage();
                     echo json_encode($temp);
                     break;
              case 'checkUserName':
                     $userName = $_POST['userName'];
                     $temp = $check->checkUserName($userName);
                     echo json_encode($temp);
                     break;
              case 'addAccount':
                     $userName = $_POST['userName'];
                     $passWord = $_POST['passWord'];
                     $dateCreate = $_POST['dateCreate'];
                     $accountStatus = $_POST['accountStatus'];
                     $name = $_POST['name'];
                     $address = $_POST['address'];
                     $email = $_POST['email'];
                     $phoneNumber = $_POST['phoneNumber'];
                     $birth = $_POST['birth'];
                     $sex = $_POST['sex'];
                     $codePermission = $_POST['codePermission'];
                     $temp = $check->addAccount($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission);
                     echo json_encode($temp);
                     break;
              case 'updateAccount':
                     $userName = $_POST['userName'];
                     $passWord = $_POST['passWord'];
                     $dateCreate = $_POST['dateCreate'];
                     $accountStatus = $_POST['accountStatus'];
                     $name = $_POST['name'];
                     $address = $_POST['address'];
                     $email = $_POST['email'];
                     $phoneNumber = $_POST['phoneNumber'];
                     $birth = $_POST['birth'];
                     $sex = $_POST['sex'];
                     $codePermission = $_POST['codePermission'];
                     $temp = $check->updateAccount($userName, $passWord, $dateCreate, $accountStatus, $name, $address, $email, $phoneNumber, $birth, $sex, $codePermission);
                     echo json_encode($temp);
                     break;
              case 'getObjAccount':
                     $userName = $_POST['userName'];
                     $temp = $check->getObjAccount($userName);
                     echo json_encode($temp);
                     break;
              case 'resetPass':
                     $userName = $_POST['userName'];
                     $email = $_POST['email'];
                     $temp = $check->resetPass($userName, $email);
                     echo json_encode($temp);
                     break;
       }
}
?>
