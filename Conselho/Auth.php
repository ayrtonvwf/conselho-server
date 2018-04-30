<?php
namespace Conselho;
use PDO;

final class Auth {

    public function check() : void {
        if (!$token = ($_SERVER['HTTP_TOKEN'] ?? null)) {
            die('No token');
        }

        $db_host = getenv('DB_HOST');
        $db_name = getenv('DB_NAME');
        $db_dns = "mysql:host=$db_host;dbname=$db_name;charset=UTF8";
        $db_user = getenv('DB_USER');
        $db_pass = getenv('DB_PASS');
        $db = new PDO($db_dns, $db_user, $db_pass);

        $sql = '
            SELECT user.*
            FROM user
            INNER JOIN user_token ON
                user_token.user_id = user.id AND
                user_token.value = :token_value AND
                user_token.expires_at > NOW()';
        $statement = $db->prepare($sql);
        $statement->bindValue(':token_value', $token, PDO::PARAM_STR);
        $statement->execute();
        if (!$statement->fetch(PDO::FETCH_OBJ)) {
            die('User token not found');
        }
    }
}