<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerService
{
    protected HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig())
            // Allow basic text formatting and structural tags
            ->allowElement('b')
            ->allowElement('br')
            ->allowElement('em')
            ->allowElement('h1')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('h5')
            ->allowElement('h6')
            ->allowElement('i')
            ->allowElement('li')
            ->allowElement('ol')
            ->allowElement('p')
            ->allowElement('strong')
            ->allowElement('ul')
            ->allowElement('div')
            ->allowElement('span')
            
            // Allow useful attributes for the allowed tags
            ->allowAttribute('class', ['*']) // Allow 'class' on all allowed elements
            ->allowAttribute('style', ['*']) // Allow 'style' on all allowed elements (use cautiously!)
            
            // Configure links: allow specific schemes and attributes
            ->allowElement('a', ['href', 'target', 'title', 'class']) // Allow <a> with these attributes
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel']) // Allowed URL schemes for links
            ->forceHttpsUrls(false) // Set to true to convert HTTP to HTTPS
            
            // Configure images: allow with specific attributes and schemes
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'class', 'style'])
            ->allowMediaSchemes(['http', 'https']) // Allowed URL schemes for images
            ->allowRelativeLinks(false) // Set to true if you want to allow relative URLs
            ;

        $this->sanitizer = new HtmlSanitizer($config);
    }

    /**
     * Sanitize the given HTML string.
     */
    public function sanitize(?string $html): ?string
    {
        if (is_null($html) || empty(trim($html))) {
            return $html;
        }

        return $this->sanitizer->sanitize($html);
    }
}