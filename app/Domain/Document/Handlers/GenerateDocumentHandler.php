<?php

namespace App\Domain\Document\Handlers;

use App\Domain\Document\Commands\GenerateDocument;
use App\Domain\Document\Events\DocumentGenerated;
use App\Domain\Document\Models\Document;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;
use Illuminate\Support\Str;

class GenerateDocumentHandler implements CommandHandler
{
    /**
     * @param GenerateDocument $command
     */
    public function handle(Command $command): Document
    {
        // Simulation of document generation
        $fileName = strtoupper($command->documentType) . '-' . Str::random(12) . '.pdf';
        $filePath = 'documents/' . date('Y/m/d/') . $fileName;

        $document = Document::create([
            'documentable_type' => $command->documentableType,
            'documentable_id' => $command->documentableId,
            'document_type' => $command->documentType,
            'template_id' => $command->templateId,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        event(new DocumentGenerated($document));

        return $document;
    }
}
