<?php
namespace JSomerstone\DaysWithoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nick', 'text', array('max_length' => 32, 'required' => false))
            ->add('password', 'password', array('required' => false));

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