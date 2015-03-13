<?php
namespace JSomerstone\DaysWithoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    private $nick;
    public function __construct($nick = null)
    {
        $this->nick = $nick;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'nick',
            'text',
            array(
                'max_length' => 32,
                'required' => false,
                'data' => $this->nick
            )
        )
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