-- PROCEDURE --
DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `secure_attack_sql`(
    IN p_userName VARCHAR(255),
    IN p_password VARCHAR(255)
)
BEGIN
    -- Khai báo biến stmt cho câu truy vấn đã chuẩn bị sẵn
    DECLARE stmt TEXT;

    -- Sử dụng prepared statement thay vì CONCAT
    SET stmt = 'SELECT * FROM accounts WHERE userName = ? AND password = ?';

    -- Chuẩn bị câu lệnh SQL với các tham số an toàn
    PREPARE sql_stmt FROM stmt;
    SET @username = p_userName;
    SET @password = p_password;

    -- Thực thi câu lệnh SQL chuẩn bị sẵn
    EXECUTE sql_stmt USING @username, @password;

    -- Giải phóng tài nguyên sau khi thực thi xong
    DEALLOCATE PREPARE sql_stmt;
END$$

DELIMITER ;