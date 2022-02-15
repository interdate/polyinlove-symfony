<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

//use AppBundle\Entity\UserRepository as UserRepo;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{

    private $userRepo;
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=25, nullable=true)
     *
     * @Assert\Length(
     *      min = 10,
     *      max = 12,
     *      minMessage = "Your phone must be at least {{ limit }} characters long",
     *      maxMessage = "Your phone cannot be longer than {{ limit }} characters"
     * )
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="users")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     **/
    private $role;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=20)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="Gender", inversedBy="users")
     * @ORM\JoinColumn(name="gender_id", referencedColumnName="id")
     */
    private $gender;

    /**
     * @ORM\ManyToMany(targetEntity="Gender", inversedBy="users")
     * @ORM\JoinTable(name="users_contact_gender")
     */
    private $contactGender;

    /**
     * @ORM\ManyToMany(targetEntity="LookingFor", inversedBy="users")
     * @ORM\JoinColumn(name="user_looking_for")
     */
    private $lookingFor;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="datetime")
     */
    private $birthday;


    /**
     * @var boolean
     *
     * @ORM\Column(name="is_sent_email", type="boolean")
     */
    protected $isSentEmail = true;




    /**
     * @ORM\ManyToOne(targetEntity="Children", inversedBy="users")
     * @ORM\JoinColumn(name="children_id", referencedColumnName="id")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Religion", inversedBy="users")
     * @ORM\JoinColumn(name="religion_id", referencedColumnName="id")
     */
    private $religion;

    /**
     * @ORM\ManyToOne(targetEntity="Nutrition", inversedBy="users")
     * @ORM\JoinColumn(name="nutrition_id", referencedColumnName="id")
     */
    private $nutrition;

    /**
     * @ORM\ManyToOne(targetEntity="RelationshipStatus", inversedBy="users")
     * @ORM\JoinColumn(name="relationship_status_id", referencedColumnName="id")
     */
    private $relationshipStatus;

    /**
     * @ORM\ManyToOne(targetEntity="RelationshipType", inversedBy="users")
     * @ORM\JoinColumn(name="relationship_type_id", referencedColumnName="id")
     */
    private $relationshipType;

    /**
     * @var string
     *
     * @ORM\Column(name="relationshipTypeDetails", type="string", length=255, nullable=true)
     */
    private $relationshipTypeDetails;


    /**
     * @ORM\ManyToOne(targetEntity="SexOrientation", inversedBy="users")
     * @ORM\JoinColumn(name="sex_orientation_id", referencedColumnName="id")
     */
    private $sexOrientation;

    /**
     * @var string
     *
     * @ORM\Column(name="sexOrientationDetails", type="string", length=255, nullable=true)
     */
    private $sexOrientationDetails;


    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="user")
     **/
    private $payments;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text")
     */
    private $about;

    /**
     * @var string
     *
     * @ORM\Column(name="looking", type="text")
     */
    private $looking;

    /**
     * @var string
     *
     * @ORM\Column(name="LookingForDetails", type="string", length=255, nullable=true)
     */
    private $lookingForDetails;

    /**
     * @var integer
     *
     * @ORM\Column(name="age_from", type="integer")
     */
    private $ageFrom = 18;

    /**
     * @var integer
     *
     * @ORM\Column(name="age_to", type="integer")
     */
    private $ageTo = 99;


    /**
     * @ORM\ManyToOne(targetEntity="Smoking", inversedBy="users")
     * @ORM\JoinColumn(name="smoking_id", referencedColumnName="id")
     */
    private $smoking;

    /**
     * @var float
     *
     * @ORM\Column(name="height", type="float")
     */
    private $height;

    /**
     * @ORM\ManyToOne(targetEntity="Body", inversedBy="users")
     * @ORM\JoinColumn(name="body_id", referencedColumnName="id")
     */
    private $body;


    /**
     * @ORM\ManyToOne(targetEntity="Origin", inversedBy="users")
     * @ORM\JoinColumn(name="origin_id", referencedColumnName="id")
     */
    private $origin;


    /**
     * @ORM\ManyToOne(targetEntity="Zodiac", inversedBy="users")
     * @ORM\JoinColumn(name="zodiac_id", referencedColumnName="id")
     */
    private $zodiac;

    /**
     * @ORM\ManyToOne(targetEntity="LoginFrom", inversedBy="users")
     * @ORM\JoinColumn(name="login_from_id", referencedColumnName="id")
     */
    private $loginFrom;

    /**
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $photos;

    /**
     * @ORM\OneToMany(targetEntity="ShowPhoto", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $showPhoto;

    /**
     * @ORM\OneToMany(targetEntity="ShowPhoto", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $showPhoto2;

    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $notesAboutMe;

    /**
     * @ORM\OneToMany(targetEntity="View", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $viewed;

    /**
     * @ORM\OneToMany(targetEntity="View", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $viewedMe;

    /**
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $contacted;

    /**
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $contactedMe;

    /**
     * @ORM\OneToMany(targetEntity="Favorite", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $favorited;

    /**
     * @ORM\OneToMany(targetEntity="Favorite", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $favoritedMe;

    /**
     * @ORM\OneToMany(targetEntity="BlackList", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $blackListed;

    /**
     * @ORM\OneToMany(targetEntity="BlackList", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $blackListedMe;

    /**
     * @ORM\OneToMany(targetEntity="Communication", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     */
    private $connected;

    /**
     * @ORM\OneToMany(targetEntity="Communication", mappedBy="member", cascade={"remove"}, orphanRemoval=true)
     */
    private $connectedMe;

    /**
     * @var integer
     *
     * @ORM\Column(name="views", type="integer")
     */
    private $views = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="freeze_reason", type="string", length=255, nullable=true)
     */
    private $freezeReason;

    /**
     * @var string
     *
     * @ORM\Column(name="ban_reason", type="string", length=255, nullable=true)
     */
    private $banReason;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_comments", type="string", length=255, nullable=true)
     */
    private $adminComments;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=1000, nullable=true)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=15, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=50, nullable=true)
     */
    private $zipCode;


    /**
     * @var string
     */
    private $salt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_activated", type="boolean")
     */
    private $isActivated = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_non_locked", type="boolean")
     */
    private $isNonLocked = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isUpdatedZodiac", type="boolean")
     */
    private $isUpdatedZodiac = false;

    //public $age;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_frozen", type="boolean")
     */
    private $isFrozen = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_flagged", type="boolean")
     */
    private $isFlagged = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_on_homepage", type="boolean")
     */
    private $isOnHomepage = false;

    /**
     * Date/Time of the last activity
     *
     * @var \Datetime
     * @ORM\Column(name="last_activity_at", type="datetime")
     */
    protected $lastActivityAt;

    /**
     * Date/Time of the last real activity
     *
     * @var \Datetime
     * @ORM\Column(name="last_real_activity_at", type="datetime", nullable=true)
     */
    protected $lastRealActivityAt;

    /**
     * Date/Time of the last login
     *
     * @var \Datetime
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     */
    protected $lastloginAt;

    /**
     * Date/Time of the signing up
     *
     * @var \Datetime
     * @ORM\Column(name="sign_up_date", type="datetime")
     */
    protected $signUpDate;

    /**
     * @var \Datetime
     * @ORM\Column(name="start_subscription", type="datetime", nullable=true)
     */
    protected $startSubscription;

    /**
     * @var \Datetime
     * @ORM\Column(name="end_subscription", type="datetime", nullable=true)
     */
    protected $endSubscription;

    /**
     * @var \Datetime
     * @ORM\Column(name="code_date", type="datetime", nullable=true)
     */
    protected $codeDate;

    /**
     * @var string
     */
    private $oldPassword;

    /**
     * @var integer
     *
     * @ORM\Column(name="verify_count", type="integer")
     */
    private $verifyCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="code_count", type="integer")
     */
    private $codeCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="UserNotifications", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"isRead" = "ASC", "date" = "DESC"})
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity="UserMessengerNotifications", mappedBy="user")
     */
    private $userMessengerNotification;



    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", nullable=true)
     */
    private $longitude;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", nullable=true)
     */
    private $latitude;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_sent_push", type="boolean")
     */
    protected $isSentPush = true;

    /**
     * @ORM\OneToMany(targetEntity="LikeMe", mappedBy="userFrom", cascade={"remove"}, orphanRemoval=true)
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity="LikeMe", mappedBy="userTo", cascade={"remove"}, orphanRemoval=true)
     */
    private $likesMe;
    /**
     * @ORM\OneToMany(targetEntity="ArenaDislike", mappedBy="userFrom", cascade={"remove"}, orphanRemoval=true)
     */
    private $dislikes;

    /**
     * @ORM\OneToMany(targetEntity="ArenaDislike", mappedBy="userTo", cascade={"remove"}, orphanRemoval=true)
     */
    private $dislikesMe;

    /**
     * @ORM\OneToMany(targetEntity="Verify", mappedBy="userTo", cascade={"remove"}, orphanRemoval=true)
     */
    private $verifyMe;

    /**
     * @ORM\OneToMany(targetEntity="Verify", mappedBy="userTo", cascade={"remove"}, orphanRemoval=true)
     */
    private $verify;

    /**
     * @var string
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    private $facebook;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LocCountries", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LocRegions", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LocCities", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $city;


    private $distance;

    private $agree;


    /**
     * User constructor.
     */
    public function __construct() {
        //$this->codeDate = new \DateTime();
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->likes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->likesMe = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dislikes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dislikesMe = new \Doctrine\Common\Collections\ArrayCollection();
        $this->verifyMe = new \Doctrine\Common\Collections\ArrayCollection();
        $this->showPhoto = new  \Doctrine\Common\Collections\ArrayCollection();
        $this->contactGender = new  \Doctrine\Common\Collections\ArrayCollection();
        $this->lookingFor = new  \Doctrine\Common\Collections\ArrayCollection();
        $this->salt = null;
//        $this->userRepo = new UserRepository(EntityManagerInter, Mapping\ClassMetadata);
    }

    public function hasValidPhotos()
    {
        foreach($this->photos as $photo){
            if($photo->getIsValid()){
                return true;
            }
        }

        return false;
    }

    public function getDistance(){
        return $this->distance;
    }

    public function setDistance($distance){
        $this->distance = $distance;
        return $this;
    }



    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
//        dump($this->notifications);die;
        foreach ($this->notifications as $notification){
            if(($notification->getLikeMe()->getIsBingo() and $notification->getNotification()->getId() == 1)){
                $this->removeNotification($notification);
            }
        }
        return $this->notifications;
    }

    /**
     * Get messengerNotifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessengerNotifications()
    {
        return $this->userMessengerNotification;
    }

    /**
     * Set isSentEmail
     *
     * @param boolean $isSentEmail
     *
     * @return User
     */
    public function setIsSentEmail($isSentEmail)
    {
        $this->isSentEmail = $isSentEmail;

        return $this;
    }

    /**
     * Get isSentEmail
     *
     * @return boolean
     */
    public function getIsSentEmail()
    {
        return $this->isSentEmail;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return User
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return User
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getRoles()
    {
        return array($this->role->getRole());
    }

    public function getRoleSystemName()
    {
        return $this->role->getRole();
    }

    public function isAdmin()
    {
        return $this->role->getRole() == 'ROLE_ADMIN';
    }

    /**
     * Set isSentPush
     *
     * @param boolean $isSentPush
     *
     * @return User
     */
    public function setIsSentPush($isSentPush)
    {
        $this->isSentPush = $isSentPush;

        return $this;
    }

    /**
     * Get isSentPush
     *
     * @return boolean
     */
    public function getIsSentPush()
    {
        return $this->isSentPush;
    }

    public function getIsVerify() {
        return $this->verifyCount >= 3;
    }

//    public function setIsVerify($verify){
//        $this->isVerify = $verify;
//        return $this;
//    }

    /**
 * Set verifyCount
 *
 * @param integer $count
 *
 * @return Verify
 */
    public function setVerifyCount($count)
    {
        $this->verifyCount = $count;
        if($this->verifyCount >= 3){
           // $this->setIsVerify(true);
        }
        return $this;
    }

    /**
     * Get verifyCount
     *
     * @return integer
     */
    public function getVerifyCount()
    {
        return $this->verifyCount;
    }



    /**
     * Set codeCount
     *
     * @param integer $count
     *
     * @return User
     */
    public function setCodeCount($count)
    {
        $this->codeCount = $count;

        return $this;
    }

    /**
     * Get codeCount
     *
     * @return integer
     */
    public function getCodeCount()
    {
        return $this->codeCount;
    }


    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }


    public function __toString()
    {
        return strval($this->id);
    }


    public function isEnabled()
    {
        return $this->isActive;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActivated($isActivated)
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    public function getIsActivated()
    {
        return $this->isActivated;
    }

    public function isAccountNonLocked()
    {
        return $this->isNonLocked;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function age()
    {
        return date_diff(date_create($this->birthday->format('Y-m-d')), date_create('today'))->y;
    }

    public function isPaying()
    {
//        var_dump(123);
//        echo 234;
//return true;
//        $currentUser->getSignUpDate()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s', strtotime('-48 hour'))
//        var_dump($this->getSignUpDate() /*<= date('Y-m-d H:i:s', strtotime('-48 hour'))*/);
        if (in_array($this->getGender()->getId(), [1,4]) && $this->getSignUpDate()->format('Y-m-d H:i:s') >  date('Y-m-d H:i:s', strtotime('-48 hour'))) {
            return true;
        }

        $date = new \DateTime();

        return $this->startSubscription instanceof \DateTime
        && $this->endSubscription instanceof \DateTime
        && $date >= $this->startSubscription
        && $date <= $this->endSubscription;
    }


    public function apiKey()
    {
        return md5($this->id) . md5($this->password);
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set gender
     *
     * @param integer $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Add ContactGender
     *
     * @param \AppBundle\Entity\Gender $gender
     * @return User
     */
    public function addContactGender(\AppBundle\Entity\Gender $gender)
    {
        $this->contactGender[] = $gender;

        return $this;
    }

    /**
     * Remove ContactGender
     *
     * @param \AppBundle\Entity\Gender $gender
     */
    public function removeContactGender(\AppBundle\Entity\Gender $gender)
    {
        $this->contactGender->removeElement($gender);
    }

    /**
     * Get ContactGender
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContactGender()
    {
        return $this->contactGender;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }


    /**
     * Set children
     *
     * @param integer $children
     * @return User
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get children
     *
     * @return integer
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * Set relationshipStatus
     *
     * @param integer relationshipStatus
     * @return User
     */
    public function setRelationshipStatus($relationshipStatus)
    {
        $this->relationshipStatus = $relationshipStatus;

        return $this;
    }

    /**
     * Get relationshipStatus
     *
     * @return integer
     */
    public function getRelationshipStatus()
    {
        return $this->relationshipStatus;
    }

    /**
     * Set relationshipType
     *
     * @param integer relationshipType
     * @return User
     */
    public function setRelationshipType($relationshipType)
    {
        $this->relationshipType = $relationshipType;

        return $this;
    }

    /**
     * Get relationshipType
     *
     * @return integer
     */
    public function getRelationshipType()
    {
        return $this->relationshipType;
    }

    /**
     * Set relationshipTypeDetails
     *
     * @param string $relationshipTypeDetails
     * @return User
     */
    public function setRelationshipTypeDetails($relationshipTypeDetails)
    {
        $this->relationshipTypeDetails = $relationshipTypeDetails;

        return $this;
    }

    /**
     * Get relationshipTypeDetails
     *
     * @return string
     */
    public function getRelationshipTypeDetails()
    {
        return $this->relationshipTypeDetails;
    }


    /**
     * Set sexOrientation
     *
     * @param integer $sexOrientation
     * @return User
     */
    public function setSexOrientation($sexOrientation)
    {
        $this->sexOrientation = $sexOrientation;

        return $this;
    }

    /**
     * Get sexOrientationDetails
     *
     * @return integer
     */
    public function getSexOrientationDetails()
    {
        return $this->sexOrientationDetails;
    }

    /**
     * Set sexOrientationDetails
     *
     * @param integer $sexOrientationDetails
     * @return User
     */
    public function setSexOrientationDetails($sexOrientationDetails)
    {
        $this->sexOrientationDetails = $sexOrientationDetails;

        return $this;
    }

    /**
     * Get sexOrientation
     *
     * @return integer
     */
    public function getSexOrientation()
    {
        return $this->sexOrientation;
    }

    /**
     * Set about
     *
     * @param string $about
     * @return User
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set ageFrom
     *
     * @param integet $ageFrom
     * @return User
     */
    public function setAgeFrom($ageFrom)
    {
        $this->ageFrom = $ageFrom;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAgeFrom()
    {
        return $this->ageFrom;
    }

    /**
     * Set ageFrom
     *
     * @param integet $ageTo
     * @return User
     */
    public function setAgeTo($ageTo)
    {
        $this->ageTo = $ageTo;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAgeTo()
    {
        return $this->ageTo;
    }

    /**
     * Set looking
     *
     * @param string $looking
     * @return User
     */
    public function setLooking($looking)
    {
        $this->looking = $looking;

        return $this;
    }

    /**
     * Get looking
     *
     * @return string
     */
    public function getLooking()
    {
        return $this->looking;
    }

    /**
     * Set lookingForDetails
     *
     * @param string $lookingForDetails
     * @return User
     */
    public function setLookingForDetails($lookingForDetails)
    {
        $this->lookingForDetails = $lookingForDetails;

        return $this;
    }

    /**
     * Get lookingForDetails
     *
     * @return string
     */
    public function getLookingForDetails()
    {
        return $this->lookingForDetails;
    }


    /**
     * Set smoking
     *
     * @param integer $smoking
     * @return User
     */
    public function setSmoking($smoking)
    {
        $this->smoking = $smoking;

        return $this;
    }

    /**
     * Get smoking
     *
     * @return integer
     */
    public function getSmoking()
    {
        return $this->smoking;
    }

    /**
     * Set height
     *
     * @param float $height
     * @return User
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set body
     *
     * @param integer $body
     * @return User
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return integer
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Add LookingFor
     *
     * @param \AppBundle\Entity\LookingFor $lookingFor
     * @return User
     */
    public function addLookingFor(\AppBundle\Entity\LookingFor $lookingFor)
    {
        $this->lookingFor[] = $lookingFor;

        return $this;
    }

    /**
     * Remove LookingFor
     *
     * @param \AppBundle\Entity\LookingFor $lookingFor
     */
    public function removeLookingFor(\AppBundle\Entity\LookingFor $lookingFor)
    {
        $this->lookingFor->removeElement($lookingFor);
    }

    /**
     * Get LookingFor
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLookingFor()
    {
        return $this->lookingFor;
    }

    /**
     * Set origin
     *
     * @param integer $origin
     * @return User
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return integer
     */
    public function getOrigin()
    {
        return $this->origin;
    }



    /**
     * Set zodiac
     *
     * @param \AppBundle\Entity\Zodiac $zodiac
     * @return User
     */
    public function setZodiac(\AppBundle\Entity\Zodiac $zodiac = null)
    {
        $this->zodiac = $zodiac;

        return $this;
    }

    /**
     * Get zodiac
     *
     * @return \AppBundle\Entity\Zodiac
     */
    public function getZodiac()
    {
         if(is_object($this->zodiac)){
            return $this->zodiac->getName();
         }else{
             return $this->zodiac;
         }
    }

    /**
     * Add payments
     *
     * @param \AppBundle\Entity\Payment $payments
     * @return User
     */
    public function addPayment(\AppBundle\Entity\Payment $payments)
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments
     *
     * @param \AppBundle\Entity\Payment $payments
     */
    public function removePayment(\AppBundle\Entity\Payment $payments)
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Add photos
     *
     * @param \AppBundle\Entity\Photo $photos
     * @return User
     */
    public function addPhoto(\AppBundle\Entity\Photo $photos)
    {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param \AppBundle\Entity\Photo $photos
     */
    public function removePhoto(\AppBundle\Entity\Photo $photos)
    {
        $this->photos->removeElement($photos);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Add ShowPhoto
     *
     * @param \AppBundle\Entity\ShowPhoto $showPhoto
     * @return User
     */
    public function addShowPhoto(\AppBundle\Entity\ShowPhoto $showPhoto)
    {
        $this->showPhoto[] = $showPhoto;

        return $this;
    }

    /**
     * Remove ShowPhoto
     *
     * @param \AppBundle\Entity\ShowPhoto $showPhoto
     */
    public function removeShowPhoto(\AppBundle\Entity\ShowPhoto $showPhoto)
    {
        $this->showPhoto->removeElement($showPhoto);
    }

    /**
     * Get ShowPhoto
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShowPhoto()
    {

        return new ArrayCollection(array_merge($this->showPhoto->toArray(), $this->showPhoto2->toArray()));
    }

    /**
     * Get main photo
     *
     * @return \AppBundle\Entity\Photo
     */
    public function getMainPhoto($is_curent_user = false)
    {
        //var_dump($this->photos[0]);
        foreach($this->photos as $photo){
            if(($photo->getIsValid() or $is_curent_user) && $photo->getIsMain()){
//                var_dump($photo);
                return $photo;
            }
        }

        return null;


        //It's the same just through the query to DB
        /*
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("isValid", true))
            ->andWhere(Criteria::expr()->eq("isMain", true))
        ;
        $collection = $this->getPhotos()->matching($criteria);
        return $collection[0];
        */
    }

    /**
     * @param \Datetime $lastActivityAt
     */
    public function setLastActivityAt($lastActivityAt)
    {
        $this->lastActivityAt = $lastActivityAt;
    }

    /**
     * @return \Datetime
     */
    public function getLastActivityAt()
    {
        return $this->lastActivityAt;
    }

    /**
     * @return Bool Whether the user is active or not
     */
    public function isOnline($minutes = 1440)
    {
        // Delay during wich the user will be considered as still active
        $delay = new \DateTime($minutes . ' minutes ago');

        return ( $this->getLastActivityAt() > $delay );
    }

    /**
     * @return Bool Whether the user is new or not
     */
    public function isNew($days = 30)
    {
        // Delay during wich the user will be considered as still new
        $delay = new \DateTime($days .' days ago');

        return ( $this->getSignUpDate() > $delay );
    }

    /**
     * Set isNonLocked
     *
     * @param boolean $isNonLocked
     * @return User
     */
    public function setIsNonLocked($isNonLocked)
    {
        $this->isNonLocked = $isNonLocked;

        return $this;
    }

    ///user.isAddFavorite(app.user.id)
    /**
     * Check if current user was favorited by user with id $userId (user.isAddFavorite(app.user.id))
     *
     * @param int $userId
     * @return boolean
     */
    public function isAddFavorite($userId)
    {
        $res = false;
        if($userId == $this->getId()){
            $res = true;
        }else {
            foreach ($this->favorited as $favorite) {
                if ($favorite->getMember()->getId() == $userId) {
                    $res = true;
                }
            }
        }
        return $res;
    }

    public function isAddBlackListed($userId)
    {
        $res = false;
        if($userId == $this->getId()){
            $res = true;
        }else {
            foreach ($this->blackListed as $blackList) {
                if ($blackList->getMember()->getId() == $userId) {
                    $res = true;
                }
            }
        }
        return $res;
    }

    public function getNoPhoto()
    {
//       $genderId = $this->gender->getId();
//       if ($genderId == 3) {
//           $genderId = 1;
//       } elseif ($genderId == 4) {
//           $genderId = 2;
//       }
        return '/images/no_photo_' . $this->gender->getId() . '.jpg';
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAddLike(\AppBundle\Entity\User $user)
    {
        $userId = $user->getId();
        if($userId == $this->getId() or $this->getMainPhoto() === null or $user->getMainPhoto() === null){
           return true;
        }else {
            foreach ($this->likes as $like) {

                if ($like->getUserTo()->getId() == $userId) {
                    return true;
                } // Check if current user liked the passed user

            }

            $contactLikes = $user->getLikes();
            foreach ($contactLikes as $cLike) {
                if ($cLike->getUserTo()->getId() == $this->getId() && $cLike->getIsBingo()) {
                    return true;
                }
            } // check if the passed user liked the current user and have bingo (current user like hin to)
        }
        return false;
    }



    /**
     * @param User $user
     *
     * Check if current user was verified by passed user (user.isAddVerify(app.user))
     *
     * @return bool
     */
    public function isAddVerify(\AppBundle\Entity\User $user)
    {
        $userId = $user->getId();

//        return count($this->verifyMe);

            foreach ($this->verifyMe as $verifyMe) {
            
                if ($verifyMe->getUserFrom()->getId() == $userId) {
                    return true;
                }
            }

        return false;
    }



    /**
     * Get isNonLocked
     *
     * @return boolean
     */
    public function getIsNonLocked()
    {
        return $this->isNonLocked;
    }

    /**
     * Set signUpDate
     *
     * @param \DateTime $signUpDate
     * @return User
     */
    public function setSignUpDate($signUpDate)
    {
        $this->signUpDate = $signUpDate;

        return $this;
    }

    /**
     * Get signUpDate
     *
     * @return \DateTime
     */
    public function getSignUpDate()
    {
        return $this->signUpDate;
    }

    /**
     * Get role
     *
     * @return \AppBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add notes
     *
     * @param \AppBundle\Entity\Note $notes
     * @return User
     */
    public function addNote(\AppBundle\Entity\Note $notes)
    {
        $this->notes[] = $notes;

        return $this;
    }

    /**
     * Remove notes
     *
     * @param \AppBundle\Entity\Note $notes
     */
    public function removeNote(\AppBundle\Entity\Note $notes)
    {
        $this->notes->removeElement($notes);
    }

    /**
     * Get notes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add notesAboutMe
     *
     * @param \AppBundle\Entity\Note $notesAboutMe
     * @return User
     */
    public function addNotesAboutMe(\AppBundle\Entity\Note $notesAboutMe)
    {
        $this->notesAboutMe[] = $notesAboutMe;

        return $this;
    }

    /**
     * Remove notesAboutMe
     *
     * @param \AppBundle\Entity\Note $notesAboutMe
     */
    public function removeNotesAboutMe(\AppBundle\Entity\Note $notesAboutMe)
    {
        $this->notesAboutMe->removeElement($notesAboutMe);
    }

    /**
     * Get notesAboutMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotesAboutMe(\AppBundle\Entity\User $owner = null)
    {
        if(null !== $owner){
            $criteria = Criteria::create()->where(Criteria::expr()->eq("owner", $owner));
            $collection = $this->notesAboutMe->matching($criteria);
            return $collection[0];
        }

        return $this->notesAboutMe;
    }

    /**
     * Set views
     *
     * @param integer $views
     * @return User
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Add viewed
     *
     * @param \AppBundle\Entity\View $viewed
     * @return User
     */
    public function addViewed(\AppBundle\Entity\View $viewed)
    {
        $this->viewed[] = $viewed;

        return $this;
    }

    /**
     * Remove viewed
     *
     * @param \AppBundle\Entity\View $viewed
     */
    public function removeViewed(\AppBundle\Entity\View $viewed)
    {
        $this->viewed->removeElement($viewed);
    }

    /**
     * Get viewed
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * Add viewedMe
     *
     * @param \AppBundle\Entity\View $viewedMe
     * @return User
     */
    public function addViewedMe(\AppBundle\Entity\View $viewedMe)
    {
        $this->viewedMe[] = $viewedMe;

        return $this;
    }

    /**
     * Remove viewedMe
     *
     * @param \AppBundle\Entity\View $viewedMe
     */
    public function removeViewedMe(\AppBundle\Entity\View $viewedMe)
    {
        $this->viewedMe->removeElement($viewedMe);
    }

    /**
     * Get viewedMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getViewedMe()
    {
        return $this->viewedMe;
    }

    /**
     * Add contacted
     *
     * @param \AppBundle\Entity\Contact $contacted
     * @return User
     */
    public function addContacted(\AppBundle\Entity\Contact $contacted)
    {
        $this->contacted[] = $contacted;

        return $this;
    }

    /**
     * Remove contacted
     *
     * @param \AppBundle\Entity\Contact $contacted
     */
    public function removeContacted(\AppBundle\Entity\Contact $contacted)
    {
        $this->contacted->removeElement($contacted);
    }

    /**
     * Get contacted
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacted()
    {
        return $this->contacted;
    }

    /**
     * Add contactedMe
     *
     * @param \AppBundle\Entity\Contact $contactedMe
     * @return User
     */
    public function addContactedMe(\AppBundle\Entity\Contact $contactedMe)
    {
        $this->contactedMe[] = $contactedMe;

        return $this;
    }

    /**
     * Remove contactedMe
     *
     * @param \AppBundle\Entity\Contact $contactedMe
     */
    public function removeContactedMe(\AppBundle\Entity\Contact $contactedMe)
    {
        $this->contactedMe->removeElement($contactedMe);
    }

    /**
     * Get contactedMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContactedMe()
    {
        return $this->contactedMe;
    }

    /**
     * Add favorited
     *
     * @param \AppBundle\Entity\Favorite $favorited
     * @return User
     */
    public function addFavorited(\AppBundle\Entity\Favorite $favorited)
    {
        $this->favorited[] = $favorited;

        return $this;
    }

    /**
     * Remove favorited
     *
     * @param \AppBundle\Entity\Favorite $favorited
     */
    public function removeFavorited(\AppBundle\Entity\Favorite $favorited)
    {
        $this->favorited->removeElement($favorited);
    }

    /**
     * Get favorited
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFavorited()
    {
        return $this->favorited;
    }

    /**
     * Add favoritedMe
     *
     * @param \AppBundle\Entity\Favorite $favoritedMe
     * @return User
     */
    public function addFavoritedMe(\AppBundle\Entity\Favorite $favoritedMe)
    {
        $this->favoritedMe[] = $favoritedMe;

        return $this;
    }

    /**
     * Remove favoritedMe
     *
     * @param \AppBundle\Entity\Favorite $favoritedMe
     */
    public function removeFavoritedMe(\AppBundle\Entity\Favorite $favoritedMe)
    {
        $this->favoritedMe->removeElement($favoritedMe);
    }

    /**
     * Get favoritedMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFavoritedMe()
    {
        return $this->favoritedMe;
    }

    /**
     * Add blackListed
     *
     * @param \AppBundle\Entity\BlackList $blackListed
     * @return User
     */
    public function addBlackListed(\AppBundle\Entity\BlackList $blackListed)
    {
        $this->blackListed[] = $blackListed;

        return $this;
    }

    /**
     * Remove blackListed
     *
     * @param \AppBundle\Entity\BlackList $blackListed
     */
    public function removeBlackListed(\AppBundle\Entity\BlackList $blackListed)
    {
        $this->blackListed->removeElement($blackListed);
    }

    /**
     * Get blackListed
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlackListed()
    {
        return $this->blackListed;
    }

    /**
     * Add blackListedMe
     *
     * @param \AppBundle\Entity\BlackList $blackListedMe
     * @return User
     */
    public function addBlackListedMe(\AppBundle\Entity\BlackList $blackListedMe)
    {
        $this->blackListedMe[] = $blackListedMe;

        return $this;
    }

    /**
     * Remove blackListedMe
     *
     * @param \AppBundle\Entity\BlackList $blackListedMe
     */
    public function removeBlackListedMe(\AppBundle\Entity\BlackList $blackListedMe)
    {
        $this->blackListedMe->removeElement($blackListedMe);
    }

    /**
     * Get blackListedMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlackListedMe()
    {
        return $this->blackListedMe;
    }

    /**
     * Set isFrozen
     *
     * @param boolean $isFrozen
     * @return User
     */
    public function setIsFrozen($isFrozen)
    {
        $this->isFrozen = $isFrozen;

        return $this;
    }

    /**
     * Get isFrozen
     *
     * @return boolean
     */
    public function getIsFrozen()
    {
        return $this->isFrozen;
    }

    /**
     * Set freezeReason
     *
     * @param string $freezeReason
     * @return User
     */
    public function setFreezeReason($freezeReason)
    {
        $this->freezeReason = $freezeReason;

        return $this;
    }

    /**
     * Get freezeReason
     *
     * @return string
     */
    public function getFreezeReason()
    {
        return $this->freezeReason;
    }

    /**
     * Set banReason
     *
     * @param string $banReason
     * @return User
     */
    public function setBanReason($banReason)
    {
        $this->banReason = $banReason;

        return $this;
    }

    /**
     * Get banReason
     *
     * @return string
     */
    public function getBanReason()
    {
        return $this->banReason;
    }

    /**
     * Set isFlagged
     *
     * @param boolean $isFlagged
     * @return User
     */
    public function setIsFlagged($isFlagged)
    {
        $this->isFlagged = $isFlagged;

        return $this;
    }

    /**
     * Get isFlagged
     *
     * @return boolean
     */
    public function getIsFlagged()
    {
        return $this->isFlagged;
    }

    /**
     * Set startSubscription
     *
     * @param \DateTime $startSubscription
     * @return User
     */
    public function setStartSubscription($startSubscription)
    {
        $this->startSubscription = $startSubscription;

        return $this;
    }

    /**
     * Get startSubscription
     *
     * @return \DateTime
     */
    public function getStartSubscription()
    {
        return $this->startSubscription;
    }

    /**
     * Set endSubscription
     *
     * @param \DateTime $endSubscription
     * @return User
     */
    public function setEndSubscription($endSubscription)
    {
        $this->endSubscription = $endSubscription;

        return $this;
    }

    /**
     * Get endSubscription
     *
     * @return \DateTime
     */
    public function getEndSubscription()
    {
        return $this->endSubscription;
    }

    /**
     * Set points
     *
     * @param integer $points
     * @return User
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set adminComments
     *
     * @param string $adminComments
     * @return User
     */
    public function setAdminComments($adminComments)
    {
        $this->adminComments = $adminComments;

        return $this;
    }

    /**
     * Get adminComments
     *
     * @return string
     */
    public function getAdminComments()
    {
        return $this->adminComments;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
//        if(substr($phone, 0, 1) == '0'){
//            $phone = '+972'.substr($phone, 1);
//        }
//        if(substr($phone, 0, 4) != '+972'){
//            $phone = '+972' . $phone;
//        }
        $phone = preg_replace('/\D/', '', $phone);
        $this->phone = $phone;

//        $otherPhoneFormat = str_replace('972','0',$user->userPhone);

//        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return User
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return User
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * Set codeDate
     *
     * @param \DateTime $codeDate
     * @return User
     */
    public function setCodeDate($codeDate)
    {
        $this->codeDate = $codeDate;

        return $this;
    }

    /**
     * Get codeDate
     *
     * @return \DateTime
     */
    public function getCodeDate()
    {
        return $this->codeDate;
    }


    /**
     * Set loginFrom
     *
     * @param \AppBundle\Entity\LoginFrom $loginFrom
     * @return User
     */
    public function setLoginFrom(\AppBundle\Entity\LoginFrom $loginFrom = null)
    {
        $this->loginFrom = $loginFrom;

        return $this;
    }

    /**
     * Get loginFrom
     *
     * @return \AppBundle\Entity\LoginFrom
     */
    public function getLoginFrom()
    {
        return $this->loginFrom;
    }

    /**
     * Set isOnHome
     *
     * @param boolean $isOnHomepage
     * @return User
     */
    public function setIsOnHomepage($isOnHomepage)
    {
        $this->isOnHomepage = $isOnHomepage;

        return $this;
    }

    /**
     * Get isOnHomepage
     *
     * @return boolean
     */
    public function getIsOnHomepage()
    {
        return $this->isOnHomepage;
    }

    /**
     * Set lastloginAt
     *
     * @param \DateTime $lastloginAt
     * @return User
     */
    public function setLastloginAt($lastloginAt)
    {
        $this->lastloginAt = $lastloginAt;

        return $this;
    }

    /**
     * Get lastloginAt
     *
     * @return \DateTime
     */
    public function getLastloginAt()
    {
        return $this->lastloginAt;
    }

    /**
     * Set lastRealActivityAt
     *
     * @param \DateTime $lastRealActivityAt
     * @return User
     */
    public function setLastRealActivityAt($lastRealActivityAt)
    {
        $this->lastRealActivityAt = $lastRealActivityAt;

        return $this;
    }

    /**
     * Get lastRealActivityAt
     *
     * @return \DateTime
     */
    public function getLastRealActivityAt()
    {
        return $this->lastRealActivityAt;
    }

    /**
     * Add connected
     *
     * @param \AppBundle\Entity\Communication $connected
     * @return User
     */
    public function addConnected(\AppBundle\Entity\Communication $connected)
    {
        $this->connected[] = $connected;

        return $this;
    }

    /**
     * Remove connected
     *
     * @param \AppBundle\Entity\Communication $connected
     */
    public function removeConnected(\AppBundle\Entity\Communication $connected)
    {
        $this->connected->removeElement($connected);
    }

    /**
     * Get connected
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnected()
    {
        return $this->connected;
    }

    /**
     * Add connectedMe
     *
     * @param \AppBundle\Entity\Communication $connectedMe
     * @return User
     */
    public function addConnectedMe(\AppBundle\Entity\Communication $connectedMe)
    {
        $this->connectedMe[] = $connectedMe;

        return $this;
    }

    /**
     * Remove connectedMe
     *
     * @param \AppBundle\Entity\Communication $connectedMe
     */
    public function removeConnectedMe(\AppBundle\Entity\Communication $connectedMe)
    {
        $this->connectedMe->removeElement($connectedMe);
    }

    /**
     * Get connectedMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnectedMe()
    {
        return $this->connectedMe;
    }

    /**
     * Set oldPassword
     *
     * @param string $oldPassword
     * @return Users
     */
    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    /**
     * Get oldPassword
     *
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * Add notifications
     *
     * @param \AppBundle\Entity\UserNotifications $notifications
     * @return User
     */
    public function addNotification(\AppBundle\Entity\UserNotifications $notifications)
    {
        $this->notifications[] = $notifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \AppBundle\Entity\UserNotifications $notifications
     */
    public function removeNotification(\AppBundle\Entity\UserNotifications $notifications)
    {
        $this->notifications->removeElement($notifications);
    }


    /**
     * Add notifications
     *
     * @param \AppBundle\Entity\UserMessengerNotifications $userMessengerNotifications
     * @return User
     */
    public function addUserMessengerNotification(\AppBundle\Entity\UserMessengerNotifications $userMessengerNotifications)
    {
        $this->userMessengerNotification[] = $userMessengerNotifications;

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \AppBundle\Entity\UserMessengerNotifications $userMessengerNotifications
     */
    public function removeUserMessengerNotification(\AppBundle\Entity\UserMessengerNotifications $userMessengerNotifications)
    {
        $this->notifications->removeElement($userMessengerNotifications);
    }


    /**
     * Add likes
     *
     * @param LikeMe $likes
     * @return User
     */
    public function addLike(LikeMe $likes)
    {
        $this->likes[] = $likes;

        return $this;
    }

    /**
     * Remove likes
     *
     * @param LikeMe $likes
     */
    public function removeLike(LikeMe $likes)
    {
        $this->likes->removeElement($likes);
    }

    /**
     * Get likes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * Add likesMe
     *
     * @param LikeMe $likesMe
     * @return User
     */
    public function addLikesMe(LikeMe $likesMe)
    {
        $this->likesMe[] = $likesMe;

        return $this;
    }

    /**
     * Remove likesMe
     *
     * @param LikeMe $likesMe
     */
    public function removeLikesMe(LikeMe $likesMe)
    {
        $this->likesMe->removeElement($likesMe);
    }

    /**
     * Get likesMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLikesMe()
    {
        return $this->likesMe;
    }



    /**
     * Add likes
     *
     * @param ArenaDislike $dislikes
     * @return User
     */
    public function addDislike(ArenaDislike $dislikes)
    {
        $this->dislikes[] = $dislikes;

        return $this;
    }

    /**
     * Remove likes
     *
     * @param ArenaDislike $dislikes
     */
    public function removeDislike(ArenaDislike $dislikes)
    {
        $this->dislikes->removeElement($dislikes);
    }

    /**
     * Get likes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDislikes()
    {
        return $this->dislikes;
    }

    /**
     * Add likesMe
     *
     * @param ArenaDislike $dislikeMe
     * @return User
     */
    public function addDislikesMe(ArenaDislike $dislikeMe)
    {
        $this->likesMe[] = $dislikeMe;

        return $this;
    }

    /**
     * Remove likesMe
     *
     * @param ArenaDislike $arenadislikeMe
     */
    public function removeDislikesMe(LikeMe $dislikeMe)
    {
        $this->likesMe->removeElement($dislikeMe);
    }

    /**
     * Get likesMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDislikesMe()
    {
        return $this->likesMe;
    }



    /**
     * Add verifyMe
     *
     * @param Verify $verify
     * @return User
     */
    public function addVerifyMe(Verify $verify)
    {
        $this->verifyMe[] = $verify;

        return $this;
    }

    /**
     * Remove verifyMe
     *
     * @param Verify $verify
     */
    public function removeVerifyMe(Verify $verify)
    {
        $this->verifyMe->removeElement($verify);
    }

    /**
     * Get verifyMe
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVerifyMe()
    {
        return $this->verifyMe;
    }



    /**
     * Set facebook
     *
     * @param string $facebook
     * @return User
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }


//    public function getDistance($contact)
//    {
//        $this->userRepo->getDistance($this, $contact);
//    }


    /**
     * @return mixed
     */
    public function getAgree()
    {
        return $this->agree;
    }

    /**
     * @param mixed $agree
     */
    public function setAgree($agree)
    {
        $this->agree = $agree;
    }


    /**
     * Set nutrition
     *
     * @param integer $nutrition
     * @return User
     */
    public function setNutrition($nutrition)
    {
        $this->nutrition = $nutrition;

        return $this;
    }

    /**
     * Get nutrition
     *
     * @return integer
     */
    public function getNutrition()
    {
        return $this->nutrition;
    }


    /**
     * Set religion
     *
     * @param integer $religion
     * @return User
     */
    public function setReligion($religion)
    {
        $this->religion = $religion;

        return $this;
    }

    /**
     * Get religion
     *
     * @return integer
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * @param bool $isUpdatedZodiac
     */
    public function setIsUpdatedZodiac($isUpdatedZodiac)
    {
        $this->isUpdatedZodiac = $isUpdatedZodiac;
    }

    public function getIsUpdatedZodiac() {
        return $this->isUpdatedZodiac;
    }

    public function isMan() {
        return in_array($this->getGender()->getId(), [1,4]);
    }

    public function isBingoPushToday() {

        $count = 0;
        foreach ($this->notifications as $notification) {
            if ($notification->getDate() && $notification->getNotification()->getId() === 2) {
                if ($notification->getDate()->format('Y-m-d') == date('Y-m-d')) {
                    $count++;
                    if ($count == 2) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getCountry(){
        return $this->country;
    }
    public function getRegion(){
        return $this->region;
    }
    public function getCity(){
        return $this->city;
    }

    public function setCountry($country){
        $this->country = $country;
        return $this->country;
    }
    public function setRegion($region){
        $this->region = $region;
        return $this->region;
    }
    public function setCity($city){
        $this->city = $city;
        return $this->city;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode(){
        return $this->zipCode;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return User
     */
    public function setZipCode($zipcode){
        $this->zipCode = $zipcode;
        return $this;
    }



}
