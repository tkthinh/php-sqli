<?php

// import
// require('../DAL/AbstractionDAL.php');
// require('../DTO/SupplierDTO.php');

class SupplierDAL extends AbstractionDAL
{
       private $actionSQL = null;

       public function __construct()
       {
              parent::__construct();
              $this->actionSQL = parent::getConn();
       }

       // Xóa một đối tượng bởi mã đối tượng 
       function deleteByID($code)
       {
              $check_data_Supplier = "SELECT * FROM product WHERE supplierCode = ?";
              $stmt = $this->actionSQL->prepare($check_data_Supplier);
              $stmt->bind_param("s", $code);
              $stmt->execute();
              $resutl_1 = $stmt->get_result();
              $stmt->close();

              if ($resutl_1->num_rows < 1) {
                     $deleteQuery = "DELETE FROM supplier WHERE codeSupplier = ?";
                     $stmt = $this->actionSQL->prepare($deleteQuery);
                     $stmt->bind_param("s", $code);
                     $result = $stmt->execute();
                     $stmt->close();
                     return $result;
              }
              return false;
       }

       // Xóa một đối tượng bằng cách truyền đối tượng vào
       function delete($obj)
       {
              if ($obj != null) {
                     return $this->deleteByID($obj->getCodeSupplier());
              }
              return null;
       }

       // Lấy ra mảng các đối tượng
       function getListObj()
       {
              $array_list = array();
              $query = 'SELECT * FROM Supplier';
              $result = $this->actionSQL->query($query);

              if ($result->num_rows > 0) {
                     while ($data = $result->fetch_assoc()) {
                            $supplier = new SupplierDTO(
                                   $data['codeSupplier'],
                                   $data['nameSupplier'],
                                   $data['address'],
                                   $data['email'],
                                   $data['brandSupplier'],
                                   $data['phoneNumber']
                            );
                            array_push($array_list, $supplier);
                     }
                     return $array_list;
              }
              return null;
       }

       // Tìm kiếm đối tượng theo từ khóa
       function getListResultSearch($str)
       {
              $array_list = array();
              $searchQuery = "SELECT * FROM Supplier WHERE codeSupplier LIKE ?";
              $stmt = $this->actionSQL->prepare($searchQuery);
              $searchTerm = "%$str%";
              $stmt->bind_param("s", $searchTerm);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result->num_rows > 0) {
                     while ($data = $result->fetch_assoc()) {
                            $supplier = new SupplierDTO(
                                   $data['codeSupplier'],
                                   $data['nameSupplier'],
                                   $data['address'],
                                   $data['email'],
                                   $data['brandSupplier'],
                                   $data['phoneNumber']
                            );
                            array_push($array_list, $supplier);
                     }
              }
              $stmt->close();
              return $array_list;
       }

       // Lấy ra một đối tượng dựa theo mã đối tượng
       function getObj($code)
       {
              $query = "SELECT * FROM Supplier WHERE codeSupplier = ?";
              $stmt = $this->actionSQL->prepare($query);
              $stmt->bind_param("s", $code);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($data = $result->fetch_assoc()) {
                     $supplier = new SupplierDTO(
                            $data['codeSupplier'],
                            $data['nameSupplier'],
                            $data['address'],
                            $data['email'],
                            $data['brandSupplier'],
                            $data['phoneNumber']
                     );
                     $stmt->close();
                     return $supplier;
              }
              $stmt->close();
              return null;
       }

       // Thêm một đối tượng 
       function addObj($obj)
       {
              if ($obj != null) {
                     $codeSupplier = $obj->getCodeSupplier();
                     $checkQuery = "SELECT * FROM Supplier WHERE codeSupplier = ?";
                     $stmt = $this->actionSQL->prepare($checkQuery);
                     $stmt->bind_param("s", $codeSupplier);
                     $stmt->execute();
                     $resultCheck = $stmt->get_result();

                     if ($resultCheck->num_rows < 1) {
                            $insertQuery = "INSERT INTO Supplier (codeSupplier, nameSupplier, address, email, brandSupplier, phoneNumber) 
                                            VALUES (?, ?, ?, ?, ?, ?)";
                            $stmt = $this->actionSQL->prepare($insertQuery);
                            $stmt->bind_param(
                                   "ssssss",
                                   $codeSupplier,
                                   $obj->getNameSupplier(),
                                   $obj->getAddress(),
                                   $obj->getEmail(),
                                   $obj->getBrandSupplier(),
                                   $obj->getPhoneNumber()
                            );
                            $result = $stmt->execute();
                            $stmt->close();
                            return $result;
                     }
                     $stmt->close();
              }
              return false;
       }

       // Sửa một đối tượng
       function upadateObj($obj)
       {
              if ($obj != null) {
                     $updateQuery = "UPDATE Supplier 
                                     SET nameSupplier = ?, 
                                         address = ?, 
                                         email = ?, 
                                         brandSupplier = ?, 
                                         phoneNumber = ? 
                                     WHERE codeSupplier = ?";
                     $stmt = $this->actionSQL->prepare($updateQuery);
                     $stmt->bind_param(
                            "ssssss",
                            $obj->getNameSupplier(),
                            $obj->getAddress(),
                            $obj->getEmail(),
                            $obj->getBrandSupplier(),
                            $obj->getPhoneNumber(),
                            $obj->getCodeSupplier()
                     );
                     $result = $stmt->execute();
                     $stmt->close();
                     return $result;
              }
              return false;
       }
}
