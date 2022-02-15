<?php 

namespace AppBundle\Services\Messenger;

use Symfony\Component\HttpFoundation\JsonResponse;

class Messenger{ 
	
	public $db;
	public $config;
	public $isNewMessage = false;
	
	public function __construct(){
		$this->config = Config::getInstance();
		$this->config = $this->arrayToObject($this->config);
		$this->db = Database::getInstance($this->config->database);
		date_default_timezone_set('Asia/Jerusalem');
	}
	
	public function response($array) {
		return new JsonResponse($array);
	}
	
	public function arrayToObject($array){
		$json = json_encode($array);
		return json_decode($json);
	}
	
	public function isNewMessage(){
		return $this->isNewMessage;		
	}
	
	public function openChat($options){
		$userAttributes = new UserAttributes();
		
		$chatSession = $userAttributes->get($this->config->messengerSession, array($options['userId'], $options['contactId']));
		if(count($chatSession) == 0){
			$userAttributes->post($this->config->messengerSession, array($options['userId'], $options['contactId']));
			return true;
		}
		 
		return false;
	}
	
	public function closeChat($options){		
		$sql = "DELETE FROM " . $this->config->messengerSession->table . " WHERE userId = ? AND contactId = ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);
		$stmt->bindParam(2, $options['contactId'], \PDO::PARAM_INT);
		$success = ($stmt->execute()) ? true : false;
		return $this->response(array('success' => $success));		
	}
	
	public function getActiveChats($options){   
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
		
		foreach ($result as $row){
			
			$chat = new Chat(array(
				'userId' => $row['userId'],
				'contactId' => $row['contactId']
			));
			
			if(!$chat->isForbidden()){
				$activeChats[] = array(
					'id' => $row['contactId'],
					'name' => $row['username']
				);
			}
		}
		
		return $activeChats;
	}
		
	public function checkActiveChatsNewMessages($options){
		$result = array();
		$dateTime = array();
		$userAttributes = new UserAttributes();		
		
		$allChats = $userAttributes->get($this->config->messengerSession, array($options['userId']));		
		
		if(count($allChats)){
		
			$startTime = time();
			while(time() - $startTime < 10) {
				
				foreach ($allChats as $chatOptions){
					$chat = new Chat($chatOptions);
					$newMessages = $chat->getNewMessages();
					
					if(count($newMessages) > 0){
						$allowedToReadMessage = ($chat->user()->isPaying() || $chat->contact()->isPaying()) ? true : false;
						foreach ($newMessages as $message){
							$this->isNewMessage = true;						
							$messageDateObject = new \DateTime($message['date']);
							$timestamp = $messageDateObject->getTimestamp();
							$date = date("m/d/Y", $timestamp);
							$time = date("H:i", $timestamp);
							
							$text = ($message['fromUser'] != $chat->user()->getId() && !$allowedToReadMessage)
								? ''
								: nl2br(urldecode($message['message']))
							;
							
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
				
				if($this->isNewMessage()){
					$timestamp = time();
					$time = date("i:s", $timestamp);
					$dateTime[] = $time;
					
					foreach($result as $message){
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
		//return $this->response(array("newMessages" => $result, "MinSec" => $dateTime));
		return array("newMessages" => $result, "MinSec" => $dateTime);
	}

	public function checkMessagesIfRead($messages){
		
		$readMessages = array();	
		$startTime = time();
		while(time() - $startTime < 10) {			
			if(mb_strlen(trim($messages), "utf-8") > 0){
				$sql = "SELECT messageId FROM " . $this->config->messenger->table . " WHERE messageId IN (" . trim($messages) . ") AND isRead = 1";
				$stmt = $this->db->prepare($sql);
				$stmt->execute();
				foreach ($stmt->fetchAll() as $row){
					$readMessages[] = $row['messageId'];
				}
				
				if(count($readMessages)){
					return $readMessages;
				}
			}
			
			usleep(500);
		}
		
		return $readMessages;
	}
	
	
	public function checkNewMessages($options){
		
		$users = array();
        $messagesIds = array();

		$sql = "
			SELECT 
				m.messageId, m.fromUser, m.message, m.isRead, m.isDelivered, u.username, u.gender_id, p.name as photoName FROM " . $this->config->messenger->table . " m
			JOIN 					 
				" . $this->config->users->table . " u 
			ON
				( m.fromUser = u.id)

			LEFT JOIN
			    photo p
			ON
			    ( p.user_id = u.id AND p.is_main = 1 AND p.is_valid = 1)
			WHERE 
				m.toUser = ? AND m.isRead = 0 AND m.isNotified = 0 AND m.date > ?";
		
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $options['userId'], \PDO::PARAM_INT);		
		$stmt->bindParam(2, $options['lastLoginAt']);
		$stmt->execute();
		$result = $stmt->fetchAll();
		
		foreach ($result as $row){
            $message = strip_tags(urldecode($row['message']));
            if(strlen($row['message']) > 75){
                $message = substr($message, 0, 74) . '...';
            }

			$photo = (!empty ($row['photoName']))
                ? cloudinary_url($row['photoName'], array(
                    "width" => 150, "height" => 150, "crop" => "thumb", "gravity" => "face"//, "radius" => 20
                ))
                : '/images/no_photo_thumb_' . $row['gender_id'] . '.jpg'
            ;
			
			$user = array(
				"id" => $row['fromUser'],
				"name" => $row['username'],
				"isDelivered" => $row['isDelivered'],
				"isRead" => $row['isRead'],
                "messageId" => $row['messageId'],
				"message" => $message,
                "photo" => $photo,
			);
			
			if(!in_array($user, $users)){
				$users[] = $user;
			}

            $messagesIds[] = $row['messageId'];
		}

        if(count($messagesIds)){
            $sql = "UPDATE messenger SET isNotified = 1 WHERE messageId IN (" . implode(',', $messagesIds) . ")";
            $this->db->query($sql);
        }

		return $this->response(array("fromUsers" => $users));
	}

	public function checkNewMessagesMobile($options){



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

	public function checkDialogNewMessages($options){
		$result = array();
		$dateTime = array();
		$startTime = time();
		
		while(time() - $startTime < 10) {
			
			$dialog = new Dialog($options);
			$newMessages = $dialog->getNewMessages();
		
			if(count($newMessages) > 0){
				$allowedToReadMessage = ($dialog->user()->isPaying() || $dialog->contact()->isPaying()) ? true : false;
				
				foreach ($newMessages as $message){
					$this->isNewMessage = true;
					$messageDateObject = new \DateTime($message['date']);
					$timestamp = $messageDateObject->getTimestamp();
					$date = date("m/d/Y", $timestamp);
					$time = date("H:i", $timestamp);
					$text = ($message['fromUser'] != $dialog->user()->getId() && !$allowedToReadMessage)
						? ''
						: nl2br(urldecode($message['message']))
					;
		
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
				
			if($this->isNewMessage()){
				$timestamp = time();
				$time = date("i:s", $timestamp);
				$dateTime[] = $time;
		
				foreach($result as $message){
					$dialog->setMessageAsDelivered($message['id']);
				}

				return array(
					"newMessages" => $result, 
					"currentUserHasPoints" => $dialog->user()->hasPoints(), 
					"MinSec" => $dateTime,
				);
			}
		
			usleep(500);
		}
		 
		$timestamp = time();
		$time = date("i:s", $timestamp);
		$dateTime[] = $time;
		return array("newMessages" => $result, "MinSec" => $dateTime);
	}
	
	public function getNewMessagesNumber($options = false){
		
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
	
	
	public function getUsersMessages($page, $perPage, $userId)
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
              fromUser.username as fromusername,
              toUser.username as tousername
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

        $stmt = $this->db->query($sql);
        $messages = $stmt->fetchAll();

		foreach ($messages as $key => $item){
            $messages[$key]['message'] = nl2br(urldecode($item['message']));
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
            " . $userCond
        ;

        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
		return $result['number'];

	}
	
	public function useFreePointToReadMessage($messageId, $userId){
		$user = new User($userId);
		if($user->hasPoints()){			
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

    public function setMessageAsNotified($messageId){
        $sql = "UPDATE " . $this->config->messenger->table . " SET isNotified = 1 WHERE messageId = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $messageId, \PDO::PARAM_INT);
        if($stmt->execute())
            return true;

        return false;
    }

	public function removeMessages($messagesIdsString){		
		$sql = "DELETE FROM " . $this->config->messenger->table . " WHERE messageId IN (" . $messagesIdsString . ")";
        echo $sql;
		$this->db->query($sql);
	}

}
