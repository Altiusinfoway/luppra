<?php

namespace App\Exports;

use App\Models\Products;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductInventoryExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly int $creatorId)
    {
    }

    public function collection(): Collection
    {
        $query = Products::query()
            ->where('created_by', $this->creatorId)
            ->with(['getCategory', 'getGstSlabMaster', 'getUnitType', 'getUnit'])
            ->orderByDesc('id');

        if (Schema::hasTable('marketplace_listings')) {
            $query->withCount('marketplaceListings');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Category',
            'SKU Code',
            'HSN Code',
            'MRP',
            'GST Rate',
            'Stock Qty',
            'Total Valuation (MRP)',
            'Marketplace Listings',
            'Unit Type',
            'Unit',
            'Created At',
        ];
    }

    public function map($product): array
    {
        $mrp = (float) ($product->price ?? 0);
        $stock = (float) ($product->stock_qty ?? 0);

        return [
            $product->id,
            $product->name,
            $product->getCategory?->name ?? '',
            $product->sku_code,
            $product->hsn_code ?? '',
            $mrp,
            (float) ($product?->getGstSlabMaster?->rate ?? 0),
            $stock,
            $mrp * $stock,
            (int) ($product->marketplace_listings_count ?? 0),
            $product->getUnitType?->name ?? '',
            $product->getUnit?->name ?? '',
            optional($product->created_at)->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
