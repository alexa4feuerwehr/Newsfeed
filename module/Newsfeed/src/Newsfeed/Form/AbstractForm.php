<?php


namespace Newsfeed\Form;

use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Annotation\AnnotationBuilder;

abstract class AbstractForm
    extends Form
{
    //
    protected $strName = 'registration';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $objEntityManager;

    /**
     * AbstractForm constructor.
     * @param \Interop\Container\ContainerInterface $objServiceManager
     */
    public function __construct(\Interop\Container\ContainerInterface $objServiceManager)
    {
        $this->objEntityManager = $objServiceManager->get('doctrine.entitymanager.orm_default');
        //
        return parent::__construct($this->strName, []);
    }

    /**
     * @param Form $objForm
     * @param $arrOrder
     */
    protected function setFormFieldOrder(\Zend\Form\Form &$objForm, $arrOrder)
    {
        $arrFields = [];
        //
        foreach ($arrOrder AS $strField)
        {
            $arrFields[] = $objForm->get($strField);
        }
        // entferne alle felder & validations die nicht mehr vorhanden sind
        foreach($objForm->getElements() AS $strField=>$objElement)
        {
            $objForm->remove($strField);
            $objForm->filter->remove($strField);
        }
        //
        /** @var \Zend\Form\Element $objField */
        foreach ($arrFields as $objField)
        {
            $objField->setAttribute('placeholder', $objField->getLabel());
            #$objField->setOption('twb-layout', \TwbBundle\Form\View\Helper\TwbBundleForm::LAYOUT_INLINE);
            $objForm->add($objField);
        }
    }

    /**
     * @param $strEntity
     * @return Form
     */
    protected function buildFormFromEntity($strEntity)
    {
        $builder = new AnnotationBuilder();
        $objForm = $builder->createForm($strEntity);
        $objForm->setHydrator(new DoctrineHydrator($this->objEntityManager), $strEntity);

        return $objForm;
    }
}