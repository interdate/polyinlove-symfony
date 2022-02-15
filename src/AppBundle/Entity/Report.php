<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Report
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Report
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="params", type="json_array")
     */
    private $params;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_flagged", type="boolean", length=1)
     */
    private $isFlagged;

    private $count;

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
     * Set name
     *
     * @param string $name
     * @return Report
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
     * Set params
     *
     * @param array $params
     * @return Report
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return array 
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set isFlagged
     *
     * @param boolean $isFlagged
     * @return Report
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

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }


    public function countResults()
    {
    	$data = json_decode($this->getParams(), true);
    	$qb = $this->createQueryBuilder('u');
    	//return count($data);
    	if(!empty($data['id'])){
    		$qb->where('u.id = :id')->setParameter('id', $data['id']);
    	}
    	elseif(!empty($data['email'])){
    		$qb->where(
    				$qb->expr()->like('u.email', ":email")
    				)->setParameter('email', '%' . $data['email'] . '%');
    	}
    	elseif(!empty($data['username'])){
    		$qb->where(
    				$qb->expr()->like('u.username', ":username")
    				)->setParameter('username', '%' . $data['username'] . '%');
    	}
    	else{
    		
    		if(!empty($data['region'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.region', ":region")
    					)->setParameter('region', $data['region']);
    		}
    	
    		if(!empty($data['type'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.type', ":type")
    					)->setParameter('type', $data['type']);
    		}
    	
    		if(!empty($data['ageFrom']) && !empty($data['ageTo'])){
    			$data['date_1'] = date("Y") - $data['ageFrom'] . '-' . date("m") . '-' . date("d");
    			$data['date_2'] = date("Y") - $data['ageTo'] . '-01-01';
    			  	
    	
    			$qb->andWhere('u.birthday <= :date_1')
    			->andWhere('u.birthday >= :date_2')
    			->setParameter('date_1', $data['date_1'])
    			->setParameter('date_2', $data['date_2'])
    			;
    		}
    	
    		if(!empty($data['heightFrom']) && !empty($data['heightTo'])){
    			$qb->andWhere('u.height >= :heightFrom')
    			->andWhere('u.height <= :heightTo')
    			->setParameter('heightFrom', $data['heightFrom'])
    			->setParameter('heightTo', $data['heightTo'])
    			;
    		}
    	
    		if(!empty($data['body'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.body', ":body")
    					)->setParameter('body', $data['body']);
    		}
    	
    		if(!empty($data['relationshipStatus'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.relationshipStatus', ":relationshipStatus")
    					)->setParameter('relationshipStatus', $data['relationshipStatus']);
    		}
    	
    		if(!empty($data['occupation'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.occupation', ":occupation")
    					)->setParameter('occupation', $data['occupation']);
    		}
    	
    		if(!empty($data['education'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.education', ":education")
    					)->setParameter('education', $data['education']);
    		}
    	
    		if(!empty($data['religion'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.religion', ":religion")
    					)->setParameter('religion', $data['religion']);
    		}
    	
    		if(!empty($data['sexOrientation'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.sexOrientation', ":sexOrientation")
    					)->setParameter('sexOrientation', $data['sexOrientation']);
    		}
    	
    		if(!empty($data['purposes'][0])){
    			$qb->join('u.purposes', 'p', 'WITH',
    					$qb->expr()->in('p.id', ':purposes')
    					)->setParameter('purposes', $data['purposes']);
    		}
    	
    		if(!empty($data['veggieReasons'][0])){
    			$qb->join('u.veggieReasons', 'vr', 'WITH',
    					$qb->expr()->in('vr.id', ':veggieReasons')
    					)->setParameter('veggieReasons', $data['veggieReasons']);
    		}
    	
    		if(!empty($data['interests'][0])){
    			$qb->join('u.interests', 'i', 'WITH',
    					$qb->expr()->in('i.id', ':interests')
    					)->setParameter('interests', $data['interests']);
    		}
    	
    		if(!empty($data['smoking'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.smoking', ":smoking")
    					)->setParameter('smoking', $data['smoking']);
    		}
    	
    		if(!empty($data['drinking'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.drinking', ":drinking")
    					)->setParameter('drinking', $data['drinking']);
    		}
    	
    		if(!empty($data['children'][0])){
    			$qb->andWhere(
    					$qb->expr()->in('u.children', ":children")
    					)->setParameter('children', $data['children']);
    		}
    	
    		if(!empty($data['withPhoto'])){
    			
    			$qb->join('u.photos', 'ph', 'WITH',
    					$qb->expr()->eq('ph.isValid', true)
    					);
    		}
    	
    		
    	
    		if( !empty($data['gender']) ){
    			$qb->andWhere('u.gender = :gender')
    			->setParameter('gender', $data['gender'])
    			;
    		}
    	
    	
    		//if($this->admin){
    	
    			if(!empty($data['zodiac'])) {
    				$qb->andWhere(
    						$qb->expr()->in('u.zodiac', ":zodiac")
    						)->setParameter('zodiac', $data['zodiac']);
    			}
    	
    			if(!empty($data['loginFrom'])) {
    				$qb->andWhere(
    						$qb->expr()->in('u.loginFrom', ":loginFrom")
    						)->setParameter('loginFrom', $data['loginFrom']);
    			}
    	
    			if(!empty($data['isActive'])){
    				$isActive = $data['isActive'] == 1 ? 1 : 0;
    				$qb->andWhere('u.isActive = ' . $isActive);
    			}
    	
    			if(!empty($data['isFrozen'])){
    				$isFrozen = $data['isFrozen'] == 1 ? 1 : 0;
    				$qb->andWhere('u.isFrozen = ' . $isFrozen);
    			}
    	
    			if(!empty($data['isPhone'])){
    				$not = $data['isPhone'] == 1 ? 'NOT ' : '';
    				$qb->andWhere("u.phone IS " . $not . "NULL");
    			}
    	
    			if(!empty($data['hasPoints'])){
    				if($data['hasPoints'] == 1){
    					$qb->andWhere("u.points > 0");
    				}
    				else{
    					$qb->andWhere("u.points = 0");
    				}
    			}
    	
    			if(!empty($data['isPaying'])){
    	
    				$date = date("Y-m-d");
    	
    				if($data['isPaying'] == 1){
    					$qb->andWhere("u.startSubscription <= '" . $date ."'")
    					->andWhere("u.endSubscription >= '" . $date . "'")
    					;
    				}
    				else{
    					$qb->andWhere(
    							"u.startSubscription IS NULL OR u.endSubscription IS NULL OR u.endSubscription < '" . $date . "'"
    							);
    				}
    			}
    	
    			if(!empty($data['isPhoto'])){
    				if($data['isPhoto'] == 1){
    					$qb->join('u.photos', 'ph', 'WITH',
    							$qb->expr()->eq('ph.isValid', true)
    							);
    				}
    				else{
    					$qb->andWhere(
    							$qb->expr()->not(
    									$qb->expr()->exists("SELECT p.id FROM AppBundle:Photo p WHERE  p.user = u.id AND p.isValid = 1")
    									)
    							);
    				}
    			}
    	
    			if(!empty($data['startSubscriptionFrom']) && !empty($data['startSubscriptionTo'])){
    				$this->setFromToDateConditions(
    						$data['startSubscriptionFrom'],
    						$data['startSubscriptionTo'],
    						'startSubscription',
    						$qb
    						);
    			}
    	
    			if(!empty($data['endSubscriptionFrom']) && !empty($data['endSubscriptionTo'])){
    				$this->setFromToDateConditions(
    						$data['endSubscriptionFrom'],
    						$data['endSubscriptionTo'],
    						'endSubscription',
    						$qb
    						);
    			}
    	
    			if(!empty($data['signUpFrom']) && !empty($data['signUpTo'])){
    				$this->setFromToDateConditions(
    						$data['signUpFrom'],
    						$data['signUpTo'],
    						'signUpDate',
    						$qb
    						);
    			}
    	
    			if(!empty($data['lastVisitedFrom']) && !empty($data['lastVisitedTo'])){
    				$this->setFromToDateConditions(
    						$data['lastVisitedFrom'],
    						$data['lastVisitedTo'],
    						'lastActivityAt',
    						$qb
    						);
    			}
    	
    			if(!empty($data['ip'])) {
    				$qb->andWhere('u.ip = :ip')->setParameter('ip', $data['ip']);
    			}
    	
    			//echo $qb->getDQL();
    			//die;
    		//}
    	}
    
    	$users = $qb->getQuery()->getResult();
    	shuffle($users);
    	return $users;
    	
    	//return $qb->getQuery()->getSingleScalarResult();
    }

}
