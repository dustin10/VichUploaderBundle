<?php

namespace Vich\UploaderBundle\Form\Type;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 * @author Massimiliano Arione <max.arione@gmail.com>
 */
class VichImageType extends VichFileType
{
    /**
     * @var CacheManager|null
     */
    private $cacheManager;

    public function __construct(
        StorageInterface $storage,
        UploadHandler $handler,
        PropertyMappingFactory $factory,
        PropertyAccessorInterface $propertyAccessor = null,
        CacheManager $cacheManager = null
    ) {
        parent::__construct($storage, $handler, $factory, $propertyAccessor);
        $this->cacheManager = $cacheManager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'image_uri' => true,
            'imagine_pattern' => null,
        ]);

        $resolver->setAllowedTypes('image_uri', ['bool', 'string', 'callable']);

        $imageUriNormalizer = function (Options $options, $imageUri) {
            return $imageUri ?? $options['download_uri'];
        };

        $resolver->setNormalizer('image_uri', $imageUriNormalizer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $object = $form->getParent()->getData();
        $view->vars['object'] = $object;
        $view->vars['image_uri'] = null;
        $view->vars['download_uri'] = null;

        if (null !== $object) {
            if ($options['imagine_pattern']) {
                if (null === $this->cacheManager) {
                    throw new \RuntimeException('LiipImagineBundle must be installed and configured for using "imagine_pattern" option.');
                }

                $path = $this->storage->resolveUri($object, $form->getName(), null);
                if (null !== $path) {
                    $view->vars['image_uri'] = $this->cacheManager->getBrowserPath($path, $options['imagine_pattern']);
                }
            } else {
                $view->vars['image_uri'] = $this->resolveUriOption($options['image_uri'], $object, $form);
            }

            $view->vars = \array_replace(
                $view->vars,
                $this->resolveDownloadLabel($options['download_label'], $object, $form)
            );

            $view->vars['download_uri'] = $this->resolveUriOption($options['download_uri'], $object, $form);
        }
        // required for BC
        // TODO: remove for 2.0
        $view->vars['show_download_link'] = !empty($view->vars['download_uri']);
    }

    public function getBlockPrefix(): string
    {
        return 'vich_image';
    }
}
