<?php

namespace App\Support;

use App\Models\BlogPost;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class BlogPdf
{
    private const PAGE_WIDTH = 612;

    public static function render(BlogPost $post): string
    {
        $bodyLines = self::formattedLines($post->content);
        $image = self::jpegImage($post->article_image_path ?: $post->image_path);
        $firstPageTop = $image ? 400 : 660;
        $continuationTop = 728;
        $chunks = self::paginateLines($bodyLines, $firstPageTop, $continuationTop);

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>',
            6 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-BoldOblique /Encoding /WinAnsiEncoding >>',
        ];
        $nextObjectId = 7;
        $imageObjectId = null;
        if ($image) {
            $imageObjectId = $nextObjectId++;
            $objects[$imageObjectId] = '<< /Type /XObject /Subtype /Image /Width '.$image['width'].' /Height '.$image['height'].' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '.strlen($image['data']).">>\nstream\n".$image['data']."\nendstream";
        }

        $pageIds = [];
        foreach ($chunks as $index => $lines) {
            $pageId = $nextObjectId++;
            $contentId = $nextObjectId++;
            $pageIds[] = $pageId.' 0 R';
            $commands = self::pageHeader($post, $index === 0, $image, $imageObjectId);
            $top = $index === 0 ? $firstPageTop : $continuationTop;
            foreach ($lines as $line) {
                if ($line === []) {
                    $top -= self::lineHeight($line);

                    continue;
                }
                $size = self::lineSize($line);
                $y = $top - $size;
                $commands .= 'BT 54 '.$y." Td\n";
                foreach ($line as $run) {
                    if ($run['text'] === '') {
                        continue;
                    }
                    // A single text object lets the PDF font advance each run
                    // accurately, including spaces and mixed bold/italic text.
                    $commands .= '/F'.$run['font'].' '.$run['size'].' Tf ('.self::pdfText($run['text']).") Tj\n";
                }
                $commands .= "ET\n";
                $top -= self::lineHeight($line);
            }
            $commands .= 'BT /F1 9 Tf 54 30 Td ('.self::pdfText('CMS Blog  |  Page '.($index + 1).' of '.count($chunks)).") Tj ET\n";

            $xObjects = $imageObjectId && $index === 0 ? ' /XObject << /Im1 '.$imageObjectId.' 0 R >>' : '';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::PAGE_WIDTH.' 792] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R /F4 6 0 R >>'.$xObjects.' >> /Contents '.$contentId.' 0 R >>';
            $objects[$contentId] = '<< /Length '.strlen($commands).">>\nstream\n".$commands.'endstream';
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageIds).'] /Count '.count($pageIds).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n% CMS Blog PDF\n";
        $offsets = [0 => 0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }
        $xrefOffset = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private static function lineSize(array $line): int
    {
        return $line === [] ? 0 : max(array_column($line, 'size'));
    }

    private static function lineHeight(array $line): float
    {
        return $line === [] ? 8 : max(16, self::lineSize($line) * 1.4);
    }

    private static function paginateLines(array $lines, float $firstTop, float $continuationTop): array
    {
        $pages = [[]];
        $top = $firstTop;
        foreach ($lines as $index => $line) {
            $pageIndex = array_key_last($pages);
            if ($line === [] && $pages[$pageIndex] === []) {
                continue;
            }
            $required = self::lineHeight($line);
            // Keep a heading with the next nonempty line, instead of leaving
            // the heading alone at the bottom of a page.
            if (self::lineSize($line) > 11) {
                for ($next = $index + 1; $next < count($lines); $next++) {
                    $required += self::lineHeight($lines[$next]);
                    if ($lines[$next] !== []) {
                        break;
                    }
                }
            }
            if ($top - $required < 54 && $pages[$pageIndex] !== []) {
                $pages[] = [];
                $pageIndex++;
                $top = $continuationTop;
                if ($line === []) {
                    continue;
                }
            }
            $pages[$pageIndex][] = $line;
            $top -= self::lineHeight($line);
        }

        return $pages;
    }

    private static function pageHeader(BlogPost $post, bool $firstPage, ?array $image, ?int $imageObjectId): string
    {
        $commands = '';
        if ($firstPage) {
            $titleLines = explode("\n", wordwrap($post->title, 48, "\n", true));
            $y = 754;
            foreach (array_slice($titleLines, 0, 2) as $line) {
                $commands .= 'BT /F2 22 Tf 54 '.$y.' Td ('.self::pdfText($line).") Tj ET\n";
                $y -= 27;
            }
            $commands .= 'BT /F1 10 Tf 54 '.($y - 1).' Td ('.self::pdfText($post->category->name.'  |  '.$post->author.'  |  '.($post->published_at?->format('F j, Y') ?? 'Draft')).") Tj ET\n";
            $commands .= "0.65 w 54 680 m 558 680 l S\n";
            if ($image && $imageObjectId) {
                $scale = min(504 / $image['width'], 240 / $image['height']);
                $width = $image['width'] * $scale;
                $height = $image['height'] * $scale;
                $x = (self::PAGE_WIDTH - $width) / 2;
                $commands .= 'q '.$width.' 0 0 '.$height.' '.$x.' 418 cm /Im1 Do Q'."\n";
            }
        } else {
            $commands .= 'BT /F2 11 Tf 54 758 Td ('.self::pdfText($post->title).") Tj ET\n0.65 w 54 744 m 558 744 l S\n";
        }

        return $commands;
    }

    /** @return list<list<array{text:string,font:int,size:int}>> */
    private static function formattedLines(string $html): array
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div>'.BlogHtml::clean($html).'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $runs = [];
        if ($document->documentElement) {
            self::collectRuns($document->documentElement, false, false, 11, $runs);
        }
        $lines = [[]];
        $lineWidth = 0.0;
        foreach ($runs as $run) {
            foreach (preg_split('/(\s+)/u', $run['text'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [] as $piece) {
                if (str_contains($piece, "\n")) {
                    $count = substr_count($piece, "\n");
                    for ($i = 0; $i < $count; $i++) {
                        $lines[] = [];
                        $lineWidth = 0.0;
                    }

                    continue;
                }
                $pieceWidth = strlen(self::toWinAnsi($piece)) * $run['size'] * 0.5;
                if (trim($piece) !== '' && $lineWidth + $pieceWidth > 504 && $lines[array_key_last($lines)] !== []) {
                    $lines[] = [];
                    $lineWidth = 0.0;
                }
                if (trim($piece) === '' && $lines[array_key_last($lines)] === []) {
                    continue;
                }
                $lines[array_key_last($lines)][] = [...$run, 'text' => $piece];
                $lineWidth += $pieceWidth;
            }
        }
        while ($lines && end($lines) === []) {
            array_pop($lines);
        }

        return $lines ?: [[['text' => 'No article content.', 'font' => 1, 'size' => 11]]];
    }

    /** @param list<array{text:string,font:int,size:int}> $runs */
    private static function collectRuns(DOMNode $node, bool $bold, bool $italic, int $size, array &$runs): void
    {
        if ($node instanceof DOMText) {
            $text = preg_replace('/\s+/u', ' ', $node->nodeValue ?? '') ?? '';
            if ($text !== '') {
                $runs[] = ['text' => $text, 'font' => $bold ? ($italic ? 4 : 2) : ($italic ? 3 : 1), 'size' => $size];
            }

            return;
        }
        if (! $node instanceof DOMElement) {
            foreach ($node->childNodes as $child) {
                self::collectRuns($child, $bold, $italic, $size, $runs);
            }

            return;
        }

        $tag = strtolower($node->tagName);
        if ($tag === 'br') {
            $runs[] = ['text' => "\n", 'font' => 1, 'size' => 11];

            return;
        }
        $isHeading = in_array($tag, ['h1', 'h2', 'h3', 'h4'], true);
        $isBlock = $isHeading || in_array($tag, ['p', 'div', 'ul', 'ol', 'li', 'blockquote'], true);
        if ($isBlock && $runs !== []) {
            $runs[] = ['text' => "\n", 'font' => 1, 'size' => 11];
        }
        $nextBold = $bold || $isHeading || in_array($tag, ['strong', 'b'], true);
        $nextItalic = $italic || in_array($tag, ['em', 'i'], true);
        $nextSize = $tag === 'h1' || $tag === 'h2' ? 16 : ($tag === 'h3' || $tag === 'h4' ? 13 : $size);
        if ($tag === 'li') {
            $runs[] = ['text' => '• ', 'font' => 1, 'size' => 11];
        }
        foreach ($node->childNodes as $child) {
            self::collectRuns($child, $nextBold, $nextItalic, $nextSize, $runs);
        }
        if ($isBlock) {
            $runs[] = ['text' => "\n", 'font' => 1, 'size' => 11];
        }
    }

    private static function jpegImage(?string $path): ?array
    {
        if (! $path || ! str_starts_with($path, 'uploads/blogs/') || str_contains($path, '..')) {
            return null;
        }
        $file = public_path($path);
        if (! is_file($file)) {
            return null;
        }
        $raw = file_get_contents($file);
        if ($raw === false) {
            return null;
        }
        $dimensions = @getimagesizefromstring($raw);
        if (! $dimensions) {
            return null;
        }

        if (($dimensions['mime'] ?? '') === 'image/jpeg' && ! function_exists('imagecreatefromstring')) {
            return ['data' => $raw, 'width' => $dimensions[0], 'height' => $dimensions[1]];
        }
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }
        $source = @imagecreatefromstring($raw);
        if (! $source) {
            return null;
        }
        $rgb = imagecreatetruecolor(imagesx($source), imagesy($source));
        $background = imagecolorallocate($rgb, 255, 255, 255);
        imagefill($rgb, 0, 0, $background);
        imagecopy($rgb, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
        ob_start();
        imagejpeg($rgb, null, 88);
        $jpeg = ob_get_clean();
        imagedestroy($source);
        imagedestroy($rgb);

        return $jpeg ? ['data' => $jpeg, 'width' => $dimensions[0], 'height' => $dimensions[1]] : null;
    }

    private static function toWinAnsi(string $value): string
    {
        return iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) ?: '';
    }

    private static function pdfText(string $value): string
    {
        $value = self::toWinAnsi($value);
        $value = preg_replace('/[\x00-\x1F\x7F]/', ' ', $value) ?? $value;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
