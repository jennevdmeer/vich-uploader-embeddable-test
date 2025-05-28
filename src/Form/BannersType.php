<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Banners;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BannersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('largeRectangle', VichImageType::class, [
            'required' => false,
        ]);

        $builder->add('largeSkyscraper', VichImageType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Banners::class,
        ]);
    }
}
