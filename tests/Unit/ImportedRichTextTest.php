<?php

namespace Tests\Unit;

use App\Services\Grants\RichText;
use PHPUnit\Framework\TestCase;

class ImportedRichTextTest extends TestCase
{
    public function test_markdown_and_html_produce_equivalent_supported_formatting(): void
    {
        $richText = new RichText;
        $markdown = "## Eligibility\n\nFor **nonprofits** with *local experience*.\n\n- First item\n- Second item\n\n### Apply\n\n1. Prepare\n2. Submit\n\n> A helpful quote\n\n[Programme guide](https://example.org)";
        $html = '<h2>Eligibility</h2><p>For <strong>nonprofits</strong> with <em>local experience</em>.</p><ul><li>First item</li><li>Second item</li></ul><h3>Apply</h3><ol><li>Prepare</li><li>Submit</li></ol><blockquote><p>A helpful quote</p></blockquote><p><a href="https://example.org">Programme guide</a></p>';
        $normalize = fn (string $value): string => preg_replace('/>\s+</', '><', trim($value));
        $this->assertSame($normalize($richText->fromImport($html)), $normalize($richText->fromImport($markdown)));
    }

    public function test_mixed_content_is_sanitized_and_remains_stable_in_the_editor(): void
    {
        $richText = new RichText;
        $html = $richText->fromImport("## Heading\n\n**Safe** and <em>mixed HTML</em>.\n\n<script>alert(1)</script>\n\n[Unsafe](javascript:alert%281%29)\n\n<a href=\"javascript:alert(1)\" onclick=\"alert(1)\">Unsafe HTML</a>\n\n<img src=x onerror=alert(1)>");
        $this->assertStringContainsString('<h2>Heading</h2>', $html);
        $this->assertStringContainsString('<strong>Safe</strong> and <em>mixed HTML</em>', $html);
        foreach (['<script', '<img', 'javascript:', 'onclick', 'onerror'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $html);
        }
        $this->assertSame($html, $richText->sanitize($html));
        $this->assertSame('<p>Literal **asterisks**</p>', $richText->sanitize('<p>Literal **asterisks**</p>'));
    }

    public function test_empty_content_and_plain_text_line_breaks_still_work(): void
    {
        $richText = new RichText;
        $this->assertSame('', $richText->fromImport(null));
        $this->assertSame('', $richText->fromImport(''));
        $this->assertStringContainsString("First<br />\nSecond &amp; third", $richText->fromImport("First\nSecond & third"));
    }
}
