<?php


// Kết nối chỉ với quyền SELECT để lấy dữ liệu
$pdo_view = new PDO('mysql:host=localhost;dbname=database_name',
                    'user_view',
                    'password');

// Kết nối với quyền quản lý khi thực hiện thao tác cập nhật hoặc xóa
$pdo_admin = new PDO('mysql:host=localhost;dbname=database_name',
                     'user_admin',
                     'password');


function getUser($username) {
    global $pdo_view; // Sử dụng kết nối chỉ có quyền SELECT
    $stmt = $pdo_view->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Lấy thông tin người dùng
$user = getUser("example_user");
print_r($user);


function updateUserPassword($username, $newPassword) {
  global $pdo_admin; // Sử dụng kết nối với quyền UPDATE
  $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
  $stmt = $pdo_admin->prepare("UPDATE users SET password = :password WHERE username = :username");
  $stmt->bindParam(':username', $username);
  $stmt->bindParam(':password', $hashedPassword);
  return $stmt->execute();
}

// Cập nhật mật khẩu người dùng
updateUserPassword("example_user", "new_secure_password");
