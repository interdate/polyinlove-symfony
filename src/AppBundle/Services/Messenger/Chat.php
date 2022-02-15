<?php 

namespace AppBundle\Services\Messenger;

class Chat extends Messenger{
	
	public $user;	
	public $contact;
	private $message;
	private $isForbidden;
    private $isInline;

	public function __construct($options){
		parent::__construct();


        if(isset($options['isInline']) && !empty($options['isInline'])) {

			if ($options['messageId'] == 9999) {
				$options['message'] = urlencode(strip_tags('I allow you to see my private photos'));

			}
		}

		$this->user = (!empty($options['userId'])) ? new User($options['userId']) : null;
		$this->contact = (!empty($options['contactId'])) ? new User($options['contactId']) : null;
		$this->message = isset($options['message'])  ? new Message($options) : false;
        $this->isInline =  $options['isInline'] ?? false;
		$this->isForbidden = $this->checkIfForbidden();
//		var_dump($this->isForbidden);
	}
	
	protected function checkIfForbidden(){
		$sql = "
			SELECT
				owner_id
			FROM
				black_list
			WHERE
				(owner_id = " . $this->contact->getId() . " AND member_id = " . $this->user->getId() . ")
			OR
				(owner_id = " . $this->user->getId() . " AND member_id = " . $this->contact->getId() . ")
		";
		$stmt = $this->db->query($sql);
		$row = $stmt->fetch();
//		var_dump($row);
		if (!isset($row['owner_id']) || empty($row['owner_id'])) {
			return false;
		}

		return $row['owner_id'] == $this->user->getId() ? 1 : 2;
	}
	
	public function getNewMessages(){
		$sql = "SELECT 
					messageId,message,date,fromUser 
				FROM 
					" . $this->config->messenger->table . " 
				WHERE 
					toUser = ? AND fromUser = ? AND isRead = 0";
				
		$userId = $this->user->getId();
		$contactId = $this->contact->getId();
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId, \PDO::PARAM_INT);
		$stmt->bindParam(2, $contactId, \PDO::PARAM_INT);
		$stmt->execute();

		$result = $stmt->fetchAll();
		//var_dump($result);die;
		return $result;
	}
	
	public function setMessageAsDelivered($messageId){
		$userAttributes = new UserAttributes();
		$userSession = $userAttributes->get($this->config->messengerSession, array($this->user->getId(), $this->contact->getId()));
		if(count($userSession) == 0){
			return false;
		}
				
		$sql = "UPDATE " . $this->config->messenger->table . " SET isDelivered = 1, modified = ? WHERE messageId = ?";
		$stmt = $this->db->prepare($sql);		
		$modified = date("Y-m-d H:i:s");		
		$stmt->bindParam(1, $modified, \PDO::PARAM_INT);
		$stmt->bindParam(2, $messageId, \PDO::PARAM_INT);
		$stmt->execute();		
	}
	
	public function setMessageAsRead($messagesId) {

		$messagesId = trim($messagesId, ', ');

		$sql = "UPDATE " . $this->config->messenger->table . " SET isRead = 1 WHERE messageId IN (" . trim($messagesId, ',') . ")";
		$stmt = $this->db->prepare($sql);

		$userId = $this->user->getId();
		$contactId = $this->contact->getId();

		if ($stmt->execute()) return true;


		return false;
	}
	
	public function contact(){
		return $this->contact;		
	}
	
	public function user(){
		return $this->user;		
	}
	
	public function sendMessage($mess = false){

        //var_dump(123);
//		var_dump($this->message);die;
		if($this->message){

          // var_dump($this->message->isInline);die;
			$userAttributes = new UserAttributes();

			$userAttributes->post($this->config->messenger,
				array(					
					$this->message->from, 
					$this->message->to,					 
					$this->message->text,
					$this->message->date,
					$this->message->isRead,
					$this->message->isDelivered,
					$this->isInline ? $this->isInline : 0,
				)
			);


			$lastMessageId = $userAttributes->getLastId();

           // var_dump($lastMessageId);

			$messageDateObject = new \DateTime($this->message->date);
			$timestamp = $messageDateObject->getTimestamp();
			$date = date("d/m/Y", $timestamp);
			$time = date("H:i", $timestamp);
			
			
			$sql = "				
				SELECT owner_id FROM communication WHERE
					(owner_id = ? AND member_id = ?)
						OR
					(owner_id = ? AND member_id = ?)
			";
			
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $this->message->from, \PDO::PARAM_INT);
			$stmt->bindParam(2, $this->message->to, \PDO::PARAM_INT);
			$stmt->bindParam(3, $this->message->to, \PDO::PARAM_INT);
			$stmt->bindParam(4, $this->message->from, \PDO::PARAM_INT);
			$stmt->execute();

			$contacted = $stmt->fetchAll();
			
			if(!count($contacted)){				
				$userAttributes->post($this->config->contacted,
					array(
						$this->message->from,
						$this->message->to,
					)
				);			
			}

			$sql = "SELECT id FROM " . $this->config->lastMessages->table . " WHERE 
				( user1 = ? AND user2 = ? ) 
					OR 
				( user1 = ? AND user2 = ?)
			";
							
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $this->message->from, \PDO::PARAM_INT);
			$stmt->bindParam(2, $this->message->to, \PDO::PARAM_INT);
			$stmt->bindParam(3, $this->message->to, \PDO::PARAM_INT);
			$stmt->bindParam(4, $this->message->from, \PDO::PARAM_INT);			
			$stmt->execute();
            //var_dump(123);
			$lastMessage = $stmt->fetch();
          // var_dump($lastMessage);
			if(empty($lastMessage['id'])){
//                var_dump($this->message->from,
//                    $this->message->to,
//                    $this->message->text,
//                    $this->message->date,
//                    $lastMessageId
//                );

				$userAttributes->post($this->config->lastMessages,
					array(
						$this->message->from,
						$this->message->to,
						$lastMessageId,
						$this->message->text,
						$this->message->date,
                        $lastMessageId,
                        0,
                        0,
					)
				);
//                var_dump(345);
			}
			else{
				$sql = "
					UPDATE " . $this->config->lastMessages->table . " SET 
						messageId = ?, 
						messageId2 = ?, 
						message = ?,
						date = ?,
						user1_del = 0,
						user2_del = 0
					WHERE id = ?
				";
				
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(1, $lastMessageId, \PDO::PARAM_INT);				
				$stmt->bindParam(2, $lastMessageId, \PDO::PARAM_INT);
				$stmt->bindParam(3, $this->message->text);
				$stmt->bindParam(4, $this->message->date);
				$stmt->bindParam(5, $lastMessage['id'], \PDO::PARAM_INT);
				$stmt->execute();				
			}
            // var_dump(123);
			$text = $mess ? $mess : $this->message->text;

			return array(
				"id" => $lastMessageId,	
				"from" => $this->message->from,
				"to" => $this->message->to,
				"text" => urldecode($text),
				"dateTime" => $date . " " . $time,
				"senderImage" => $this->user->getImage(),
				"contactIsOnline" => $this->contact->isOnline(),
				"isSaved" => true,
			);
		
		}
		else return false; 
		
		
	}
	
	public function getHistory($messagesNumber = 30){

		$userId = $this->user->getId();
		$contactId = $this->contact->getId();
		$userImage = $this->user->getImage();
		$contactImage = $this->contact->getImage();
		$result = array();
		
		$sql = "SELECT TOP " . $messagesNumber . " 
					fromUser,toUser,message,date,isRead,messageId,isDelivered
				FROM
					" . $this->config->messenger->table . "
				WHERE
					(toUser = ? AND fromUser = ?)
				OR
					(toUser = ? AND fromUser = ?)
				ORDER BY 
					messageId 
				DESC";


		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId, \PDO::PARAM_INT);
		$stmt->bindParam(2, $contactId, \PDO::PARAM_INT);
		$stmt->bindParam(3, $contactId, \PDO::PARAM_INT);
		$stmt->bindParam(4, $userId, \PDO::PARAM_INT);
		$stmt->execute();
		$messages = $stmt->fetchAll();	
		$messages = array_reverse($messages);
		$allowedToReadMessages = ($this->user->isPaying() || $this->contact->isPaying()) ? true : false;
		
		foreach ($messages as $message){
			$allowedToReadMessage = $allowedToReadMessages || $message['isRead'] || $message['fromUser'] == $userId;
			$messageDateObject = new \DateTime($message['date']);
			$timestamp = $messageDateObject->getTimestamp();
			$date = date("d/m/Y", $timestamp);
			$time = date("H:i", $timestamp);			
			$isRead = ($message['isRead'] == 0) ? false : true;			
			$image = ($userId == $message['fromUser']) ? $userImage : $contactImage;
			
			$text = ($message['fromUser'] != $this->user->getId() && !$allowedToReadMessage && $message['isRead'] == 0)
				? ''
				: nl2br(urldecode($message['message']))
			; 

			$result[] = array(
				"id" => $message['messageId'],
				"from" => $message['fromUser'],					
				"text" => $text,
				"dateTime" => $date . ' ' . $time,
				"userImage" => $image,
				"isSaved" => true,
				"isRead" => $isRead,
				"allowedToRead" => true
			);
			
			if($message['fromUser'] == $contactId and $message['isDelivered'] == 0){		
				$this->setMessageAsDelivered($message['messageId']);   
			}			
			
		}
		
		return $result;
	}

	public function isNotSentToday(){
		$date = new \DateTime('now');
		$date1 = $date->format("Y-m-d 00:00:00");
		$date2 = $date->format("Y-m-d 23:59:59");
		$userId = $this->user->getId();
		$contactId = $this->contact->getId();
		$sql = "SELECT messageId FROM " . $this->config->messenger->table . " WHERE fromUser = ? AND toUser = ? AND date BETWEEN ? AND ?";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId);
		$stmt->bindParam(2, $contactId);
		$stmt->bindParam(3, $date1);
		$stmt->bindParam(4, $date2);
		$stmt->execute();
//        return $stmt->fetchAll() == 1 ;
		return ( count($stmt->fetchAll()) == 1 );
	}

	/**
	 *
	 * Check if the message before the last was sent from contact (because the last message is the message that we want to push about him)
	 *
	 * return boolean
	 */
	public function isLastFromContact() {
		$date = new \DateTime('now');
		$date1 = $date->format("Y-m-d 00:00:00");
		$date2 = $date->format("Y-m-d 23:59:59");
		$userId = $this->user->getId();
		$contactId = $this->contact->getId();

		$sql = "SELECT * FROM " . $this->config->messenger->table . " WHERE fromUser =  ?  AND toUser = ?  OR fromUser =  ?  AND toUser = ? 
		ORDER BY messageId DESC limit 2";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId);
		$stmt->bindParam(2, $contactId);
		$stmt->bindParam(3, $contactId);
		$stmt->bindParam(4, $userId);
		$stmt->execute();
		$res = $stmt->fetchAll();
		if (count($res) == 1) {
			return true;
		}
//		var_dump($res[1]['fromUser'] == $contactId);die;
		return ( !$res || $res[1]['fromUser'] == $contactId);
	}

//	public function isLimit($limit){
//
//		if($this->user->isPaying()) {
//			$limit = $limit * 2;
//		}
//
//        $date = new \DateTime('now');
//		$date1 = $date->format("Y-m-d 00:00:00");
//		$date2 = $date->format("Y-m-d 23:59:59");
//		$userId = $this->user->getId();
//        $contactId = $this->contact->getId();
//
//        $sql = "SELECT toUser FROM " . $this->config->messenger->table . " WHERE fromUser = ? AND toUser = ? AND date BETWEEN ? AND ? GROUP BY toUser";
//		$stmt = $this->db->prepare($sql);
//		$stmt->bindParam(1, $userId);
//		$stmt->bindParam(2, $contactId);
//		$stmt->bindParam(3, $date1);
//		$stmt->bindParam(4, $date2);
//		$stmt->execute();
//
//        if(count($stmt->fetchAll()) > 0){
//            return false;
//        }
//
//        $sql = "SELECT toUser FROM " . $this->config->messenger->table . " WHERE fromUser = ? AND date BETWEEN ? AND ? GROUP BY toUser";
//		$stmt = $this->db->prepare($sql);
//		$stmt->bindParam(1, $userId);
//		$stmt->bindParam(2, $date1);
//		$stmt->bindParam(3, $date2);
//		$stmt->execute();
//
//		return ( count($stmt->fetchAll()) >= $limit );
//	}


	public function isLimit($limit){

//		if($this->user->isPaying()) {
//			$limit = $limit * 2;
//		}

		$date = new \DateTime('now');
		$date1 = $date->format("Y-m-d 00:00:00");
		$date2 = $date->format("Y-m-d 23:59:59");
		$userId = $this->user->getId();
		$contactId = $this->contact->getId();

		$sql = 'SELECT * FROM `communication` WHERE  (owner_id = ? AND member_id = ?) OR (owner_id = ? AND member_id = ?)';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId);
		$stmt->bindParam(2, $contactId);
		$stmt->bindParam(3, $contactId);
		$stmt->bindParam(4, $userId);
		$stmt->execute();

		if(count($stmt->fetchAll()) > 0){
			return false;
		}

		$sql = "SELECT toUser FROM " . $this->config->messenger->table . " WHERE fromUser = ? AND date BETWEEN ? AND ? GROUP BY toUser";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId);
		$stmt->bindParam(2, $date1);
		$stmt->bindParam(3, $date2);
		$stmt->execute();
//		var_dump($limit);die;
		return ( count($stmt->fetchAll()) >= $limit );
	}

	public function isForbidden(){
		return $this->isForbidden;		
	}

    public function getUsersBlockStatus()
    {
        $res = 0;
        $sql = "
            SELECT
                id
            FROM
               user
            WHERE
               (id = " . $this->contact->getId() . " OR id = " . $this->user->getId() . ")
            AND
               is_active = 0
        ";

        $stmt = $this->db->query($sql);
        $row = $stmt->fetchAll();

        if (count($row) > 0) {

            if ($row[0]['id'] == $this->user->getId() or (count($row) == 2 and $row[1]['id'] == $this->user->getId())) {
                $res = 2;
            } elseif ($row[0]['id'] == $this->contact->getId() or (count($row) == 2 and $row[1]['id'] == $this->contact->getId())) {
                $res = 1;
            }
        }
        //var_dump($res);die;
        return $res;
    }


}

?>
