<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class BlogHtml
{
    // Rebuild allowed markup instead of trusting pasted HTML or attributes.
    public static function clean(string $html): string
    {
        $source = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $source->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
            $target = new DOMDocument('1.0', 'UTF-8');
            $root = $target->createElement('div');
            $target->appendChild($root);
            $body = $source->getElementsByTagName('body')->item(0);
            if ($body) {
                foreach ($body->childNodes as $node) {
                    self::copy($node, $root, $target);
                }
            }
            $result = '';
            foreach ($root->childNodes as $node) {
                $result .= $target->saveHTML($node);
            }

            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function copy(DOMNode $node, DOMNode $parent, DOMDocument $document): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $parent->appendChild($document->createTextNode($node->nodeValue));

            return;
        }
        if (! $node instanceof DOMElement) {
            return;
        }
        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form', 'input', 'textarea', 'button', 'select', 'template'], true)) {
            return;
        }
        $destination = $parent;
        if (in_array($tag, ['p', 'div', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'a', 'hr', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td'], true)) {
            $destination = $document->createElement($tag);
            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                if (preg_match('~^https?://~i', $href) && filter_var($href, FILTER_VALIDATE_URL)) {
                    $destination->setAttribute('href', $href);
                    $destination->setAttribute('rel', 'noopener noreferrer');
                } elseif (preg_match('~^/(?!/)~', $href) && ! preg_match('~[\\\\\x00-\x20]~', $href)) {
                    $destination->setAttribute('href', $href);
                }
            }
            $parent->appendChild($destination);
        }
        foreach ($node->childNodes as $child) {
            self::copy($child, $destination, $document);
        }
    }
}
