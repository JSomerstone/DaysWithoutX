<?php
namespace JSomerstone\DaysWithoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OwnerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('owner', new OwnerType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'JSomerstone\DaysWithoutBundle\Model\UserModel',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'owner';
    }
}