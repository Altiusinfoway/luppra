<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetService
{
    protected $service;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName('Laravel Google Sheet Import');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(storage_path('app/google/stellera-8ba415296530.json'));

        $this->service = new Sheets($client);
    }

    public function getSheetData($spreadsheetId, $range)
    {
        $response = $this->service->spreadsheets_values->get(
            $spreadsheetId,
            $range
        );

        return $response->getValues();
    }
}
