<?php

namespace Newsfeed\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;


/**
 * AbstractEntity
 *
 */
abstract class AbstractEntity
{
	/**
	 * @var \Integer
	 *
	 * @ORM\Column(type="guid", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="UUID")
	 * @Annotation\Exclude()
	 */
	public $id;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=false)
	 * @Annotation\Exclude()
	 */
	public $created;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Annotation\Exclude()
	 */
	public $updated;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Annotation\Exclude()
	 * @Annotation\Required(false)
	 */
	public $deleted;


	public function __get($name)
	{
		return $this->{strtolower($name)};
	}

	public function __set($name, $value)
	{
		return $this->{strtolower($name)} = $value;
	}

    public function __toString()
    {
        return $this->id;
    }


    public function __call($name, $arguments)
    {
        if(substr($name, 0,3)=='get')
        {
            $strGetValue = substr($name, 3);
            return $this->$strGetValue;
        }
        elseif(substr($name, 0,3)=='set')
        {
            $strGetValue = substr($name, 3);
            $this->$strGetValue = $arguments[0];
        }
        else
        {
            die('uncallable function: '.$name);
        }
    }

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function preUpdate()
	{
		if (empty($this->created))
		{
			$this->created = new \DateTime();
            $this->updated = null;
            $this->deleted = null;
		}
		else
        {
            $this->updated = new \DateTime();
        }
	}

	public function getArrayCopy()
	{
		return get_object_vars($this);
	}

	public function exchangeArray($arrData)
	{
		foreach($arrData AS $strKey => $mixData)
		{
			$this->$strKey = $mixData;
		}
	}

}