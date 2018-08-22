<?php

namespace Newsfeed\Entity;

use Doctrine\ORM\Mapping as ORM;
use IntiresCore\Service\CalcCustoms;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Container
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User
    extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    public $Username;

    /**
     * @ORM\Column(type="password", length=50, nullable=true)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":5, "max":30}})
     * @Annotation\Options({"label":"Passwort"})
     * @Annotation\Type("Zend\Form\Element\Text")
     */
    public $Password;


    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    public $AmazonUsername;

    /**
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    public $AmazonPassword;

    /**
     * @ORM\Column(type="text")
     */
    public $AmazonStore;

    /**
     * @ORM\Column(type="text")
     */
    public $AlexasSelected;
}
