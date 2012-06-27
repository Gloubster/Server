<?php

namespace Gloubster\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class SpecificationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'choice', array(
            'choices'   => array(
                'video'   => 'Video',
                'image' => 'Image',
                'audio'   => 'Audio',
            ),
            'multiple'  => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gloubster\\Documents\\Specification',
        ));
    }

    public function getName()
    {
        return 'specification';
    }
}
