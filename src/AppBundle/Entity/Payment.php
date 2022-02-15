<?php

namespace AppBundle\Entity;

use AppBundle\AppBundle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\Criteria;

/**
 * PaymentHistory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Payment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="payments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="PaymentHistory", mappedBy="payment")
     */
    private $paymentHistories;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="next_payment_date", type="date", nullable=true)
     */
    private $nextPaymentDate;


    /**
     * @var string
     *
     * @ORM\Column(name="transaction_id", type="string", length=50, nullable=true)
     */
    private $transactionId;


    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="string", length=15, nullable=true)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="pay_period", type="string", length=25, nullable=true)
     */
    private $payPeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="full_data", type="text")
     */
    private $fullData;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="error", type="integer")
     */
    private $error = 0;



    public function __construct() {
        //$this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->paymentHistories = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Payment
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add paymentHistory
     *
     * @param \AppBundle\Entity\PaymentHistory $paymentHistory
     * @return Payment
     */
    public function addPaymentHistory(\AppBundle\Entity\PaymentHistory $paymentHistory)
    {
        $this->paymentHistories[] = $paymentHistory;

        return $this;
    }

    /**
     * Remove paymentHistory
     *
     * @param \AppBundle\Entity\PaymentHistory $paymentHistory
     */
    public function removePaymentHistory(\AppBundle\Entity\PaymentHistory $paymentHistory)
    {
        $this->paymentHistories->removeElement($paymentHistory);
    }

    /**
     * Get paymentHistories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPaymentHistories()
    {
        return $this->paymentHistories;
    }

    /**
     * Set nextPaymentDate
     *
     * @param \DateTime $nextPaymentDate
     * @return Payment
     */
    public function setNextPaymentDate($nextPaymentDate)
    {
        $this->nextPaymentDate = $nextPaymentDate;

        return $this;
    }

    /**
     * Get nextPaymentDate
     *
     * @return \DateTime
     */
    public function getNextPaymentDate()
    {
        return $this->nextPaymentDate;
    }

    /**
     * Set transactionId
     *
     * @param string $transactionId
     * @return Payment
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Get transactionId
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Payment
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Payment
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

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
     * Set payPeriod
     *
     * @param string $payPeriod
     * @return Payment
     */
    public function setPayPeriod($payPeriod)
    {
        $this->payPeriod = $payPeriod;

        return $this;
    }

    /**
     * Get payPeriod
     *
     * @return string
     */
    public function getPayPeriod()
    {
        return $this->payPeriod;
    }

    /**
     * Set fullData
     *
     * @param array $fullData
     * @return Payment
     */
    public function setFullData($fullData)
    {
        $fullData = serialize((array)$fullData);
        $this->fullData = $fullData;

        return $this;
    }

    /**
     * Get fullData
     *
     * @return array
     */
    public function getFullData()
    {
        $dc2array = unserialize($this->fullData);
        return $dc2array;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return Payment
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Payment
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set error
     *
     * @return Payment
     */
    public function setError()
    {
        $this->error += 1;

        return $this;
    }

    /**
     * Get error
     *
     * @return integer
     */
    public function getError()
    {
        return $this->error;
    }


}
