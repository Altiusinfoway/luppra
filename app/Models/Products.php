<?php

namespace App\Models;

use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Products extends Model
{
    use UsesTenantConnection;


    protected $fillable = [
        'category_id',
        'name',
        'image',
        'sku_code',
        'price',
        'dealer_price',
        'created_by',
        'type',
        'unit_type',
        'unit',
        'type_id',
        'min_qty',
        'dealer_price',
        'created_by',
        'is_active',
        'delete_status',
        'hsn_code',
        'gst_slab_master_id',
        'stock_qty',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopeInHouse($query)
    {
        return $query->where('type','inhouse')->where('is_active',1);
    }

    public function scopeVendor($query)
    {
        return $query->where('type', 'vendor');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

     public function getImageAttribute($value)
    {
        if (empty($value)) {
            return Utility::defaultImage();
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $filename = basename((string) parse_url($value, PHP_URL_PATH));

            if (!empty($filename) && static::productImageExists($filename)) {
                return route('products.image', ['filename' => $filename]);
            }

            return $value;
        }

        $filename = basename(str_replace('\\', '/', (string) $value));

        if (empty($filename)) {
            return Utility::defaultImage();
        }

        if (static::productImageExists($filename)) {
            return route('products.image', ['filename' => $filename]);
        }

        return Utility::defaultImage();
    }

    public static function productImageExists(?string $filename): bool
    {
        if (empty($filename)) {
            return false;
        }

        foreach (static::productImagePaths($filename) as $path) {
            if (is_file($path)) {
                return true;
            }
        }

        return false;
    }

    public static function productImagePaths(string $filename): array
    {
        $filename = basename(str_replace('\\', '/', $filename));

        return [
            storage_path('uploads/products/' . $filename),
            public_path('uploads/products/' . $filename),
            public_path('storage/uploads/products/' . $filename),
            storage_path('app/public/uploads/products/' . $filename),
        ];
    }

    public function getUnit()
    {
        return $this->belongsTo(Units::class,'unit');
    }

    public function getUnitType()
    {
        return $this->belongsTo(UnitTypes::class,'unit_type');
    }

    public function getCategory()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function getGstSlabMaster()
    {
        return $this->belongsTo(GstSlabMaster::class,'gst_slab_master_id');
    }

    public function marketplaceListings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class, 'product_id')
            ->with('marketplaceAccount')
            ->orderBy('platform')
            ->orderBy('account_name')
            ->orderBy('platform_sku');
    }

    public function activeMarketplaceListings(): HasMany
    {
        return $this->marketplaceListings()->where('listing_status', 'active');
    }

}
