<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TermsAndConditionService
{
    public function getInvoiceTerms(?string $connection = null): array
    {
        $terms = $this->getTerms('invoice', $connection);

        return !empty($terms) ? $terms : $this->defaultInvoiceTerms();
    }

    public function getConfiguredInvoiceTerms(?string $connection = null): array
    {
        return $this->getTerms('invoice', $connection);
    }

    public function getQuotationTerms(?string $connection = null): array
    {
        return $this->getTerms('quotation', $connection);
    }

    private function getTerms(string $column, ?string $connection): array
    {
        if ($connection === null || in_array($connection, ['tenant', 'landlord'], true)) {
            $connection = (string) config('database.default', 'mysql');
        }

        if (!Schema::connection($connection)->hasTable('terms_and_conditions')
            || !Schema::connection($connection)->hasColumn('terms_and_conditions', $column)) {
            return [];
        }

        $text = trim((string) (DB::connection($connection)
            ->table('terms_and_conditions')
            ->orderBy('id')
            ->value($column) ?? ''));

        return $this->parseTerms($text);
    }

    private function parseTerms(string $text): array
    {
        if ($text === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $terms = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            $line = preg_replace('/^\s*(?:\d+[\.\)]|[-*])\s*/', '', $line) ?? $line;
            $line = trim($line);

            if ($line !== '') {
                $terms[] = $line;
            }
        }

        return $terms;
    }

    private function defaultInvoiceTerms(): array
    {
        return [
            'Goods once sold will not be taken back.',
            'Payment due as per agreed invoice terms.',
            'Subject to local jurisdiction only.',
        ];
    }
}
