<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleSheetImportController;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;
use App\Services\GoogleSheetService;

class ImportGoogleSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-sheet:import';

    protected $description = 'Import leads from Google Sheet';

    public function handle(GoogleSheetService $sheet)
    {
        $controller = app(GoogleSheetImportController::class);

        try {
            $result = $controller->import($sheet);

            if ($this->isFailedResult($result)) {
                $this->error('Google Sheet import failed.');
                return self::FAILURE;
            }

            $this->info('Google Sheet import completed.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error("Google Sheet import failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function isFailedResult($result): bool
    {
        if ($result === false) {
            return true;
        }

        if ($result instanceof Response) {
            return $result->getStatusCode() >= 400;
        }

        return false;
    }
}
