<?php

namespace AppBundle\Services\Messenger;

use PDO;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class Messenger
{

    public $db;
    public $config;
    public $isNewMessage = false;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->config = $this->arrayToObject($this->config);
        $this->db = Database::getInstance($this->config->database);
        date_default_timezone_set('Asia/Jerusalem');
    }

    public function response($array)
    {
        return new JsonResponse($array);
    }

    public function arrayToObject($array)
    {
        $json = json_encode($array);
        return json_decode($json);
    }

    public function isNewMessage()
    {
        return $this->isNewMessage;
    }

    public function openChat($options)
    {
        $userAttributes = new UserAttributes();

        $chatSession = $userAttributes->get($this->config->messengerSession, array($options['userId'], $options['contactId']));
        if (count($chatSession) == 0) {
            $userAttributes->post($this->config->messengerSession, array($options['userId'], $options['contactId']));
            return true;
        }

        return false;
    }

    public function closeChat($options)
    {
        $sql = "DELETE FROM " . $this->config->messengerSession->table . " WHERE userId = ? AND contactId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $options['contactId'], \PDO::PARAM_INT);
        $success = ($stmt->execute()) ? true : false;
        return $this->response(array('success' => $success));
    }

    public function getActiveChats($options)
    {
        $userAttributes = new UserAttributes();
        $activeChats = array();

        $sql = "
			SELECT
				s.userId, s.contactId, u.username FROM " . $this->config->messengerSession->table . " s
			JOIN
				" . $this->config->users->table . " u
			ON
				(s.contactId = u.id)
			WHERE
				s.userId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {

            $chat = new Chat(array(
                'userId' => $row['userId'],
                'contactId' => $row['contactId']
            ));

            if (!$chat->isForbidden()) {
                $activeChats[] = array(
                    'id' => $row['contactId'],
                    'name' => $row['username']
                );
            }
        }

        return $activeChats;
    }

    public function checkActiveChatsNewMessages($options)
    {
        $result = array();
        $dateTime = array();
        $userAttributes = new UserAttributes();

        $allChats = $userAttributes->get($this->config->messengerSession, array($options['userId']));

        if (count($allChats)) {

            $startTime = time();
            while (time() - $startTime < 10) {

                foreach ($allChats as $chatOptions) {
                    $chat = new Chat($chatOptions);
                    $newMessages = $chat->getNewMessages();

                    if (count($newMessages) > 0) {
                        $allowedToReadMessage = ($chat->user()->isPaying() || $chat->contact()->isPaying()) ? true : false;
                        foreach ($newMessages as $message) {
                            $this->isNewMessage = true;
                            $messageDateObject = new \DateTime($message['date']);
                            $timestamp = $messageDateObject->getTimestamp();
                            $date = date("m/d/Y", $timestamp);
                            $time = date("H:i", $timestamp);

                            $text = ($message['fromUser'] != $chat->user()->getId() && !$allowedToReadMessage)
                                ? ''
                                : nl2br(urldecode($message['message']));

                            $result[] = array(
                                "id" => $message['messageId'],
                                "from" => $chat->contact()->getId(),
                                "text" => $text,
                                "dateTime" => $date . ' ' . $time,
                                "userImage" => $chat->contact()->getImage(),
                                "userName" => $chat->contact()->getNickName(),
                                "allowedToRead" => $allowedToReadMessage
                            );
                        }
                    }
                }

                if ($this->isNewMessage()) {
                    $timestamp = time();
                    $time = date("i:s", $timestamp);
                    $dateTime[] = $time;

                    foreach ($result as $message) {
                        $chat->setMessageAsDelivered($message['id']);
                    }

                    return array(
                        "newMessages" => $result,
                        "currentUserHasPoints" => $chat->user()->hasPoints(),
                        "MinSec" => $dateTime,
                    );

                }

                usleep(500);
            }

        }

        $timestamp = time();
        $time = date("i:s", $timestamp);
        $dateTime[] = $time;
        return array("newMessages" => $result, "MinSec" => $dateTime);
    }

    public function checkMessagesIfRead($messages)
    {


        $readMessages = array();
        $startTime = time();
        while (time() - $startTime < 10) {
            if (mb_strlen(trim($messages), "utf-8") > 0) {
                $sql = "SELECT messageId FROM " . $this->config->messenger->table . " WHERE messageId IN (" . trim($messages) . ") AND isRead = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                foreach ($stmt->fetchAll() as $row) {
                    $readMessages[] = $row['messageId'];
                }

                if (count($readMessages)) {
                    return $readMessages;
                }
            }

            usleep(500);
        }

        return $readMessages;
    }


    public function checkNewMessages($options)
    {

        $users = array();
        $messagesIds = array();

        $sql = "
			SELECT 
				m.messageId, m.fromUser, m.message, m.isRead, m.isDelivered, u.username, u.gender_id, f.ext, f.id as photoId FROM " . $this->config->messenger->table . " m
			JOIN 					 
				" . $this->config->users->table . " u 
			ON
				( m.fromUser = u.id)
				
			JOIN
			      file f
			ON 
			      ( f.user_id = u.id AND f.is_main = 1 AND f.is_valid = 1)  
			WHERE 
				m.toUser = ? AND m.isRead = 0 AND m.isNotified = 0 AND m.date > ?";


        $this->db->exec("set names utf8");
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $options['lastLoginAt']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $row) {
            $message = strip_tags(urldecode($row['message']));
            if (strlen($row['message']) > 75) {
                $message = substr($message, 0, 74) . '...';
            }

            $user = array(
                "id" => $row['fromUser'],
                "name" => urldecode(strip_tags(urlencode(nl2br($row['username'])))),
                "isDelivered" => $row['isDelivered'],
                "isRead" => $row['isRead'],
                "messageId" => $row['messageId'],
                "message" => $message,
                "photo" => 'https://polydate.co.il/media/photos/' . $row['fromUser'] . '/' . $row['photoId'] . '.' . $row['ext'],
            );

            if (!in_array($user, $users)) {
                $users[] = $user;
            }

            $messagesIds[] = $row['messageId'];
        }


        if (count($messagesIds)) {
            $sql = "UPDATE messenger SET isNotified = 1 WHERE messageId IN (" . implode(',', $messagesIds) . ")";
            $this->db->query($sql);
        }


//        return $users;
        return $this->response(array("fromUsers" => $users));
    }


    public function testCheckNewMessagesMobile($options, $base_url = 'www.polyinlove.com')
    {

        $users = array();
        $messagesIds = array();

        $sql = "
			SELECT 
				m.messageId, m.fromUser, m.message, m.isRead, m.isDelivered, u.username, u.gender_id, f.ext, f.id as photoId FROM " . $this->config->messenger->table . " m
			JOIN 					 
				" . $this->config->users->table . " u 
			ON
				( m.fromUser = u.id)
				
			JOIN
			      file f
			ON 
			      ( f.user_id = u.id AND f.is_main = 1 AND f.is_valid = 1)  
			WHERE 
				m.toUser = ? AND m.isRead = 0 AND m.isNotified = 0 AND m.date > ?";

        $this->db->exec("set names utf8");
        $stmt = $this->db->prepare($sql);
        $date = strtotime("-12 seconds");
        $date = date("m/d/Y h:i:s ", $date);


        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);

        $stmt->bindParam(2, $date);
        $stmt->execute();
        $result = $stmt->fetchAll();
//		var_dump($result);die;
        foreach ($result as $row) {
            $message = strip_tags(urldecode($row['message']));
            if (strlen($row['message']) > 75) {
                $message = substr($message, 0, 74) . '...';
            }


            $user = array(
                "id" => $row['fromUser'],
                "name" => urldecode(strip_tags(urlencode(nl2br($row['username'])))),
                "isDelivered" => $row['isDelivered'],
                "isRead" => $row['isRead'],
                "messageId" => $row['messageId'],
                "message" => $message,
                "photo" => 'https://' . $base_url . '/media/photos/' . $row['fromUser'] . '/' . $row['photoId'] . '.' . $row['ext'],
            );

            if (!in_array($user, $users)) {
                $users[] = $user;
            }

            $messagesIds[] = $row['messageId'];
        }


        if (count($messagesIds)) {
            $sql = "UPDATE messenger SET isNotified = 1 WHERE messageId IN (" . implode(',', $messagesIds) . ")";
            $this->db->query($sql);
        }


        return $this->response(array("fromUsers" => $users));
    }

    public function checkNewMessagesMobile($options)
    {


        $sql = "
			SELECT
				COUNT(m.messageId) as newMessagesNumber FROM " . $this->config->messenger->table . " m
			JOIN
				" . $this->config->users->table . " u
			ON
				m.fromUser = u.id AND m.fromUser <> ? AND u.is_active = 1 AND u.is_non_locked = 1 AND u.is_frozen = 0
			WHERE
				m.toUser = ? AND m.isRead = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['contactId'], \PDO::PARAM_INT);
        $stmt->bindParam(2, $options['userId'], \PDO::PARAM_INT);
        $stmt->execute();
        return $this->response($stmt->fetch());
    }

    public function checkDialogNewMessages($options)
    {
        $result = array();
        $dateTime = array();
        $startTime = time();
        //var_dump($startTime);
        //	while(time() - $startTime < 10) {

        $dialog = new Dialog($options);
        $newMessages = $dialog->getNewMessages();
//			var_dump($newMessages);die;

        //return $this->response(array("newMessages" => $newMessages, "MinSec" => $dateTime));
        //die();

        if (count($newMessages) > 0) {
            $allowedToReadMessage = ($dialog->user()->isPaying() || $dialog->contact()->isPaying()) ? true : false;

            foreach ($newMessages as $message) {
                $this->isNewMessage = true;
                $messageDateObject = new \DateTime($message['date']);
                $timestamp = $messageDateObject->getTimestamp();
                $date = date("m/d/Y", $timestamp);
                $time = date("H:i", $timestamp);
                $text = ($message['fromUser'] != $dialog->user()->getId() && !$allowedToReadMessage)
                    ? ''
                    : nl2br(urldecode($message['message']));

                $result[] = array(
                    "id" => $message['messageId'],
                    "from" => $dialog->contact()->getId(),
                    "text" => $text,
                    "dateTime" => $date . ' ' . $time,
                    "userImage" => $dialog->contact()->getImage(),
                    "userName" => $dialog->contact()->getNickName(),
                    "allowedToRead" => $allowedToReadMessage,
                );
            }
        }

        if ($this->isNewMessage()) {
            $timestamp = time();
            $time = date("i:s", $timestamp);
            $dateTime[] = $time;

            foreach ($result as $message) {
                $dialog->setMessageAsDelivered($message['id']);
            }

            return array(
                "newMessages" => $result,
                "currentUserHasPoints" => $dialog->user()->hasPoints(),
                "MinSec" => $dateTime,

            );
        }

        $timestamp = time();
        $time = date("i:s", $timestamp);
        $dateTime[] = $time;
        $sql = "SELECT isRead FROM messenger WHERE messageId = ? ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$options['lastMess']]);
        $lastMess = $stmt->fetchAll();
        return array("newMessages" => $result, "MinSec" => $dateTime, 'lastIsRead' => $lastMess);
    }


    public function oldCheckDialogNewMessages($options)
    {
        $result = array();
        $dateTime = array();
        $startTime = time();


        $dialog = new Dialog($options);
        $newMessages = $dialog->getNewMessages();


        if (count($newMessages) > 0) {
            $allowedToReadMessage = /*($dialog->user()->isPaying() || $dialog->contact()->isPaying()) ?*/
                true /*: false*/
            ;

            foreach ($newMessages as $message) {
                $this->isNewMessage = true;
                $messageDateObject = new \DateTime($message['date']);
                $timestamp = $messageDateObject->getTimestamp();
                $date = date("m/d/Y", $timestamp);
                $time = date("H:i", $timestamp);
                $text = nl2br(urldecode($message['message']));

                $result[] = array(
                    "id" => $message['messageId'],
                    "from" => $dialog->contact()->getId(),
                    "text" => $text,
                    "dateTime" => $date . ' ' . $time,
                    "userImage" => $dialog->contact()->getImage(),
                    "userName" => $dialog->contact()->getNickName(),
                    "allowedToRead" => $allowedToReadMessage,
                );
            }
        }


        if ($this->isNewMessage()) {
            $timestamp = time();
            $time = date("i:s", $timestamp);
            $dateTime[] = $time;

            foreach ($result as $message) {
                $dialog->setMessageAsDelivered($message['id']);
            }

            return array(
                "newMessages" => $result,
                "currentUserHasPoints" => $dialog->user()->hasPoints(),
                "MinSec" => $dateTime,

            );
        }


        $timestamp = time();
        $time = date("i:s", $timestamp);
        $dateTime[] = $time;

        if (isset($options['lastMess']) && !empty($options['lastMess'])) {
            $sql = " SELECT * FROM `messenger` WHERE `MessageId` = " . $options['lastMess'];
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $mess = $stmt->fetchAll();
        } else if (isset($options['notReadMess']) && !empty($options['notReadMess'])) {
            $messageString = $options['notReadMess'];
            $sql = "SELECT  MessageId FROM `messenger` WHERE `isRead` = 1 AND `MessageId` IN (" . $messageString . ")";
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $mess = $stmt->fetchAll();
        }
        return array("newMessages" => $result, "MinSec" => $dateTime, 'lastIsRead' => $mess ?? '');
    }


    public function getNewMessagesNumber($options = false)
    {

        $sql = "
			SELECT
				m.messageId FROM " . $this->config->messenger->table . " m	
			JOIN
				users u
			ON
				u.id = m.fromUser 
				AND u.userBlocked = 0 
				AND u.userFrozen = 0 
				AND u.userNotActivated = 0
			WHERE
				m.toUser = ? AND m.isRead = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
        $stmt->execute();
        return count($stmt->fetchAll());
    }


    public function getUsersMessages($page, $perPage, $userId, $conn)
    {
        $offset = ($page - 1) * $perPage;
        $userCond = $userId === null ? '' : 'WHERE m.fromUser = ' . $userId . ' OR m.toUser = ' . $userId;


        $sql = "
            SELECT
              m.messageId,
              m.fromUser,
              m.toUser,
              m.message,
              m.date,
              fromUser.username as fromUsername,
              toUser.username as toUsername
            FROM
              messenger m
            LEFT JOIN
              user fromUser
            ON fromUser.id = m.fromUser

            LEFT JOIN
              user toUser
            ON toUser.id = m.toUser
            " . $userCond . "
            ORDER BY m.messageId DESC
            LIMIT " . $offset . "," . $perPage;

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $messages = $stmt->fetchAll();
        foreach ($messages as $key => $item) {
            $messages[$key]['message'] = nl2br(urldecode($item['message']));
            $messages[$key]['fromUsername'] = strip_tags(urldecode(nl2br($item['fromUsername'])));
        }

        return $messages;

    }

    public function getUsersMessagesNumber($userId)
    {
        $userCond = $userId === null ? '' : 'WHERE m.fromUser = ' . $userId . ' OR m.toUser = ' . $userId;

        $sql = "
            SELECT
              COUNT(m.messageId) as number
            FROM
              messenger m
            LEFT JOIN
              user fromUser
            ON fromUser.id = m.fromUser

            LEFT JOIN
              user toUser
            ON toUser.id = m.toUser
            " . $userCond;

        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['number'];

    }

    public function useFreePointToReadMessage($messageId, $userId)
    {
        $user = new User($userId);
        if ($user->hasPoints()) {
            $sql = "SELECT fromUser, message FROM " . $this->config->messenger->table . " WHERE messageId = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $messageId, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();

            $result = array(
                'success' => true,
                'message' => array(
                    'from' => $row['fromUser'],
                    'text' => nl2br(urldecode($row['message'])),
                )
            );

            $sql = "UPDATE " . $this->config->messenger->table . " SET isRead = 1, isDelivered = 1 WHERE messageId = '" . $messageId . "'";
            $stmt = $this->db->query($sql);

            $sql = "UPDATE user SET points = points - 1 WHERE id = '" . $userId . "'";
            $stmt = $this->db->query($sql);

            return $this->response($result);
        }

        return $this->response(array('success' => false));
    }

    public function setMessageAsNotified($messageId)
    {
        //$sql = "UPDATE " . $this->config->messenger->table . " SET isRead = 1 WHERE messageId = ? AND toUser = ? AND fromUser = ? AND isRead = 0";
        $sql = "UPDATE " . $this->config->messenger->table . " SET isNotified = 1 WHERE messageId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $messageId, \PDO::PARAM_INT);
        if ($stmt->execute())
            return true;

        return false;
    }

    /*
    public function checkMessagesIfRead($messages){
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);
        $readMessages = array();
        if(mb_strlen(trim($messages), "utf-8") > 0){
            $sql = "SELECT messageId FROM " . $this->config->messenger->table . " WHERE messageId IN (" . trim($messages) . ") AND isRead = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $row){
                $readMessages[] = $row['messageId'];
            }
        }
        return $readMessages;
    }
    */

    public function removeMessages($messagesIdsString)
    {
        //$sql = "SELECT MAX(id) FROM "me"

        $sql = "DELETE FROM " . $this->config->messenger->table . " WHERE messageId IN (" . $messagesIdsString . ")";
        echo $sql;
        $this->db->query($sql);
    }


    public function deleteMessage($id, $delete, $user_id, $contact_id)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        try {
            $sql = "SELECT * FROM `messengerLastMessages` WHERE `messageId` = " . $id . " OR  `messageId2` = " . $id;
            $stmt = $this->db->query($sql);
            $lastMessage = $stmt->fetch();

            $who_delete = ($delete === 'false' || $delete === false) ? 'msgToDel' : 'msgFromDel';
//             var_dump($who_delete, $delete, $delete === 'false' , $delete === false);die;
            $sql = "UPDATE " . $this->config->messenger->table . " SET " . $who_delete . " = 1 WHERE messageId = $id";
            $this->db->query($sql);

            if ($lastMessage) {
                $sql = "SELECT MAX(messageId) FROM " . $this->config->messenger->table .
                    " WHERE (fromUser = $user_id AND toUser = $contact_id AND msgFromDel = 0) OR (fromUser = $contact_id AND toUser = $user_id AND msgToDel = 0) ";
                // WHERE (" . $who_delete . " = 0) AND ( (`fromUser` = $user_id AND `toUser` = $contact_id) OR (`fromUser` = $contact_id AND `toUser` = $user_id) )";
                // (msgFromDel = 0) AND (fromUser = 3212 OR toUser = 3212)
                $stmt = $this->db->query($sql);

                $lastNotDeletedMessage = $stmt->fetch();
                $message = $lastMessage['user1'] == $user_id ? 'messageId' : 'messageId2';
                $messageId = $lastNotDeletedMessage[0] ? $lastNotDeletedMessage[0] : 'null';

                $sql = "UPDATE `messengerLastMessages` SET " . $message . " = " . $messageId . " WHERE `" . $message . "` = " . $id;
                $this->db->query($sql);
            }
            return true;
        } catch (Exception $e) {
            return $e;
        }


    }

    public function setUserDevice($mob_os, $token, $userId, $model = '', $uuid = '', $os = '')
    {

        $sql = "SELECT * FROM `users_device` WHERE `$mob_os` = '$token'";
        $res = $this->db->query($sql);
        $res = $res->fetch();
//        var_dump($res);die;
        if (!$res) {
//             var_dump(123);
            $sql = "INSERT INTO `users_device`(`user_id`, `$mob_os`, `model`, `uuid`,`os_version`) VALUES ($userId, '$token', '$model', '$uuid', '$os')";
            return $this->db->query($sql) ? true : false;
        } else {

            return false;
            //commented now for testers can test the app
//             if ($res['user_id'] != $userId) {
//                 $res2 = $this->db->query("SELECT ban_reason FROM user WHERE id = $userId");
//                 $ban_reason = $res2->fetch();
//                 $sql = "UPDATE user SET is_active = 0, ban_reason = '" . $ban_reason['ban_reason'] . "\n" . 'חסימה אוטומתית. כבר נרשם עם משתמש  ' . $res['user_id'] . "'";
//                 $sql .= ' WHERE id = ' . $userId;
//                 var_dump($sql);die;
//
//                 $this->db->query($sql);
//
//             }
        }

    }


    public function pushNotification2($data, $fcm_auth_key, $site_name = 'PolyinLove', $base_url = 'www.polyinlove.com')
    {

        $userDevices = $data['users_devices'];

        if (isset($userDevices['ios']) && is_array($userDevices['ios'])) {
            foreach ($userDevices['ios'] as $device) {
                $this->iosPush($data['message'], $device, $data['user_id']);
            }
        }

        if (isset($userDevices['browser']) && is_array($userDevices['browser'])) {
            $fields = array(
                "notification" => array(
                    "body" => $data['message'],
                    "title" => $site_name,//$this->config->gcm->title,
                    "icon" => "/images/icon.png",
                    'click_action' => htmlspecialchars($base_url . "user/messenger/dialog/open/userId:" . $data['contact_id'] . "/contactId:" . $data['user_id'], ENT_COMPAT),
                ),
                'priority' => 'high',
            );

            if (isset($data['isVideo'])) {
                $fields['data']['video'] = $data['isVideo'];
            }

            $headers = array(
                'Authorization: key=' . $fcm_auth_key,
                'Content-Type: application/json'
            );

            foreach ($userDevices['browser'] as $token) {

                $fields['to'] = $token;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

                $result = curl_exec($ch);

                if ($result === FALSE) {
                    die('Curl failed: ' . curl_error($ch));
                }

                curl_close($ch);
            }
        }

        if (isset($userDevices['android']) && !empty($userDevices['android'])) {

            foreach ($userDevices['android'] as $token) {

                $fields2 = array(
                    'message' => $data['message'],
                    'count' => 1,

                    'content-available' => '0',
                    'userFrom' => $data['user_id'],
                    'image' => $data['image'],
                    'vibrate' => 1,
                    'notId' => time(),
                    "titleMess" => $data['titleMess'] ?? 'New message!',
                    "onlyInBackgroundMode" => $data['onlyInBackgroundMode'] ?? true,
                    'userId' => $data['user_id'],
                    'url' => $data['url'] ?? '/dialog',
                    'type' => $data['type'] ?? 'linkIn',
                    'android_channel_id' => $data['android_channel_id'] ?? $site_name,
                    'title' => $data['titleMess'] ?? 'New mesage!',
                    'body' => $data['message']

                );

                if (isset($data['bingoData'])) {
//                    var_dump('in isset bingodata');
                    $fields2['bingoData'] = $data['bingoData'];
                }

//                var_dump($fields2);
//                var_dump($data);


                if (isset($data['isVideo'])) {
                    $fields['video'] = $data['isVideo'];
                }

                $fields = array(
                    'to' => $token,
                    'data' => $fields2,
                    'priority' => 'normal',
                    'notification'=>$fields2
                );

                $headers = array(
                    'Authorization: key=' . $fcm_auth_key,
                    'Content-Type: application/json'
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

                $result = curl_exec($ch);

                if ($result === FALSE) {
                    die('Curl failed: ' . curl_error($ch));
                }

                curl_close($ch);
//                var_dump($result);
            }

        }

        //   echo $token;
        return $result ?? false;
    }


    // Put your device token here (without spaces):

    public function iosPush($mess, $deviceToken, $userId)
    {

        // Put your device token here (without spaces):
        $deviceToken = trim($deviceToken);
        $passphrase = 'interdate';

        $ctx = stream_context_create();

        stream_context_set_option($ctx, 'ssl', 'local_cert', $_SERVER['DOCUMENT_ROOT'] . '/apns_dev.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
        //'ssl://gateway.push.apple.com:2195', $err,
            'ssl://gateway.sandbox.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp) {
            // return $errstr;
            // exit("Failed to connect: $err $errstr" . PHP_EOL); # 500 error
        }


        $body['aps'] = array(
            'alert' => $mess,
            'sound' => 'default',
            'count' => 1,
        );
        $body['userFrom'] = $userId;
//        $body = array(
//            'apns' => array(
//                'alert' => $mess,
//                'sound' => 'default',
//                'count' => 1,
//            ),
//            'userId' => $userId,
//        );

        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));
        fclose($fp);
        //var_dump($result) ;
    }


    public function getPushToken(&$data, $type)
    {
        $sql = 'SELECT ' . $type . ' AS token FROM `users_device` ud 
                JOIN user u ON (u.id = ud.user_id)
                WHERE ud.user_id IN (' . $data['users'] . ') AND ud.' . $type . ' IS NOT NULL AND u.is_sent_push = 1';
        $stmt = $this->db->prepare($sql);
//        var_dump($sql);die;
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $res = $stmt->fetchAll();
        $data[$type] = $res;
    }


    public function adminAndroidPush($data, $fcm_auth_key, $page = 0)
    {

        $max_per_1 = 500;
        $need_more = false;

        $tokens = [];


        $count = count($data['android']);

        $now = $page * $max_per_1;

        if ($count - $now > $max_per_1) {
            $need_more = true;
        }

        for ($i = $now; $i < $now + $max_per_1; $i++) {

            if (!isset($data['android'][$i])) {
                $need_more = false;
                break;
            }

            $tokens[] = $data['android'][$i]['token'];

        }

//
//dump($data);die;
        $pushData = array(
            'registration_ids' => $tokens,
            'data' => array(
                'title' => $data['titleMess'],
                'message' => $data['message'],
                'count' => 1,
                'color' => '#e20020',
                'content-available' => '0',
                'userFrom' => $data['user_id'],
                'image' => $data['image'],
                'vibrate' => 1,
                'notId' => time(),
                "titleMess" => $data['titleMess'] ?? 'New message arrived!',
                "onlyInBackgroundMode" => $data['onlyInBackgroundMode'] ?? true,
                'userId' => $data['user_id'],
                'url' => $data['url'] ?? '/dialog',
                'type' => $data['type'] ?? 'linkIn',
                'priority' => 'height',
            ),
        );

        $headers = array(
            'Authorization: key=' . $fcm_auth_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pushData));

        $result = curl_exec($ch);

//        if ($result === FALSE) {
//            die('Curl failed: ' . curl_error($ch));
//        }

        curl_close($ch);

//        dump($result);die;
        if ($need_more) {
            $this->adminAndroidPush($data, ++$page);
        }


    }


    public function adminWebPush($data, $fcm_auth_key, $page = 0)
    {

        $max_per_1 = 500;
        $need_more = false;

        $count = count($data['browser']);

        $now = $page * $max_per_1;

        if ($count - $now > $max_per_1) {
            $need_more = true;
        }


        if (isset($data['browser']) && is_array($data['browser'])) {
            $fields = array(
                'notification' => array(
                    'body' => $data['message'],
                    'title' => $data['titleMess'],
                    'icon' => '/images/icon.png',
                    'click_action' => htmlspecialchars($data['webUrl'], ENT_COMPAT),
                ),
                'priority' => 'high',
            );


            $headers = array(
                'Authorization: key=' . $fcm_auth_key,
                'Content-Type: application/json'
            );

            $tokens = [];

            for ($i = $now; $i < $now + $max_per_1; $i++) {
                if (!isset($data['browser'][$i])) {
                    $need_more = false;
                    break;
                }
                $tokens[] = $data['browser'][$i]['token'];
            }

            $fields['registration_ids'] = $tokens;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            $result = curl_exec($ch);

//                if ($result === FALSE) {
//                    die('Curl failed: ' . curl_error($ch));
//                }

            curl_close($ch);
//var_dump($result);
            if ($need_more) {
                $this->adminWebPush($data, ++$page);
            }

        }
    }

    public function pushNotification1($data, $fcm_auth_key, $site_name, $base_url)
    {

        $data['users_devices'] = $this->getUsersDevises($data['contact_id']);
        return $this->pushNotification2($data, $fcm_auth_key, $site_name, $base_url);
    }


    private function getUsersDevises($userId)
    {
        $sql = "SELECT * FROM `users_device` WHERE `user_id` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $res = $stmt->fetchAll();
        $usersDevices = array();
//        var_dump($res);

        if ($res) {
            foreach ($res as $device) {
                if (isset($device['android']) and !empty($device['android'])) {
                    $usersDevices['android'][] = $device['android'];
                } elseif (isset($device['ios']) and !empty($device['ios'])) {
                    $usersDevices['ios'][] = $device['ios'];
                } elseif (isset($device['browser']) && !empty($device['browser'])) {
                    $usersDevices['browser'][] = $device['browser'];
                }
            }
            return $usersDevices;
        }

        return false;
    }

//    public function isNotSentToday($user, $contact){
//        $date = new \DateTime('now');
//        $date1 = $date->format("Y-m-d 00:00:00");
//        $date2 = $date->format("Y-m-d 23:59:59");
//        $sql = "SELECT msgId FROM " . $this->config->messenger->table . " WHERE fromUser = ? AND toUser = ? AND date BETWEEN ? AND ?";
//        $stmt = $this->db->prepare($sql);
//        $stmt->bindParam(1, $user);
//        $stmt->bindParam(2, $contact);
//        $stmt->bindParam(3, $date1);
//        $stmt->bindParam(4, $date2);
//        $stmt->execute();
//        return ( count($stmt->fetchAll()) == 1 ) ? true : false;
//    }


    /**
     * @param $contactId int  The id of the user that write to
     *
     * @param $userId int  The id of the user that write from
     *
     * Check if the contact was write to the user before
     *
     * @return boolean
     */
    public function hasChat($contactId, $userId)
    {
        $sql = "select * FROM `messenger` WHERE `fromUser` = ? AND `toUser` = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $contactId, \PDO::PARAM_INT);
        $stmt->bindParam(2, $userId, \PDO::PARAM_INT);
        $stmt->execute();

//        var_dump($stmt->fetch());die;
        return $stmt->fetch();
    }


    /**
     * @param $contact object of class User
     * @param $user object of class Contact
     *
     * Check if contact set like to user
     *
     * @return boolean
     */
    public function isLiked($contact, $user)
    {

        $sql = "SELECT id FROM like_me WHERE (from_id = ? AND to_id = ?) OR (from_id = ? AND to_id = ? AND is_bingo = 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $contact);
        $stmt->bindParam(2, $user);
        $stmt->bindParam(3, $user);
        $stmt->bindParam(4, $contact);
        $stmt->execute();
        return (boolean)$stmt->rowCount();
    }


    /**
     * @param $to object of User   the contact user
     *
     * @param $from object of User  the current user
     *
     *  Check if current user can write to contact user
     *
     * @return boolean
     */
    public function CheckIfCanWriteTo($from, $to)
    {

        if ($from->getId() == 111 || $to->getId() == 111) { // if message to or from admin - all users can write
            return true;
        }

        $canWrite = false;
        if ($from->age() >= $to->getAgeFrom() && $from->age() <= $to->getAgeTo()) {

            $canContact = [];
            $genders = $to->getContactGender();

            foreach ($genders as $gender) {
                $canContact[] = $gender->getId();
            }

            if (empty($canContact) || in_array($from->getGender()->getId(), $canContact)) {
                // if the user not selected any gender that can write to him then all the genders can
                $canWrite = true;
            }
        }


        if (!$canWrite && ($this->hasChat($to->getId(), $from->getId()) || $this->isLiked($to, $from))) {
            $canWrite = true;
        }

        return $canWrite;
    }


//    private function browserPush($data) {
//        $fields = array(
//            'to' 	=> $token,
//            "notification" => array(
//                "body" => $data['message'],
//                "title" => 'Polydate',//$this->config->gcm->title,
//                "icon" => "https://www.nyrichdate.com/images/icon.png",
//                'click_action' => htmlspecialchars("https://www.polydate.co.il/he/user/messenger/dialog/open/userId:5766/contactId:" . ,ENT_COMPAT),
//            ),
//            'priority' => 'high',
//        );
//
//        if ($isVideo) {
////                    $data['video'] = $isVideo;
//        }
//
////                $fields = array(
////                    'to' => $token,
////                    'data' => $data,
////                    'priority' => 'high',
////                );
//
//        $headers = array(
//            'Authorization: key=' . 'AAAAmwWlZkE:APA91bFrM0EsRUDBXGJDfhIhvyBZ7-c3Gocp8m4OYGZ8glD2bRQ6FmKmajQB_43EFbr7BmeFsChyNJMoEaBHsLORIUToK-3SuvH-8gi_L15f2I9MxdfiJLzsQIFHGYLvle8CR_p93qM9',
//            'Content-Type: application/json'
//        );
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
//
//        $result = curl_exec($ch);
//
//        if ($result === FALSE) {
//            die('Curl failed: ' . curl_error($ch));
//        }
//
//        curl_close($ch);
////                var_dump($result);
//    }
//
//    private function androidPush($data) {
//        $data = array(
//            'message' => $mess,
//            'count' => 5,
//            'sound' => 'dfault',
//            'content-available' => '0',
//            'userFrom' => $user_id,
//            'image' => $image,
//            'vibrate' => 1,
//            'notId' => time(),
//            "titleMess" => 'הגיעה הודעה חדשה',
//            "onlyInBackgroundMode" => 1,
//            'userId' => $user_id,
//        );
//
//        if ($isVideo) {
//            $data['video'] = $isVideo;
//        }
//
//        $fields = array(
//            'to' => $token,
//            'data' => $data,
//            'priority' => 'high',
//        );
//
//        $headers = array(
//            'Authorization: key=' . 'AAAAUqv2OlE:APA91bEY1KkOtqJbB1Y7n1hxQcuXI4cWhKc2HNd3jgr__x-YqeMFrM3zC9lGyu49HcoDRILUdBhTubI9ZXQg08hiBvY9pT-hjGzTxKRBhBd4fpNCjugqE_mAldjQsVeDETzpwEuCnbFP',
//            'Content-Type: application/json'
//        );
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
//
//        $result = curl_exec($ch);
//
//        if ($result === FALSE) {
//            die('Curl failed: ' . curl_error($ch));
//        }
//
//        curl_close($ch);
//        var_dump($result);
//    }


    /**
     *
     * @param $messages array [form: int, to: int, message: text]
     *
     * Send message for each array in $messages
     *
     * @return null
     */
    public function sendCallMissingMessage($messages)
    {
        foreach ($messages as $message) {
            $sql = "INSERT INTO messenger (`fromUser`, `toUser`, `message`, `date`, `isRead`, `isInline`, `isDelivered`) VALUES (" . $message['from'] . ", " . $message['to'] . ", '" . $message['message'] . "', NOW(), '0', '1', '1')";
            $stmt = $this->db->query($sql);
            $stmt->execute();
        }
    }


    public function checkIfCanCreateChatToday($user_id, $contact_id, $messages_count = 5)
    {


        $sql = 'SELECT * FROM `communication` WHERE (owner_id = ' . $user_id . ' AND member_id = ' . $contact_id . ') OR ( owner_id = ' . $contact_id . ' AND member_id = ' . $user_id . ')';
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $res = $stmt->fetch();

//          var_dump($res);
        if ($res) return true;

        $date = new \DateTime('now');
        $date1 = $date->format("Y-m-d 00:00:00");
        $date2 = $date->format("Y-m-d 23:59:59");

        $sql = 'SELECT c.member_id, m.date FROM `communication` c 
                    JOIN `messenger` m 
                        ON ( 
                            m.fromUser = c.owner_id AND 
                            m.messageId = (SELECT MIN(messageId) FROM messenger m WHERE m.fromUser = c.owner_id and m.toUser = c.member_id)
                         )
                    WHERE c.owner_id = ' . $user_id . ' AND m.date BETWEEN "' . $date1 . '" AND "' . $date2 . '"';
//          var_dump($sql);
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $res2 = $stmt->fetchAll();
//          var_dump(count($res2));
        return count($res2) < $messages_count;


    }


//    public function callPushNotification() {
////        $users = new  Users();
//        //$userDevices['Android'] = array();
//
//        $usersDevices = $this->getUsersDevises($contact->getI());
//        $userDevices['Android'] = $users->getUserDevices($this->chatWith, 'Android');
//        $userDevices['Browser'] = $users->getUserDevices($this->chatWith, 'Browser');
//        $image = cloudinary_url(trim($users->mainImageCloudId($this->userId)),
//            array(
//                "width" => 55,
//                "height" => 55,
//                "crop" => "thumb",
//                //"gravity" => "face",
//                //"radius" => "max"
//            )
//        );
//
//        $userAttributes = new userAttributes();
//        $row = $userAttributes->get($this->config->users, array($this->userId), "userNick");
//        $message = str_replace("{USERNICK}", $row['userNick'], $this->config->notifications->callMessage);
//        $data = array(
//            'image' => $image,
//            "userId" => $this->userId,
//            "titleMess"=> 'נכנסת שיחת וידאו',
//            "buttons" => array('דחה/י','ענה/י'),
//            "userNick" => $row['userNick'],
//            //"msgcnt" => $this->getNewMessCount(true),
//            "onlyInBackgroundMode" => 1,
//            "video" => 1
//            //"urlRedirect"=>"https://google.co.il"
//        );
//
//        foreach ($userDevices['Android'] as $deviceId){
//            //echo $deviceId . '_' . $i;
//
//            $device = DeviceEntityHandler::getInstance('Android');
//            $device->setProps(array('userId' => $this->chatWith, 'id'=> trim($deviceId)));
//            $device->pushNotification($message, $data );
//
//        }
//        //var_dump($data);die;
//        if(isset($userDevices['Browser'])){
//            foreach ($userDevices['Browser'] as $deviceId){
//                $device = DeviceEntityHandler::getInstance('Browser');
//                $device->setProps(array('userId' => $this->chatWith, 'id'=> trim($deviceId)));
//                $device->callPushNotification($message, $data);
//            }
//        }
//    }


    public function sendMessageFromAdmin($usersId, $message, $start_from = 0)
    {
        $users_length = count($usersId);
        $sql = "INSERT INTO messenger (fromUser, message, isRead, isDelivered, msgFromDel, isInline, toUser) VALUES";
        $values = "(111, '$message', 0, 1, 1, 1, ";
        $x = 0;
        for ($i = $start_from; $i < $users_length; $i++) {
            $x++;
            $sql .= $values . $usersId[$i] . '),';
            if ($x === 500) {
                break;
            }
        }

        $sql = rtrim($sql, ',');

        $this->db->query($sql);
        if ($i < $users_length) {
            $this->sendMessageFromAdmin($usersId, $message, $i);
        }
    }

}



