<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\File;

class ParseDocuments extends Command
{
    protected $signature = 'documents:parse {file?}';
    protected $description = 'Parse PDF documents, split into chunks and store in DB';

    public function handle()
    {
        $file = $this->argument('file');

        // Если файл не передан — парсим всё из storage/app/documents
        if (!$file) {
            $directory = storage_path('app/documents');
            $files = File::files($directory);

            if (count($files) === 0) {
                $this->error("Нет PDF файлов в /storage/app/documents/");
                return;
            }

            foreach ($files as $pdf) {
                $this->parseFile($pdf->getRealPath());
            }

            return;
        }

        // Если передали конкретный файл
        $path = storage_path('app/documents/' . $file);

        if (!file_exists($path)) {
            $this->error("Файл не найден: {$path}");
            return;
        }

        $this->parseFile($path);
    }

    private function parseFile(string $path)
    {
        $this->info("Парсим PDF: $path");

        $parser = new Parser();

        try {
            $pdf = $parser->parseFile($path);
        } catch (\Exception $e) {
            $this->error("Ошибка при чтении PDF: " . $e->getMessage());
            return;
        }

        $text = $pdf->getText();

        if (strlen($text) < 50) {
            $this->error("PDF пустой или текст не извлечён.");
            return;
        }

        $fileName = basename($path);

        // Создаём запись о документе, если нет
        $document = Document::firstOrCreate(
            ['file_path' => $fileName],
            [
                'title' => pathinfo($fileName, PATHINFO_FILENAME),
                'type'  => 'instruction',
                'description' => 'Автоматически импортированный документ'
            ]
        );

        // Удаляем старые chunks (если перезаписываем документ)
        DocumentChunk::where('document_id', $document->id)->delete();

        // Делим на chunks
        $chunks = $this->splitText($text, 2000);

        foreach ($chunks as $index => $chunkText) {
            DocumentChunk::create([
                'document_id' => $document->id,
                'chunk_index' => $index,
                'text' => $chunkText,
            ]);
        }

        $this->info("Готово! Всего чанков: " . count($chunks));
    }

    private function splitText(string $text, int $chunkSize): array
    {
        $clean = preg_replace('/\s+/', ' ', trim($text));
        return str_split($clean, $chunkSize);
    }
}
