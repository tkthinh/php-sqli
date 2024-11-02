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
              $userName_encode = base64_encode($this->validateInput($code));

              $query = "SELECT COUNT(*) FROM feedback WHERE userName = ? 
                        UNION SELECT COUNT(*) FROM comment WHERE userNameComment = ? 
                        UNION SELECT COUNT(*) FROM orders WHERE userName = ?";
              $stmt = $this->actionSQL->prepare($query);
              $stmt->bind_param('sss', $userName_encode, $userName_encode, $userName_encode);
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
                     $stmt->bind_param('s', $userName_encode);
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
                                   base64_decode($data["userName"]),
                                   base64_decode($data["passWord"]),
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
              $userName_encode = base64_encode($this->validateInput($code));
              $query = "SELECT * FROM accounts WHERE userName = ?";
              $stmt = $this->actionSQL->prepare($query);
              $stmt->bind_param('s', $userName_encode);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($data = $result->fetch_assoc()) {
                     return new AccountDTO(
                            base64_decode($data["userName"]),
                            base64_decode($data["passWord"]),
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
        $userName_encode = base64_encode($this->validateInput($obj->getUsername()));
        $passWord_hash = password_hash($this->validateInput($obj->getPassword()), PASSWORD_BCRYPT);

        // Check if the username already exists
        $checkQuery = "SELECT * FROM accounts WHERE userName = ?";
        $stmt = $this->actionSQL->prepare($checkQuery);
        $stmt->bind_param('s', $userName_encode);
        $stmt->execute();
        $resultCheck = $stmt->get_result();

        if ($resultCheck->num_rows < 1) {
            // Insert the new user
            $insertQuery = "INSERT INTO accounts 
                            (userName, passWord, dateCreated, accountStatus, name, address, email, phoneNumber, birth, sex, codePermissions) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->actionSQL->prepare($insertQuery);
            $stmt->bind_param(
                'sssssssssss',
                $userName_encode,
                $passWord_hash,
                $obj->getDateCreate(),
                $obj->getAccountStatus(),
                $obj->getName(),
                $obj->getAddress(),
                $obj->getEmail(),
                $obj->getPhoneNumber(),
                $obj->getBirth(),
                $obj->getSex(),
                $obj->getCodePermission()
            );

            if ($stmt->execute()) {
                // Get the ID of the inserted record
                $newUserId = $this->actionSQL->insert_id;

                // Retrieve the newly created user details
                $selectQuery = "SELECT * FROM accounts WHERE id = ?";
                $stmt = $this->actionSQL->prepare($selectQuery);
                $stmt->bind_param('i', $newUserId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $newUser = $result->fetch_object(); // Fetch as an object
                    $stmt->close();
                    return $newUser; // Return the new user object
                }
            }
        }
        $stmt->close();
        return false;
    }
    return false;
}



       // Update object
       function updateobj($obj)
       {
              if ($obj != null) {
                     $userName_encode = base64_encode($this->validateInput($obj->getUsername()));
                     
                     // Use password_hash to securely hash the new password
                     $passWord_hash = password_hash($this->validateInput($obj->getPassword()), PASSWORD_BCRYPT);

                     $updateQuery = "UPDATE accounts 
                                   SET passWord = ?, dateCreated = ?, accountStatus = ?, name = ?, 
                                          address = ?, email = ?, phoneNumber = ?, birth = ?, sex = ?, 
                                          codePermissions = ? 
                                   WHERE userName = ?";
                     $stmt = $this->actionSQL->prepare($updateQuery);
                     $stmt->bind_param(
                     'sssssssssss',
                     $passWord_hash,
                     $obj->getDateCreate(),
                     $obj->getAccountStatus(),
                     $obj->getName(),
                     $obj->getAddress(),
                     $obj->getEmail(),
                     $obj->getPhoneNumber(),
                     $obj->getBirth(),
                     $obj->getSex(),
                     $obj->getCodePermission(),
                     $userName_encode
                     );
                     $result = $stmt->execute();
                     $stmt->close();
                     return $result;
              }
              return false;
       }


       // Update account status
       function updateStateUser($userName, $accountStatus)
       {
              $userName_encode = base64_encode($this->validateInput($userName));
              $newStatus = ($accountStatus == '1') ? '0' : '1';

              $updateQuery = "UPDATE accounts SET accountStatus = ? WHERE userName = ?";
              $stmt = $this->actionSQL->prepare($updateQuery);
              $stmt->bind_param('ss', $newStatus, $userName_encode);
              $result = $stmt->execute();
              $stmt->close();
              return $result;
       }
}
