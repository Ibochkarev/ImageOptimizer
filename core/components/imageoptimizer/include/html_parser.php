<?php

defined('MODX_CORE_PATH') || exit;

/** Bump when inject serialize output format changes — invalidates HTML cache keys. */
const IMAGEOPTIMIZER_HTML_SERIALIZE_REV = '3';

/**
 * Pull script/style/noscript/textarea out before DOMDocument so markup inside
 * JS/CSS strings is not rewritten (can inject literal </script> and break pages).
 *
 * @return array{0: string, 1: array<string, string>}
 */
function imageoptimizer_extract_raw_blocks(string $html): array
{
    $blocks = [];
    $replaced = preg_replace_callback(
        '#<(script|style|noscript|textarea)\b[^>]*>.*?</\1>#is',
        static function (array $match) use (&$blocks): string {
            $token = '<!--IMAGEOPTIMIZER_RAW_' . count($blocks) . '-->';
            $blocks[$token] = $match[0];

            return $token;
        },
        $html
    );

    return [$replaced ?? $html, $blocks];
}

/**
 * @param array<string, string> $blocks
 */
function imageoptimizer_restore_raw_blocks(string $html, array $blocks): string
{
    if ($blocks === []) {
        return $html;
    }

    return str_replace(array_keys($blocks), array_values($blocks), $html);
}

/**
 * Full page document (has doctype or html root) vs HTML fragment.
 */
function imageoptimizer_is_full_html_document(string $html): bool
{
    $sample = ltrim(substr($html, 0, 2048));
    if ($sample === '') {
        return false;
    }

    return (bool) preg_match('/^(?:<!DOCTYPE\b|<html\b)/i', $sample);
}

/**
 * Remove loadHTML UTF-8 hint left as PI or <!--?xml ...--> comment.
 */
function imageoptimizer_strip_libxml_encoding_artifacts(DOMDocument $doc): void
{
    $remove = [];
    foreach ($doc->childNodes as $node) {
        if ($node instanceof DOMProcessingInstruction && strcasecmp($node->target, 'xml') === 0) {
            $remove[] = $node;
            continue;
        }
        if ($node instanceof DOMComment && preg_match('/^\?xml\b/i', ltrim($node->data))) {
            $remove[] = $node;
        }
    }
    foreach ($remove as $node) {
        if ($node->parentNode) {
            $node->parentNode->removeChild($node);
        }
    }
}

function imageoptimizer_load_html_document(string $html): ?DOMDocument
{
    $doc = new DOMDocument();
    $flags = LIBXML_NOERROR | LIBXML_NOWARNING;
    if (defined('LIBXML_HTML_NODEFDTD')) {
        $flags |= LIBXML_HTML_NODEFDTD;
    }

    if (imageoptimizer_is_full_html_document($html)) {
        // Keep <html>/<head>/<body>; do not wrap in #imageoptimizer-root.
        $payload = '<?xml encoding="UTF-8">' . $html;
        if (!$doc->loadHTML($payload, $flags)) {
            return null;
        }
        imageoptimizer_strip_libxml_encoding_artifacts($doc);

        return $doc;
    }

    if (defined('LIBXML_HTML_NOIMPLIED')) {
        $flags |= LIBXML_HTML_NOIMPLIED;
    }

    $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="imageoptimizer-root">'
        . $html . '</div></body></html>';
    if (!$doc->loadHTML($wrapped, $flags)) {
        return null;
    }

    return $doc;
}

/**
 * @return DOMElement[]
 */
function imageoptimizer_extract_img_nodes(DOMDocument $doc): array
{
    $images = [];
    $nodes = $doc->getElementsByTagName('img');
    foreach ($nodes as $node) {
        if ($node instanceof DOMElement) {
            $images[] = $node;
        }
    }

    return $images;
}

function imageoptimizer_serialize_document(DOMDocument $doc): string
{
    imageoptimizer_strip_libxml_encoding_artifacts($doc);

    $root = $doc->getElementById('imageoptimizer-root');
    if ($root) {
        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $doc->saveHTML($child);
        }

        return imageoptimizer_normalize_html_output($html);
    }

    return imageoptimizer_normalize_html_output((string) $doc->saveHTML());
}

function imageoptimizer_normalize_html_output(string $html): string
{
    // Belt-and-suspenders: strip PI / comment remnant from cached or odd libxml output.
    $html = preg_replace('/<\?xml\b[^?]*\?>\s*/i', '', $html) ?? $html;
    $html = preg_replace('/<!--\s*\?xml\b[\s\S]*?-->\s*/i', '', $html) ?? $html;

    $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    foreach ($voidTags as $tag) {
        $html = preg_replace('#(<' . $tag . '\b[^>]*>)\s*</' . $tag . '>#i', '$1', $html) ?? $html;
    }
    $html = preg_replace('#</source>\s*</picture>#i', '</picture>', $html) ?? $html;

    return $html;
}
