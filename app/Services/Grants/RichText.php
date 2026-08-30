<?php

namespace App\Services\Grants;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class RichText
{
    public function fromImport(?string $content): string
    {
        // Conversion belongs at the import boundary: never reinterpret literal
        // Markdown in existing records or HTML produced by the rich-text editor.
        $converter = new CommonMarkConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'max_delimiters_per_line' => 1000,
            'renderer' => ['soft_break' => "<br />\n"],
        ]);

        // Raw HTML is allowed only into the converter, never past our allowlist.
        return trim($this->sanitize((string) $converter->convert($content ?? '')));
    }

    public function sanitize(?string $content): string
    {
        $content = trim($content ?? '');
        if ($content === '') {
            return '';
        }
        // Preserve paragraphs in older, plain-text opportunity records.
        if ($content === strip_tags($content)) {
            $content = '<p>'.nl2br(e(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'))).'</p>';
        }
        $config = (new HtmlSanitizerConfig)->withMaxInputLength(250000);
        foreach (['p', 'br', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'li', 'blockquote'] as $tag) {
            $config = $config->allowElement($tag);
        }
        $config = $config->allowElement('ol', ['start'])
            ->allowElement('a', ['href', 'title'])
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->forceAttribute('a', 'rel', 'nofollow noopener noreferrer');

        $html = (new HtmlSanitizer($config))->sanitize($content);

        return $this->text($html) === '' ? '' : $html;
    }

    public function text(string $html): string
    {
        return trim(str_replace("\u{00A0}", ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
