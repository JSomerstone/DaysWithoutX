<?php
namespace JSomerstone\DaysWithoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetType extends AbstractType
{
    private $nick = false;

    public function __construct($nick = null)
    {
        $this->nick = $nick;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('owner', new UserType($this->nick))
            ->add(
                'reset',
                'submit'
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        #$resolver->setDefaults(array(
        #    'data_class' => 'JSomerstone\DaysWithoutBundle\Model\UserModel',
        #));
    }

    public function getName()
    {
        return 'resetForm';
    }
}
