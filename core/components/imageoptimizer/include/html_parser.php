<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_load_html_document(string $html): ?DOMDocument
{
    $doc = new DOMDocument();
    $flags = LIBXML_NOERROR | LIBXML_NOWARNING;
    if (defined('LIBXML_HTML_NOIMPLIED')) {
        $flags |= LIBXML_HTML_NOIMPLIED;
    }
    if (defined('LIBXML_HTML_NODEFDTD')) {
        $flags |= LIBXML_HTML_NODEFDTD;
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
    $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    foreach ($voidTags as $tag) {
        $html = preg_replace('#(<' . $tag . '\b[^>]*>)\s*</' . $tag . '>#i', '$1', $html) ?? $html;
    }
    $html = preg_replace('#</source>\s*</picture>#i', '</picture>', $html) ?? $html;

    return $html;
}
