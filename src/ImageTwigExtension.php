<?php

namespace Massive\Component\Web;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * This Twig Extension manages the image formats.
 */
class ImageTwigExtension extends \Twig_Extension
{
    /**
     * @var string|null
     */
    protected $placeholderPath;

    /**
     * @var bool
     */
    protected $hasLazyImage = false;

    /**
     * @var string[]
     */
    private $placeholders = null;

    public function __construct($placeholderPath = null)
    {
        if (null !== $placeholderPath) {
            $this->placeholderPath = rtrim($placeholderPath, '/') . '/';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_image', [$this, 'getImage'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_lazy_image', [$this, 'getLazyImage'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('has_lazy_image', [$this, 'hasLazyImage']),
        ];
    }

    /**
     * Get an image or picture tag with given attributes for lazy loading.
     *
     * @param mixed $media
     * @param string[]|string $attributes
     * @param string[] $sources
     *
     * @return string
     */
    public function getLazyImage($media, $attributes = [], array $sources = [])
    {
        if (null === $this->placeholderPath) {
            throw new \InvalidArgumentException(
                'You need to define placeholderPaths constructor argument to use the get_lazy_image function'
            );
        }

        if (is_array($media)) {
            $media = (object) $media;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $thumbnails = $propertyAccessor->getValue($media, 'thumbnails');
        $this->hasLazyImage = true;

        return $this->createImage($media, $attributes, $sources, $this->getLazyThumbnails($thumbnails));
    }

    /**
     * Get lazy image was called once or more times.
     *
     * @return bool
     */
    public function hasLazyImage()
    {
        return $this->hasLazyImage;
    }

    /**
     * Get an image or picture tag with given attributes.
     *
     * @param mixed $media
     * @param string[]|string $attributes
     * @param string[] $sources
     *
     * @return string
     */
    public function getImage($media, $attributes = [], array $sources = [])
    {
        return $this->createImage($media, $attributes, $sources);
    }

    /**
     * Get an image or picture tag with given attributes.
     *
     * @param mixed $media
     * @param array|string $attributes
     * @param array $sources
     * @param null|string[] $lazyThumbnails
     *
     * @return string
     */
    private function createImage(
        $media,
        $attributes = [],
        array $sources = [],
        $lazyThumbnails = null
    ) {
        // Return an empty string if no one of the needed parameters is set.
        if (empty($media) || empty($attributes)) {
            return '';
        }

        if (is_array($media)) {
            $media = (object) $media;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        // Thumbnails exists all times - it only can be empty.
        $thumbnails = $propertyAccessor->getValue($media, 'thumbnails');

        // If attributes is an string, convert it to an array (like '{{ get_image_tag(media, '650x') }}').
        if (is_string($attributes)) {
            $attributes = [
                'src' => $attributes,
            ];
        }

        if ($lazyThumbnails) {
            $attributes['class'] = trim((isset($attributes['class']) ? $attributes['class'] : '') . ' lazyload');
        }

        // Get title from object to use as alt attribute.
        $alt = $propertyAccessor->getValue($media, 'title');

        // Get description from object to use as title attribute else fallback to alt attribute.
        $title = $propertyAccessor->getValue($media, 'description') ?: $alt;

        // Get the image tag with all given attributes.
        $imgTag = $this->createTag(
            'img',
            array_merge(['alt' => $alt, 'title' => $title], $attributes),
            $thumbnails,
            $lazyThumbnails
        );

        if (empty($sources)) {
            return $imgTag;
        }

        $sourceTags = '';
        foreach ($sources as $media => $sourceAttributes) {
            if (is_string($sourceAttributes)) {
                $sourceAttributes = [
                    'srcset' => $sourceAttributes,
                ];
            }
            // Get the source tag with all given attributes.
            $sourceTags .= $this->createTag(
                'source',
                array_merge(['media' => $media], $sourceAttributes),
                $thumbnails,
                $lazyThumbnails
            );
        }

        // Returns the picture tag with all sources and the fallback image tag.
        return sprintf('<picture>%s%s</picture>', $sourceTags, $imgTag);
    }

    /**
     * Create html tag.
     *
     * @param string $tag
     * @param string[] $attributes
     * @param string[] $thumbnails
     * @param null|string[] $lazyThumbnails
     *
     * @return string
     */
    private function createTag($tag, $attributes, $thumbnails, $lazyThumbnails = null)
    {
        $output = '';

        foreach ($attributes as $key => $value) {
            if ('src' === $key) {
                if ($lazyThumbnails) {
                    $output .= sprintf(' %s="%s"', $key, $lazyThumbnails[$value]);
                    $key = 'data-src';
                }

                // Set the thumbnail instead of image itself.
                $value = $thumbnails[$value];
            } elseif ('srcset' === $key) {
                if ($lazyThumbnails) {
                    $output .= sprintf(
                        ' %s="%s"',
                        $key,
                        htmlentities($this->srcsetThumbnailReplace($value, $lazyThumbnails))
                    );
                    $key = 'data-srcset';
                }

                // Replace thumbnail format in srcset.
                $value = $this->srcsetThumbnailReplace($value, $thumbnails);
            }

            $output .= sprintf(' %s="%s"', $key, htmlentities($value));
        }

        return sprintf('<%s%s>', $tag, $output);
    }

    /**
     * Replace the given image format with an thumbnail.
     *
     * @param string $value
     * @param string[] $thumbnails
     *
     * @return string
     */
    private function srcsetThumbnailReplace($value, $thumbnails)
    {
        // Split string to an array (to get each srcset).
        $srcSets = explode(',', $value);

        $newSrcSets = [];
        foreach ($srcSets as $srcSet) {
            // Split the values of an srcset to an array to get the thumbnail.
            $values = explode(' ', trim($srcSet), 2);

            // Set the thumbnail.
            $newSrcSet = $thumbnails[$values[0]];

            // Merge the thumbnail again with the width from srcset.
            if (isset($values[1])) {
                $newSrcSet .= ' ' . $values[1];
            }

            $newSrcSets[] = $newSrcSet;
        }

        return implode(', ', $newSrcSets);
    }

    /**
     * Get lazy thumbnails.
     *
     * @param string[] $thumbnails
     *
     * @return string[]|null
     */
    private function getLazyThumbnails($thumbnails)
    {
        if (empty($thumbnails)) {
            return null;
        }

        if (null === $this->placeholders) {
            $placeholders = [];
            foreach (array_keys($thumbnails) as $key) {
                $placeholders[$key] = $this->placeholderPath . $key . '.svg';
            }

            $this->placeholders = $placeholders;
        }

        return $this->placeholders;
    }
}
