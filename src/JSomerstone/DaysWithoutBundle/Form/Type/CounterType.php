<?php
namespace JSomerstone\DaysWithoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CounterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('thing', 'text', array('max_length' => 64, 'required' => true))
            ->add('public', 'submit')
            ->add('owner', new UserType())
            ->add('private', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'JSomerstone\DaysWithoutBundle\Model\CounterModel',
        ));
    }

    public function getName()
    {
        return 'counter';
    }
}
