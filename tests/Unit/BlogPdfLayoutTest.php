<?php

namespace Tests\Unit;

use App\Models\BlogAuthor;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\BlogPdf;
use Tests\TestCase;

class BlogPdfLayoutTest extends TestCase
{
    public function test_multipage_article_keeps_text_below_dividers_and_above_footers(): void
    {
        $post = $this->article();
        $pdf = BlogPdf::render($post);
        preg_match_all('/stream\n(.*?)endstream/s', $pdf, $streams);
        $this->assertGreaterThan(1, count($streams[1]));
        foreach ($streams[1] as $pageIndex => $stream) {
            preg_match_all('/BT 54 ([\d.]+) Td\n(.*?)ET/s', $stream, $textLines, PREG_SET_ORDER);
            $this->assertNotEmpty($textLines);
            foreach ($textLines as $line) {
                preg_match_all('/\/F\d (\d+) Tf/', $line[2], $sizes);
                if ($sizes[1] === []) {
                    continue;
                }
                $largestFont = max(array_map('intval', $sizes[1]));
                $baseline = (float) $line[1];
                $this->assertLessThanOrEqual($pageIndex === 0 ? 660 : 728, $baseline + $largestFont);
                $this->assertGreaterThanOrEqual(54, $baseline);
            }
            $last = end($textLines);
            preg_match_all('/\/F\d (\d+) Tf/', $last[2], $lastSizes);
            $this->assertLessThanOrEqual(11, max(array_map('intval', $lastSizes[1])), 'A heading must stay with the following paragraph.');
        }
    }

    public function test_words_share_the_pdf_text_cursor_and_keep_spaces(): void
    {
        $post = $this->article();
        $post->content = '<h2>Make it a habit</h2><p>Choose <strong>one improvement</strong>, put it into practice, and keep learning.</p>';
        $pdf = BlogPdf::render($post);
        $this->assertStringContainsString('(Make) Tj', $pdf);
        $this->assertStringContainsString('( ) Tj', $pdf);
        $this->assertStringContainsString('/F2 11 Tf (improvement) Tj', $pdf);
        $this->assertStringNotContainsString('BT /F2 16 Tf', $pdf);
    }

    private function article(): BlogPost
    {
        $post = new BlogPost(['title' => 'Fresh Design for a Faster Workflow', 'content' => str_repeat('<h2>Make it a habit</h2><p>Choose one improvement, put it into practice, and keep a steady rhythm of listening, testing, and refining ideas.</p>', 60)]);
        $post->setRelation('category', new BlogCategory(['name' => 'News']));
        $post->setRelation('authorProfile', new BlogAuthor(['name' => 'Editorial Team']));

        return $post;
    }
}
