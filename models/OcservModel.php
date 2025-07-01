<?php

class OcservModel
{
    public function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function authenticate($user, $pass)
    {
        $file = "/etc/ocserv/paneladmin";
        if (!file_exists($file)) return false;
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            list($fuser, $fpass) = explode(':', $line, 2);
            if ($user === $fuser && password_verify($pass, $fpass)) {
                return true;
            }
        }
        return false;
    }

    public function runCommand($cmd)
    {
        $output = null;
        $status = null;
        exec($cmd . " 2>&1", $output, $status);
        return ['output' => $output, 'status' => $status];
    }

    public function listUsers()
    {
        $users = [];
        $passwdFile = "/etc/ocserv/passwd";
        if (file_exists($passwdFile)) {
            $lines = file($passwdFile, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                $parts = explode(":", $line);
                if (!empty($parts[0])) {
                    $users[] = $parts[0];
                }
            }
        }
        return $users;
    }
}
