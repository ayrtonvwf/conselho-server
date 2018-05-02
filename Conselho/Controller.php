<?php
namespace Conselho;
use Valitron\Validator;
use PDO;

abstract class Controller {
    private $input_data = [];
    private $validation_errors = [];
    private $db;
    
    public function __construct(bool $needs_auth = true)
    {
        if ($needs_auth) {
            $this->check_auth();
        }
        $this->input_data = $this->get_input_data();
    }

    private function check_auth() : bool {
        return (bool) $this->get_user();
    }

    private function get_input_data() : array {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            return $_GET;
        }

        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function get_db_connection() : PDO {
        if (!$this->db) {
            $db_host = getenv('DB_HOST');
            $db_name = getenv('DB_NAME');
            $db_dns = "mysql:host=$db_host;dbname=$db_name;charset=UTF8";
            $db_user = getenv('DB_USER');
            $db_pass = getenv('DB_PASS');
            $this->db = new PDO($db_dns, $db_user, $db_pass);
            $this->db->query('SET time_zone =  \'+00:00\'');
        }
        return $this->db;
    }

    protected function get_pagination(int $limit = 50) : array {
        $page = $this->input_int('page');
        if (!$page) {
            $page = 1;
        }
        $offset = $limit*($page-1);
        return [
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    protected function prettify() : ?int {
        return !empty($_SERVER['HTTP_PRETTIFY']) ? JSON_PRETTY_PRINT : null;
    }
    
    protected function input_raw(string $key) : ?string {
        return $this->input_data[$key] ?? null;
    }

    protected function input_string(string $key) : ?string {
        $value = $this->input_raw($key);

        return !is_null($value) ? trim(strip_tags($value)) : null;
    }

    protected function input_int(string $key) : ?int {
        $value = $this->input_raw($key);
        return !is_null($value) ? (int) $value : null;
    }

    protected function input_bool(string $key) : ?bool {
        $value = $this->input_raw($key);
        return !is_null($value) ? (bool) $value : null;
    }

    protected function get_user() : ?object {
        $sql = '
            SELECT user.*
            FROM user
            INNER JOIN user_token ON
                user_token.user_id = user.id AND
                user_token.value = :token_value AND
                user_token.expires_at > NOW()';
        $pdo = $this->get_db_connection();
        $statement = $pdo->prepare($sql);
        $statement->bindValue(':token_value', $this->get_token(), PDO::PARAM_STR);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_OBJ);
        return $user ? $user : null;
    }

    protected function get_token() : ?string {
        return $_SERVER['HTTP_TOKEN'] ?? null;
    }

    protected function run_validation(array $rules) : bool {
        $data = array_map('strip_tags', $this->input_data);
        $data = array_map('trim', $data);

        $validator = new Validator($data);
        $validator->mapFieldsRules($rules);
        $validator->validate();
        $this->validation_errors = $validator->errors();

        return !$this->validation_errors;
    }

    protected function get_validation_errors() : array {
        return $this->validation_errors;
    }
}