-- Tạo tài khoản chỉ có quyền SELECT
CREATE USER 'user_view'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT ON database_name.users TO 'user_view'@'localhost';

-- Tạo tài khoản có quyền quản lý dữ liệu
CREATE USER 'user_admin'@'localhost' IDENTIFIED BY 'password';
GRANT INSERT, UPDATE, DELETE ON database_name.users TO 'user_admin'@'localhost';

-- Đảm bảo áp dụng các thay đổi quyền
FLUSH PRIVILEGES;

