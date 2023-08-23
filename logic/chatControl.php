<?php

session_start();

if (isset($_POST['clear']) && !empty($_POST['clear'])) {
    if ($_POST['clear'] == 'true') {
        fclose(fopen('./static/messages.txt', "w"));
    }
}

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $chatControl = new ChatControl($_SESSION['username']);

    if (isset($_GET['getMessages']) && !empty($_GET['getMessages'])) {
        $chatControl->getMessage();
    }

    if (isset($_GET['getUsers']) && !empty($_GET['getUsers'])) {
        $chatControl->getUsersCount();
    }

    if (isset($_POST['message']) && !empty($_POST['message'])) {
        if ($_POST['action'] == 'send-text') {
            $chatControl->sendMessage($_POST['message']);
        }
    }
} else {
    header('Location: ./index.php');
}

class ChatControl
{
    private string $userName;
    private string $filePath  = './static/messages.txt';

    public function __construct(string $userName)
    {
        $this->userName = $userName;
    }

    public function getMessage()
    {
        if (file_exists($this->filePath)) {

            foreach (file($this->filePath) as $message) {
                $hash      = md5($this->userName);
                $myMessage = strpos($message, $hash);

                if ($myMessage != false) {
                    echo "<span class='own-message bg-success-subtle border border-success-subtle' id='$hash'>";
                } else {
                    echo "$message";
                }
            }
        } else {
            echo "<p>Nenhuma mensagem ainda :(</p>";
        }
    }


    public function getUsersCount()
    {
        if (file_exists($this->filePath)) {

            $online    = 0;

            foreach (file($this->filePath) as $message) {
                $desconnect = strpos($message, '6269d77081ed0d003f6f4fd002dae3a8');
                $connect    = strpos($message, '04b6e1a104ba0ed5e7985abde3e13140');

                if ($connect) {
                    $online++;
                }
                if ($desconnect) {
                    $online--;
                }
            }

            echo $online . ' Online';
        } else {
            echo "0 Online";
        }
    }

    public function sendMessage(string $text = "TESTE")
    {
        $text = $this->clearString($text);

        $hash = md5($this->userName);

        $message = "
        <span id='$hash' class='other-message bg-primary-subtle border border-success-subtle'>
            <b data-bs-toggle='tooltip' data-bs-placement='top' title='Send date: " . date('d/m/Y H:i:s') . "'>$this->userName</b>: $text
        </span><br>";

        file_put_contents($this->filePath, $message . PHP_EOL, FILE_APPEND);
    }

    public function connectChat()
    {
        $pattern = '~^[[:alnum:]-]+$~u';

        if (
            !isset($this->userName) ||
            empty(trim($this->clearString($this->userName))) ||
            ((bool) preg_match($pattern, trim($this->clearString($this->userName)))) == false
        ) {
            header('Location: ./index.php');
        }

        $_SESSION['username'] = $this->clearString($_POST['user']);
    }

    private function clearString(string $string)
    {
        return stripslashes(strip_tags($string));
    }
}
