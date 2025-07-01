<?php
require_once __DIR__ . '/../models/OcservModel.php';

class OcservController
{
    private $model;

    public function __construct()
    {
        $this->model = new OcservModel();
        session_start();
    }

    public function login($post)
    {
        $user = $post['login_user'] ?? '';
        $pass = $post['login_pass'] ?? '';
        if ($this->model->authenticate($user, $pass)) {
            $_SESSION['logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            return "Login gagal.";
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    public function isLoggedIn()
    {
        return $this->model->isLoggedIn();
    }

    public function getUserList()
    {
        return $this->model->listUsers();
    }

    public function handleAction($post)
    {
        $action = $post['action'] ?? '';
        $password = $post['password'] ?? '';
        $username_raw = $post['username'] ?? '';
        if (empty($username_raw)) {
            $username_raw = $post['user_select'] ?? '';
        }
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', $username_raw);

        switch ($action) {
            case 'add_user':
                if (!empty($password) && !empty($username)) {
                    $cmd = "echo -e \"$password\\n$password\" | sudo /usr/bin/ocpasswd -c /etc/ocserv/passwd $username";
                    $result = $this->model->runCommand($cmd);
                    $this->model->runCommand("sudo chmod 644 /etc/ocserv/passwd");
                } else {
                    $result = ['output' => ['Username dan Password tidak boleh kosong.'], 'status' => 1];
                }
                break;
            case 'delete_user':
                if (!empty($username)) {
                    $result = $this->model->runCommand("sudo /usr/bin/ocpasswd -d -c /etc/ocserv/passwd $username");
                    $this->model->runCommand("sudo chmod 644 /etc/ocserv/passwd");
                } else {
                    $result = ['output' => ['Username harus dipilih untuk menghapus.'], 'status' => 1];
                }
                break;
            case 'disconnect_user':
                $result = $this->model->runCommand("sudo /usr/bin/occtl disconnect user $username");
                break;
            case 'show_users':
                $result = $this->model->runCommand("sudo /usr/bin/occtl show users");
                break;
            case 'show_user':
                $result = $this->model->runCommand("sudo /usr/bin/occtl show user $username");
                break;
            case 'list_all_users':
                $result = ['output' => $this->model->listUsers(), 'status' => 0];
                break;
            default:
                $result = ['output' => ['Unknown action'], 'status' => 1];
        }
        return $result;
    }
}
