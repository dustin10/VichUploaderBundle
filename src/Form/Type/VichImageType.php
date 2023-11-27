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
    public const STORAGE_RESOLVE_URI = 0;

    public const STORAGE_RESOLVE_PATH_ABSOLUTE = 1;

    public const STORAGE_RESOLVE_PATH_RELATIVE = 2;

    public function __construct(
        StorageInterface $storage,
        UploadHandler $handler,
        PropertyMappingFactory $factory,
        PropertyAccessorInterface $propertyAccessor = null,
        private readonly ?CacheManager $cacheManager = null
    ) {
        parent::__construct($storage, $handler, $factory, $propertyAccessor);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'image_uri' => true,
            'imagine_pattern' => null,
            'storage_resolve_method' => self::STORAGE_RESOLVE_URI,
            'attr' => [
                'accept' => 'image/*',
            ],
        ]);

        $resolver->setAllowedValues(
            'storage_resolve_method',
            [
                self::STORAGE_RESOLVE_URI,
                self::STORAGE_RESOLVE_PATH_RELATIVE,
                self::STORAGE_RESOLVE_PATH_ABSOLUTE,
            ]
        );

        $resolver->setAllowedTypes('image_uri', ['bool', 'string', 'callable']);

        $imageUriNormalizer = static fn (Options $options, $imageUri) => $imageUri ?? $options['download_uri'];

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
                $path = $this->resolvePath($options['storage_resolve_method'], $object, $form);
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
        $view->vars['asset_helper'] = $options['asset_helper'];
    }

    public function getBlockPrefix(): string
    {
        return 'vich_image';
    }

    private function resolvePath(int $storageResolveMethod, object $object, FormInterface $form): ?string
    {
        if (self::STORAGE_RESOLVE_URI === $storageResolveMethod) {
            return $this->storage->resolveUri($object, $this->getFieldName($form));
        }
        if (self::STORAGE_RESOLVE_PATH_ABSOLUTE === $storageResolveMethod) {
            return $this->storage->resolvePath($object, $this->getFieldName($form));
        }
        if (self::STORAGE_RESOLVE_PATH_RELATIVE === $storageResolveMethod) {
            return $this->storage->resolvePath($object, $this->getFieldName($form), null, true);
        }

        return null;
    }
}
