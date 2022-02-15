<?php

namespace AppBundle\Services\Messenger;

class User extends Messenger
{

    protected $id;
    protected $image;
    protected $nickName;
    protected $gender;
    protected $isFrozen;
    protected $isOnline;
    protected $isPaying;
    protected $hasPoints;
    protected $email;

    public function __construct($id)
    {
        parent::__construct();
        $this->id = $id;
        $this->setProperties();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getNickName()
    {
        return $this->nickName;
    }

    public function isFrozen()
    {
        return $this->isFrozen;
    }

    public function isOnline()
    {
        return $this->isOnline;
    }

    public function isPaying()
    {
        $stmt = $this->db->prepare("SELECT * FROM settings WHERE id = 1");
        $stmt->execute();
        $row = $stmt->fetch();
        return true;
        return ($row['is_charge'] == '0') || $this->isPaying;
//        return  $this->isPaying;
    }

    public function isMessReadAvailable()
    {


        $stmt = $this->db->prepare("SELECT * FROM settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch();


        if (!in_array($this->gender, array(1, 4)) || ($settings['is_charge'] == '0')
//			&& strpos($this->email, 'interdate') === false
//			&& strpos($this->email, 'interdate') === false
        ) {
            return true;
        } else {
            return $this->isPaying;
        }
    }

    public function hasPoints()
    {
        return $this->hasPoints;
    }

    public function setProperties()
    {
        $sql = "
			SELECT 
				u.username,
				u.email,
				u.gender_id,
				u.is_frozen,
				u.points,
				p.id AS fid,
				p.ext,
				u.sign_up_date,
				1 as isOnline,
				isPaying(u.start_subscription, u.end_subscription, NOW()) as isPaying
			FROM 
				user u
			LEFT JOIN
				file p
			ON
				p.user_id = u.id AND p.is_main = 1 AND p.is_valid = 1
			WHERE 
				u.id=:id";


        $stmt = $this->db->prepare($sql);
        $stmt->bindParam("id", $this->id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $dateForFreeSubscription = date('Y-m-d H:i:s', strtotime('-48 hour'));
        $this->nickName = (!empty ($row['username'])) ? $row['username'] : false;
        $this->gender = $row['gender_id'];
        $this->email = $row['email'];
        $this->isFrozen = $row['is_frozen'] == 1;
        $this->isOnline = $row['isOnline'] == 1;
        $this->isPaying = $row['isPaying'] == 1 || $row['sign_up_date'] > $dateForFreeSubscription;
        $this->hasPoints = $row['points'] > 0;

        $this->image = '/images/media/' . $this->id . '/' . $row['fid'] . $row['ext'];

    }

    public function canReadMessagesInChat($userId, $contactId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($userId);
        $contact = $em->getRepository('AppBundle:User')->find($contactId);

        $settings = $this->getDoctrine()->getRepository('AppBundle:Settings')->find(1);

        if (!$settings->getIsCharge()
//            && strpos($contact->getEmail(), 'interdate') === false
//            && strpos($user->getEmail(), 'interdate') === false
        ) {
            return true;
        }

        if (in_array($user->getGender(), [2, 3])) {
            return true;
        }

        if (!in_array($user->getGender()->getId(), [1, 4]) && !in_array($contact->getGender()->getId(), [1, 4])) {
            return true;
        }

        if ($user->isPaying() || $contact->isPaying()) {
            return true;
        }

        return false;
    }

}

?>
