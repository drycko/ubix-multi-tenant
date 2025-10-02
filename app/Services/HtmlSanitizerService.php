<?php

namespace App\Services;

class HtmlSanitizerService
{
    /**
     * Allowed HTML tags for rich text content
     */
    protected array $allowedTags = [
        'p', 'br', 'strong', 'em', 'b', 'i', 'u', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a', 'img',
        'div', 'span'
    ];

    /**
     * Allowed attributes for HTML elements
     */
    protected array $allowedAttributes = [
        'a' => ['href', 'target', 'title', 'class'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class', 'style'],
        '*' => ['class', 'style', 'id']
    ];

    /**
     * Sanitize the given HTML string.
     */
    public function sanitize(?string $html): ?string
    {
        if (is_null($html) || empty(trim($html))) {
            return $html;
        }

        // Remove potentially dangerous elements and scripts
        $html = $this->removeDangerousElements($html);
        
        // Clean up the HTML using strip_tags with allowed tags
        $allowedTagsString = '<' . implode('><', $this->allowedTags) . '>';
        $html = strip_tags($html, $allowedTagsString);
        
        // Additional security cleaning
        $html = $this->cleanAttributes($html);
        
        return $html;
    }

    /**
     * Remove dangerous elements and attributes
     */
    protected function removeDangerousElements(string $html): string
    {
        // Remove script tags and their content
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        
        // Remove style tags and their content (but allow style attribute)
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
        
        // Remove dangerous event handlers
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        
        // Remove javascript: URLs
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);
        
        // Remove data: URLs (except for images in some cases)
        $html = preg_replace('/src\s*=\s*["\']data:(?!image\/)[^"\']*["\']/i', '', $html);
        
        return $html;
    }

    /**
     * Clean and validate attributes
     */
    protected function cleanAttributes(string $html): string
    {
        // This is a basic implementation
        // For more advanced attribute filtering, you might want to use DOMDocument
        
        // Remove any remaining dangerous attributes
        $dangerousAttrs = ['onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur'];
        
        foreach ($dangerousAttrs as $attr) {
            $html = preg_replace('/\s*' . $attr . '\s*=\s*["\'][^"\']*["\']/i', '', $html);
        }
        
        return $html;
    }

    /**
     * Sanitize for plain text output (strip all HTML)
     */
    public function sanitizeToPlainText(?string $html): ?string
    {
        if (is_null($html) || empty(trim($html))) {
            return $html;
        }

        // Strip all HTML tags
        $text = strip_tags($html);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }

    /**
     * Sanitize for safe HTML output with minimal tags
     */
    public function sanitizeBasic(?string $html): ?string
    {
        if (is_null($html) || empty(trim($html))) {
            return $html;
        }

        // Only allow very basic formatting
        $basicTags = '<p><br><strong><em><b><i>';
        $html = strip_tags($html, $basicTags);
        
        return $this->removeDangerousElements($html);
    }
}