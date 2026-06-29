<?php

defined('MODX_CORE_PATH') || exit;

final class ImageOptimizerImgSkipRules
{
    public function __construct(private modX $modx)
    {
    }

    public function shouldSkip(DOMElement $img): ?SkipReason
    {
        if ($this->hasMetaParent($img)) {
            return SkipReason::MetaParent;
        }
        if ($this->hasDataSkip($img)) {
            return SkipReason::DataSkip;
        }
        if ($this->hasSkipClass($img)) {
            return SkipReason::SkipClass;
        }
        if ($this->matchesSrcPattern($img)) {
            return SkipReason::SrcPattern;
        }
        if ($this->isInsidePicture($img)) {
            return SkipReason::ParentIsPicture;
        }
        if ($this->hasExistingSrcset($img)) {
            return SkipReason::HasSrcset;
        }

        return null;
    }

    public function applyLazyAttributes(DOMElement $img, int $imgIndex): void
    {
        if (!$img->hasAttribute('decoding')) {
            $img->setAttribute('decoding', 'async');
        }

        $respectLoading = (bool) imageoptimizer_get_setting($this->modx, 'respect_existing_loading', true);
        $skipLazyFirst = max(0, (int) imageoptimizer_get_setting($this->modx, 'skip_lazy_first_images', 0));
        if (!$respectLoading || !$img->hasAttribute('loading')) {
            $loading = ($skipLazyFirst > 0 && $imgIndex < $skipLazyFirst) ? 'eager' : 'lazy';
            $img->setAttribute('loading', $loading);
        }

        $fetchPriority = trim($img->getAttribute('data-imageoptimizer-fetchpriority'));
        if ($fetchPriority !== '' && !$img->hasAttribute('fetchpriority')) {
            $img->setAttribute('fetchpriority', $fetchPriority);
        }
    }

    private function hasMetaParent(DOMElement $img): bool
    {
        $parent = $img->parentNode;
        while ($parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === 'meta') {
                return true;
            }
            $parent = $parent->parentNode;
        }

        return false;
    }

    private function hasDataSkip(DOMElement $img): bool
    {
        return $img->hasAttribute('data-imageoptimizer-skip');
    }

    private function hasSkipClass(DOMElement $img): bool
    {
        $classAttr = trim($img->getAttribute('class'));
        if ($classAttr === '') {
            return false;
        }
        $skipClasses = array_map(
            'trim',
            explode(',', (string) imageoptimizer_get_setting($this->modx, 'skip_classes', 'lazy,swiper-lazy,no-optim'))
        );
        $classes = preg_split('/\s+/', $classAttr) ?: [];
        foreach ($classes as $class) {
            if (in_array($class, $skipClasses, true)) {
                return true;
            }
        }

        return false;
    }

    private function matchesSrcPattern(DOMElement $img): bool
    {
        $pattern = trim((string) imageoptimizer_get_setting($this->modx, 'skip_src_pattern', 'thumb3x'));
        if ($pattern === '') {
            return false;
        }
        $src = $img->getAttribute('src');
        if ($src === '') {
            $src = $img->getAttribute('data-src');
        }

        return $src !== '' && stripos($src, $pattern) !== false;
    }

    private function isInsidePicture(DOMElement $img): bool
    {
        if (!(bool) imageoptimizer_get_setting($this->modx, 'respect_existing_picture', true)) {
            return false;
        }
        $parent = $img->parentNode;
        while ($parent instanceof DOMElement) {
            if (strtolower($parent->tagName) === 'picture') {
                return true;
            }
            $parent = $parent->parentNode;
        }

        return false;
    }

    private function hasExistingSrcset(DOMElement $img): bool
    {
        if (!(bool) imageoptimizer_get_setting($this->modx, 'respect_existing_srcset', true)) {
            return false;
        }

        return trim($img->getAttribute('srcset')) !== '';
    }
}
