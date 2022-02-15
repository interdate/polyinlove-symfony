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
class PaymentHistory
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
     * @ORM\ManyToOne(targetEntity="Payment", inversedBy="paymentHistories")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id")
     **/
    private $payment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="date")
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_payment_date", type="date")
     */
    private $endPaymentDate;

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
    private $note = '' ;



    public function __construct() {
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
     * Set paymentDate
     *
     * @param \DateTime $paymentDate
     * @return PaymentHistory
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * Get paymentDate
     *
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * Set endPaymentDate
     *
     * @param \DateTime $endPaymentDate
     * @return PaymentHistory
     */
    public function setEndPaymentDate($endPaymentDate)
    {
        $this->endPaymentDate = $endPaymentDate;

        return $this;
    }

    /**
     * Get endPaymentDate
     *
     * @return \DateTime
     */
    public function getEndPaymentDate()
    {
        return $this->endPaymentDate;
    }

    /**
     * Set fullData
     *
     * @param array $fullData
     * @return PaymentHistory
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
     * @return PaymentHistory
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
     * Set payment
     *
     * @param \AppBundle\Entity\Payment $payment
     * @return PaymentHistory
     */
    public function setPayment(\AppBundle\Entity\Payment $payment){
        $this->payment = $payment;
        return $this;
    }

    /**
     * Get payment
     *
     * @return \AppBundle\Entity\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

//    public function getEndDate(){
//        $endDate = new \DateTime($this->paymentDate->format('Y-m-d H:i:s'));
//        $ppdata = $this->getFullData();
//        $custom = explode("_",$ppdata['custom']);
//        $per = $custom[1];
//        $strPer = 'P14D';
//        if($per == 1){
//            $strPer = 'P1Y';
//        }elseif($per == 2){
//            $strPer = 'P6M';
//        }elseif($per == 3){
//            $strPer = 'P3M';
//        }elseif($per == 4){
//            $strPer = 'P1M';
//        }
//        $endDate->add(new \DateInterval($strPer));
//        return $endDate;
//    }

}
