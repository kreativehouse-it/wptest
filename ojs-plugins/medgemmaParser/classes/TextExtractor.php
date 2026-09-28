<?php

/**
 * @file plugins/generic/medgemmaParser/classes/TextExtractor.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class TextExtractor
 *
 * @brief Extracts plain text from the manuscript formats authors usually
 *  upload: PDF, DOCX, ODT, plain text and HTML.
 */

namespace APP\plugins\generic\medgemmaParser\classes;

use Exception;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class TextExtractor
{
    public const SUPPORTED_MIMETYPES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'text/plain',
        'text/html',
    ];

    /**
     * @param string $contents Raw file contents
     */
    public function extract(string $contents, string $mimetype): string
    {
        $text = match ($mimetype) {
            'application/pdf' => $this->fromPdf($contents),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->fromZippedXml($contents, 'word/document.xml'),
            'application/vnd.oasis.opendocument.text' => $this->fromZippedXml($contents, 'content.xml'),
            'text/html' => html_entity_decode(strip_tags($contents), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'text/plain' => $contents,
            default => throw new Exception("Unsupported file type: {$mimetype}"),
        };

        $text = $this->normalize($text);
        if (mb_strlen($text) < 200) {
            throw new Exception('The manuscript contains too little text. It may be a scanned PDF without a text layer.');
        }
        return $text;
    }

    private function fromPdf(string $contents): string
    {
        // smalot/pdfparser ships in the plugin's own vendor directory.
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        return (new PdfParser())->parseContent($contents)->getText();
    }

    /**
     * DOCX and ODT are zip archives with the body in an XML file.
     */
    private function fromZippedXml(string $contents, string $entry): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'medgemma');
        try {
            file_put_contents($tmp, $contents);
            $zip = new ZipArchive();
            if ($zip->open($tmp) !== true) {
                throw new Exception('Unable to open the document archive.');
            }
            $xml = $zip->getFromName($entry);
            $zip->close();
        } finally {
            @unlink($tmp);
        }
        if ($xml === false) {
            throw new Exception("Document body {$entry} not found.");
        }

        // Turn paragraph, line break and tab elements into whitespace before stripping tags.
        $xml = preg_replace('#</(w:p|text:p|text:h)>#', "\n", $xml);
        $xml = preg_replace('#<(w:br|w:cr|text:line-break)[^>]*/>#', "\n", $xml);
        $xml = preg_replace('#<(w:tab|text:tab)[^>]*/>#', "\t", $xml);
        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function normalize(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }
        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace("/[ \t\x{00A0}]+/u", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }
}
