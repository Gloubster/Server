<?php

namespace Gloubster\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class JobSetType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file', 'text', array(
            'label'       => 'File (HTTP)',
            'constraints' => array(new NotBlank(), new Url())
            )
        );
        $builder->add('specifications', 'collection', array(
            'type'         => new SpecificationType(),
            'allow_add'    => true,
            'by_reference' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gloubster\\Documents\\JobSet',
        ));
    }

    public function getName()
    {
        return 'jobset';
    }
}
