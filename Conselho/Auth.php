<?php
namespace Conselho;
use MongoDB, DateTime, DateInterval;
use MiladRahimi\PHPRouter\Request;

final class Auth {

    public function check(Request $request) : void {
        $token = $this->get_token();
        $db =  $this->get_db();
        
        $this->check_token($db, $token);
    }

    private function get_token() : string {
        if (!$token = $_SERVER['HTTP_TOKEN'] ?? null) {
            http_response_code(400);
            die(json_encode('TOKEN_NEEDED'));
        }
        return $token;
    }

    private function get_db() : MongoDB\Database{
        $db_client = new MongoDB\Client;
        $db_name = getenv('DB_NAME');
        return $db_client->$db_name;
    }

    private function check_token(MongoDB\Database $db, string $token) : void {
        $found_token = $db->user_token->findOne(['value' => $token]);
        if (!$found_token) {
            http_response_code(400);
            die(json_encode('TOKEN_NOT_FOUND'));
        }
        
        if ($found_token->expires_at->toDateTime() <= new DateTime()) {
            http_response_code(400);
            echo $found_token->expires_at->toDateTime()->format('Y-m-d H:i:s')."\n";
            $now = new DateTime();
            echo $now->format('Y-m-d H:i:s')."\n";
            die(json_encode('EXPIRED_TOKEN'));
        }
    }
}