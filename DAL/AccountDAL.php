<?php
// import
// require('../DAL/AbstractionDAL.php');
// require('../DTO/AccountDTO.php');

class AccountDAL extends AbstractionDAL
{
       private $actionSQL = null;

       public function __construct()
       {
              parent::__construct();
              $this->actionSQL = parent::getConn();
       }

       // Validate input to prevent SQL injection
       private function validateInput($input)
       {
              return htmlspecialchars(strip_tags(trim($input)));
       }

       // Delete by ID
       function deleteByID($code)
       {
              $codeValidated = $this->validateInput($code); // Validate username

              $query = "SELECT COUNT(*) FROM feedback WHERE userName = ? 
                        UNION SELECT COUNT(*) FROM comment WHERE userNameComment = ? 
                        UNION SELECT COUNT(*) FROM orders WHERE userName = ?";
              $stmt = $this->actionSQL->prepare($query);
              $stmt->bind_param('sss', $codeValidated, $codeValidated, $codeValidated);
              $stmt->execute();
              $stmt->bind_result($count);
              $totalCount = 0;

              while ($stmt->fetch()) {
                     $totalCount += $count;
              }
              $stmt->close();

              if ($totalCount == 0) {
                     $deleteQuery = "DELETE FROM accounts WHERE userName = ?";
                     $stmt = $this->actionSQL->prepare($deleteQuery);
                     $stmt->bind_param('s', $codeValidated);
                     $result = $stmt->execute();
                     $stmt->close();
                     return $result;
              } else {
                     return false;
              }
       }

       // Delete by passing object
       function delete($obj)
       {
              if ($obj != null) {
                     return $this->deleteByID($obj->getUsername());
              }
              return false;
       }

       // Fetch all objects
       function getListobj()
       {
              $array_list = array();
              $query = 'SELECT * FROM accounts';
              $result = $this->actionSQL->query($query);

              if ($result->num_rows > 0) {
                     while ($data = $result->fetch_assoc()) {
                            $account = new AccountDTO(
                                   $data["userName"],
                                   $data["passWord"],
                                   $data["dateCreated"],
                                   $data["accountStatus"],
                                   $data["name"],
                                   $data["address"],
                                   $data["email"],
                                   $data["phoneNumber"],
                                   $data["birth"],
                                   $data["sex"],
                                   $data["codePermissions"]
                            );
                            array_push($array_list, $account);
                     }
                     return $array_list;
              }
              return null;
       }

       // Fetch single object by ID
       function getobj($code)
       {
              $codeValidated = $this->validateInput($code);
              $query = "SELECT * FROM accounts WHERE userName = ?";
              $stmt = $this->actionSQL->prepare($query);
              $stmt->bind_param('s', $codeValidated);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($data = $result->fetch_assoc()) {
                     return new AccountDTO(
                            $data["userName"],
                            $data["passWord"],
                            $data["dateCreated"],
                            $data["accountStatus"],
                            $data["name"],
                            $data["address"],
                            $data["email"],
                            $data["phoneNumber"],
                            $data["birth"],
                            $data["sex"],
                            $data["codePermissions"]
                     );
              }
              $stmt->close();
              return null;
       }

       // Add a new object
       function addobj($obj)
       {
              if ($obj != null) {
                     $userNameValidated = $this->validateInput($obj->getUsername());

                     // Check if the username already exists
                     $checkQuery = "SELECT * FROM accounts WHERE userName = ?";
                     $stmt = $this->actionSQL->prepare($checkQuery);
                     $stmt->bind_param('s', $userNameValidated);
                     $stmt->execute();
                     $resultCheck = $stmt->get_result();

                     if ($resultCheck->num_rows < 1) {
                            $createDate = $obj->getDateCreate();
                            $passWord_hash = $obj->getPassword();
                            $accountStatus = $obj->getAccountStatus();
                            $name = $obj->getName();
                            $address = $obj->getAddress();
                            $email = $obj->getEmail();
                            $phoneNumber = $obj->getPhoneNumber();
                            $birthdate = $obj->getBirth();
                            $sex = $obj->getSex();
                            $codePermission = $obj->getCodePermission();

                            // Insert the new user
                            $insertQuery = "INSERT INTO accounts 
                                            (userName, passWord, dateCreated, accountStatus, name, address, email, phoneNumber, birth, sex, codePermissions) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $this->actionSQL->prepare($insertQuery);
                            $stmt->bind_param(
                                   'sssssssssss',
                                   $userNameValidated,
                                   $passWord_hash,
                                   $createDate,
                                   $accountStatus,
                                   $name,
                                   $address,
                                   $email,
                                   $phoneNumber,
                                   $birthdate,
                                   $sex,
                                   $codePermission
                            );

                            if ($stmt->execute()) {
                                   return true;
                            }
                     }
                     $stmt->close();
              }
              return false;
       }

       function updateObj($obj)
       {
              if ($obj != null) {
                     $userName = $this->validateInput($obj->getUsername());
                     $passWord_hash = password_hash($this->validateInput($obj->getPassword()), PASSWORD_DEFAULT);
                     $dateCreate = $obj->getDateCreate();
                     $accountStatus = $obj->getAccountStatus();
                     $name = $obj->getName();
                     $address = $obj->getAddress();
                     $email = $obj->getEmail();
                     $phoneNumber = $obj->getPhoneNumber();
                     $birth = $obj->getBirth();
                     $sex = $obj->getSex();
                     $codePermission = $obj->getCodePermission();

                     // Update SQL query
                     $string = "UPDATE accounts 
                                SET passWord = ?, 
                                    dateCreated = ?, 
                                    accountStatus = ?, 
                                    name = ?, 
                                    address = ?, 
                                    email = ?, 
                                    phoneNumber = ?, 
                                    birth = ?, 
                                    sex = ?, 
                                    codePermissions = ? 
                                WHERE userName = ?";

                     $stmt = $this->actionSQL->prepare($string);
                     $stmt->bind_param(
                            'sssssssssss', 
                            $passWord_hash, 
                            $dateCreate, 
                            $accountStatus, 
                            $name, 
                            $address, 
                            $email, 
                            $phoneNumber, 
                            $birth, 
                            $sex, 
                            $codePermission, 
                            $userName
                     );
                     return $stmt->execute();
              }
              return false;
       }

       function updateStateUser($userName, $accountStatus)
       {
              $userNameValidated = $this->validateInput($userName);
              $status = ($accountStatus == '1') ? '0' : '1';
              $string = "UPDATE accounts SET accountStatus = ? WHERE userName = ?";

              $stmt = $this->actionSQL->prepare($string);
              $stmt->bind_param('ss', $status, $userNameValidated);
              return $stmt->execute();
       }
}
