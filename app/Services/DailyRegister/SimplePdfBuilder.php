<?php

namespace App\Services\DailyRegister;

class SimplePdfBuilder
{
    protected string $title;
    protected array $metadata = [];
    protected array $sections = [];

    public function __construct(string $title = 'Laporan Berita Acara Bank Sulteng')
    {
        $this->title = $title;
    }

    public static function make(string $title = 'Laporan Berita Acara Bank Sulteng'): self
    {
        return new self($title);
    }

    public function addMetadata(string $key, string $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function addSection(string $heading, array $headers, array $rows): self
    {
        $this->sections[] = [
            'heading' => $heading,
            'headers' => $headers,
            'rows'    => $rows,
        ];
        return $this;
    }

    /**
     * Render a 100% valid multi-page PDF 1.4 binary string
     */
    public function render(): string
    {
        $lines = [];
        $lines[] = 'PT BANK SULTENG - RESIDENT AUDITOR SYSTEM';
        $lines[] = strtoupper($this->title);
        $lines[] = str_repeat('=', 65);
        $lines[] = '';

        foreach ($this->metadata as $k => $v) {
            $lines[] = sprintf('%-25s: %s', $k, $v);
        }
        $lines[] = str_repeat('-', 65);
        $lines[] = '';

        foreach ($this->sections as $sec) {
            if (!empty($sec['heading'])) {
                $lines[] = '[' . strtoupper($sec['heading']) . ']';
                $lines[] = str_repeat('-', 50);
            }
            if (!empty($sec['headers'])) {
                $lines[] = implode(' | ', $sec['headers']);
                $lines[] = str_repeat('-', 50);
            }
            foreach ($sec['rows'] as $r) {
                if (is_array($r)) {
                    $lines[] = implode(' | ', $r);
                } else {
                    $lines[] = (string)$r;
                }
            }
            $lines[] = '';
        }

        $lines[] = str_repeat('=', 65);
        $lines[] = 'Dokumen ini sah dan diterbitkan secara digital oleh Sistem Resident Auditor PT Bank Sulteng.';
        $lines[] = 'Tanggal Cetak: ' . date('d/m/Y H:i:s');

        // Split lines into pages (max 52 lines per page)
        $linesPerPage = 52;
        $pagesOfLines = array_chunk($lines, $linesPerPage);
        if (empty($pagesOfLines)) {
            $pagesOfLines = [[]];
        }

        $numPages = count($pagesOfLines);
        $objects = [];
        $pageObjectIds = [];
        $nextObjId = 4;

        for ($p = 0; $p < $numPages; $p++) {
            $pageObjId = $nextObjId++;
            $contentObjId = $nextObjId++;
            $pageObjectIds[] = $pageObjId;

            $pLines = $pagesOfLines[$p];
            $stream = "BT\n/F1 9 Tf\n40 800 Td\n14 TL\n";
            foreach ($pLines as $line) {
                $cleanLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
                $cleanLine = preg_replace('/[^\x20-\x7E]/', ' ', $cleanLine);
                $stream .= "({$cleanLine}) '\n";
            }
            $stream .= "ET\n";
            $streamLen = strlen($stream);

            $objects[$pageObjId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObjId} 0 R >>";
            $objects[$contentObjId] = "<< /Length {$streamLen} >>\nstream\n{$stream}endstream";
        }

        $kidsStr = implode(' ', array_map(fn($id) => "{$id} 0 R", $pageObjectIds));

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [{$kidsStr}] /Count {$numPages} >>";
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $xref = [];
        $xref[0] = "0000000000 65535 f \n";

        foreach ($objects as $num => $obj) {
            $offset = strlen($pdf);
            $xref[$num] = sprintf("%010d 00000 n \n", $offset);
            $pdf .= "{$num} 0 obj\n{$obj}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        for ($i = 0; $i < $count; $i++) {
            $pdf .= $xref[$i];
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }
}
