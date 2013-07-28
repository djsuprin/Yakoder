<?php

class Messager {

    public static function addMessage($message, $type = 'message', $show_times = '1') {
        if ($show_times <= 0) {
            $show_times = 1;
        }
        Site::$db->query("INSERT INTO messages (`type`, `message`, `show_times`) VALUES ('%s', '%s', %d)", $type, $message, $show_times);
    }

    public static function showMessages() {
        // test change
        $messages = Site::$db->query("SELECT * FROM messages WHERE show_times > 0");
		if (Site::$db->affectedRows() > 0) {
			Site::displayView('site', 'show_messages.php', $messages);
        }
        Site::$db->query("UPDATE messages SET show_times = show_times - 1 WHERE show_times > 0");
        Messager::deleteMessages();
    }

    public static function deleteMessages() {
        Site::$db->query("DELETE FROM messages WHERE show_times <= 0");
    }

}

?>
