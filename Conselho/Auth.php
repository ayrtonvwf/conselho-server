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
        $db->query('SET time_zone =  \'+00:00\'');

        $sql = '
            SELECT *
            FROM user_token
            WHERE value = :token_value';
        $statement = $db->prepare($sql);
        $statement->bindValue(':token_value', $token, PDO::PARAM_STR);
        $statement->execute();
        if (!$token = $statement->fetchObject()) {
            http_response_code(401);
            exit;
        }
        if ($token->expires_at < date('Y-m-d H:i:s')) {
            http_response_code(401);
            exit;
        }
    }
}