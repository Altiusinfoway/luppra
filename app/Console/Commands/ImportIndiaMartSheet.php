<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleSheetImportController;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;

class ImportIndiaMartSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'india-mart-sheet:import';

    protected $description = 'Import india mart lead.';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(GoogleSheetImportController::class);

        try {
            $result = $controller->india_mart_import();

            if ($this->isFailedResult($result)) {
                $this->error('IndiaMart import failed.');
                return self::FAILURE;
            }

            $this->info('IndiaMart import completed.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error("IndiaMart import failed: {$e->getMessage()}");
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
