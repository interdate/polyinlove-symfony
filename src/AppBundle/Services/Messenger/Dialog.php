<?php 

namespace AppBundle\Services\Messenger;

class Dialog extends Chat{
	
	
	public function __construct($options){	
		parent::__construct($options);
	}
	
	public function setMessageAsDelivered($messageId){
		$sql = "UPDATE " . $this->config->messenger->table . " SET isDelivered = 1, modified = ? WHERE messageId = ?";
		$stmt = $this->db->prepare($sql);
		$modified = date("Y-m-d h:i:s");
		$stmt->bindParam(1, $modified, \PDO::PARAM_INT);
		$stmt->bindParam(2, $messageId, \PDO::PARAM_INT);
		$stmt->execute();
	}
	
		
	public function getHistory($page = 1, $per_page = 1000){

		$userId = $this->user->getId();
		$contactId = $this->contact->getId();
		$userImage = $this->user->getImage();
		$contactImage = $this->contact->getImage();
		$userNickName = $this->user->getNickName();
		$contactNickName = $this->contact->getNickName();
		$result = array();

		$sql = "SELECT
					fromUser,toUser,message,date,isRead,messageId,isDelivered, msgFromDel, msgToDel, isInline
				FROM
					" . $this->config->messenger->table . "
				WHERE
					(toUser = ? AND fromUser = ?  AND msgToDel = 0)
				OR
					(toUser = ? AND fromUser = ? AND msgFromDel = 0)
				ORDER BY 
					messageId 
				DESC LIMIT  " . (($page - 1)*$per_page) . ', ' . $per_page;
		
		$stmt = $this->db->prepare($sql);

		$stmt->bindParam(1, $userId, \PDO::PARAM_INT);
		$stmt->bindParam(2, $contactId, \PDO::PARAM_INT);
		$stmt->bindParam(3, $contactId, \PDO::PARAM_INT);
		$stmt->bindParam(4, $userId, \PDO::PARAM_INT);
		$stmt->execute();
		$messages = $stmt->fetchAll();
		$messages = array_reverse($messages);
		$allowedToReadMessage = ($this->user->isPaying() || $this->contact->isPaying()) ? true : false;
		
		foreach ($messages as $message){
			$messageDateObject = new \DateTime($message['date']);
			$timestamp = $messageDateObject->getTimestamp();
			$date = date("d/m/Y", $timestamp);
			$time = date("H:i", $timestamp);			
			$isRead = ($message['isRead'] == 0) ? false : true;			
			$image = ($userId == $message['fromUser']) ? $userImage : $contactImage;
			$username = ($userId == $message['fromUser']) ? $userNickName : $contactNickName;

			$text = ($message['fromUser'] != $this->user->getId() && !$allowedToReadMessage && $message['isRead'] == 0 && !$message['isInline'])
				? ( $this->user->hasPoints() ) 
					? $this->config->payment->text . ' <a href="%PAYMENT_LINK%">' . $this->config->payment->linkText . '</a> or <a onclick="Messenger.useFreePointToReadMessage(this)">' . $this->config->points->linkText . '</a>'
					: $this->config->payment->text . ' <a href="%PAYMENT_LINK%">' . $this->config->payment->linkText . '</a>'
				: nl2br(urldecode($message['message']))
			;
				
			$result[] = array(
				"id" => $message['messageId'],
				"from" => $message['fromUser'],
				"username" => $username,					
				"text" => $text,
				"dateTime" => $date . ' ' . $time,
				"userImage" => $image,
				"isRead" => $isRead,
				"isSaved" => true,
				"allowedToRead" => $allowedToReadMessage,
                "msgFromDel" => $message['msgFromDel'],
                "msgToDel" => $message['msgToDel']

			);
			
			if($message['fromUser'] == $contactId and $message['isDelivered'] == 0){		
				$this->setMessageAsDelivered($message['messageId']);   
			}
			
			if($allowedToReadMessage and $message['fromUser'] == $contactId and $message['isRead'] == 0){
				$this->setMessageAsRead($message['messageId']);
			}
		}
		
		return $result;
	}



	public function oldGetHistory($page = 1, $per_page = 500, $bool = false){

		$userId = $this->user->getId();
		$contactId = $this->contact->getId();
		$userImage = $this->user->getImage();
		$contactImage = $this->contact->getImage();
		$userNickName = $this->user->getNickName();
		$contactNickName = $this->contact->getNickName();
		$result = array();
		$isMessReadAvailable = $this->user->isMessReadAvailable();
		$sql = "SELECT
					fromUser,toUser,message,date,isRead,messageId,isDelivered, msgFromDel, msgToDel, isInline
				FROM
					" . $this->config->messenger->table . "
				WHERE
					(toUser = ? AND fromUser = ?  AND msgToDel = 0)
				OR
					(toUser = ? AND fromUser = ? AND msgFromDel = 0)
				ORDER BY 
					messageId 
				DESC LIMIT  " . (($page - 1)*$per_page) . ', ' . $per_page;

		$stmt = $this->db->prepare($sql);

		$stmt->bindParam(1, $userId, \PDO::PARAM_INT);
		$stmt->bindParam(2, $contactId, \PDO::PARAM_INT);
		$stmt->bindParam(3, $contactId, \PDO::PARAM_INT);
		$stmt->bindParam(4, $userId, \PDO::PARAM_INT);
		$stmt->execute();
		$messages = $stmt->fetchAll();
		$messages = array_reverse($messages);


		foreach ($messages as $message){
			$allowedToReadMessage = $isMessReadAvailable || $message['isInline'] == '1' || $message['fromUser'] == $userId || $message['isRead'];
			$messageDateObject = new \DateTime($message['date']);
			$timestamp = $messageDateObject->getTimestamp();
			$date = date("d/m/Y", $timestamp);
			$time = date("H:i", $timestamp);
			$isRead = ($message['isRead'] == 0) ? false : true;
			$image = ($userId == $message['fromUser']) ? $userImage : $contactImage;
			$username = ($userId == $message['fromUser']) ? $userNickName : $contactNickName;

			if ($username == 'elad') {
			}

			$text = (!$allowedToReadMessage)
				? (( $this->user->hasPoints() )
					? $this->config->payment->text . ' <a href="%PAYMENT_LINK%">' . $this->config->payment->linkText . '</a> or <a onclick="Messenger.useFreePointToReadMessage(this)">' . $this->config->points->linkText . '</a>'
					: $this->config->payment->text . ' <a href="%PAYMENT_LINK%">' . $this->config->payment->linkText . '</a>'
				)
				:
				($bool ? ' you must update your appto view messages <br> <a href="https://play.google.com/store/apps/details?id=com.interdate.polyamory">click here:</a>' : nl2br(urldecode($message['message'])))
			;

			$result[] = array(
				"id" => $message['messageId'],
				"from" => $message['fromUser'],
				"username" => $username,
				"text" => $text,
				"dateTime" => $date . ' ' . $time,
				"userImage" => $image,
				"isRead" => $isRead,
				"isSaved" => true,
				'isInline' => $message['isInline'],
				"delivered" => $message['isDelivered'],
				"allowedToRead" => $allowedToReadMessage,
				"msgFromDel" => $message['msgFromDel'],
				"msgToDel" => $message['msgToDel'],
				'paymentText' =>  $this->config->payment->text,
				'payLinkText' => $this->config->payment->linkText,
				'pointsText' => $this->config->points->linkText,
				'hasPoint' => (boolean)$this->user->hasPoints(),
				'$isMessReadAvailable' => $isMessReadAvailable,
				'$messageisInline' => $message['isInline'],
				'$messagefromUser' => $message['fromUser'],
				'$userId' => $userId,
				'$message[isRead]' => $message['isRead'],
			);

			if($message['fromUser'] == $contactId and $message['isDelivered'] == 0){
				$this->setMessageAsDelivered($message['messageId']);
			}

			if($isMessReadAvailable and $message['fromUser'] == $contactId and $message['isRead'] == 0){
				$this->setMessageAsRead($message['messageId']);
			}
		}

		return $result;
	}




	public function getNewMessagesNumber($options = false){
		
		$userId = $this->user->getId();
		$contactId = $this->contact->getId();
		
		$sql = "
			SELECT
				messageId FROM " . $this->config->messenger->table . "
			WHERE
				toUser = ? AND fromUser = ? AND isRead = 0";
	
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(1, $userId, \PDO::PARAM_INT);
		$stmt->bindParam(2, $contactId, \PDO::PARAM_INT);
		$stmt->execute();
		return count($stmt->fetchAll());
	}
	
}
?>
