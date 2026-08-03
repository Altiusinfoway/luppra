<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Auth;
use File;
use App\Models\Utility;
use App\Models\Products;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use Illuminate\Validation\ValidationException;
use App\Jobs\ProcessProductUpload;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use App\Models\GstSlabMaster;
use App\Models\ProductStockActivity;
use App\Models\MarketplaceListing;
use App\Models\OrderProduct;
use App\Models\QuoteProducts;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
;

class ProductController extends Controller
{
    private array $supportedPlatforms = [
        'amazon' => 'Amazon',
        'flipkart' => 'Flipkart',
    ];

    private array $listingStatuses = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'draft' => 'Draft',
    ];

    private array $fulfillmentTypes = [
        'fba' => 'FBA',
        'fbm' => 'FBM',
        'flipkart_fbf' => 'Flipkart Fulfilled',
        'seller' => 'Seller Fulfilled',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('draw')) {
            return $this->data($request);
        }

        if (\Auth::user()->can('manage product & service')) {
            $products = Products::query()
                ->where('created_by', \Auth::user()->creatorId())
                ->with('getGstSlabMaster')
                ->orderByDesc('id');

            if (Schema::hasTable('marketplace_listings')) {
                $products->withCount('marketplaceListings');
            }

            $productCollection = $products->get();
            $totalListings = Schema::hasTable('marketplace_listings')
                ? (int) $productCollection->sum('marketplace_listings_count')
                : 0;
            $totalStock = (float) $productCollection->sum(function ($product) {
                return (float) ($product->stock_qty ?? 0);
            });
            $averagePrice = (float) $productCollection->avg(function ($product) {
                return (float) ($product->price ?? 0);
            });

            return response()
                ->view('products.index', [
                'products' => $productCollection,
                'marketplaceEnabled' => Schema::hasTable('marketplace_listings'),
                'productSummary' => [
                    'total_products' => $productCollection->count(),
                    'total_listings' => $totalListings,
                    'total_stock' => $totalStock,
                    'average_price' => $averagePrice,
                ],
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function data(Request $request)
    {
        if (!\Auth::user()->can('manage product & service')) {
            return response()->json([
                'draw' => (int) $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        try {
            $query = Products::query()
                ->where('created_by', \Auth::user()->creatorId())
                ->orderByDesc('id');

            if (Schema::hasTable('marketplace_listings')) {
                $query->withCount('marketplaceListings');
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('gst_val', function ($row) {
                    return $row?->getGstSlabMaster?->rate ?? 0;
                })
                ->addColumn('qty_val', function ($row) {
                    return '<input
                            type="number"
                            class="form-control stock-input"
                            data-product-id="'.$row->id.'"
                            value="'.($row->stock_qty ?? 0).'"
                            min="0"
                            style="width:120px;"
                        >';
                })
                ->addColumn('marketplaces', function ($row) {
                    if (!isset($row->marketplace_listings_count)) {
                        return '<span class="badge bg-light text-muted">Disabled</span>';
                    }

                    if ((int) $row->marketplace_listings_count === 0) {
                        return '<a href="' . route('products.marketplace', $row->id) . '" class="badge bg-light text-muted text-decoration-none">No listings</a>';
                    }

                    return '<a href="' . route('products.marketplace', $row->id) . '" class="badge bg-primary-subtle text-primary text-decoration-none">'
                        . (int) $row->marketplace_listings_count
                        . ' listings</a>';
                })
                ->addColumn('image', function ($row) {
                    $img = $row->image;
                    return ' <div class="flex-shrink-0  bg-light rounded p-1" style="width: 50px; height: 50px;">
                                    <img src="' . $img . '"alt="Products" style="width: 100%; height: 100%; object-fit: cover">
                              </div>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('products.edit', $row->id);

                    $html = '<div class="dropdown d-inline-block">
                                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-fill align-middle"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">';

                    if (\Auth::user()->can('edit product & service')) {
                        $html .= '<li>
                                        <a href="javascript:void(0);" class="dropdown-item edit-item-btn"
                                        data-size="lg" data-url="' . $editUrl . '"
                                        data-ajax-popup="true" data-bs-original-title="Edit Product">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                    </li>';

                        $html .= '<li>
                                        <a href="' . route('products.marketplace', $row->id) . '" class="dropdown-item">
                                            <i class="ri-store-2-line align-bottom me-2 text-muted"></i> Manage Listings
                                        </a>
                                    </li>';

                        $html .= '<li>
                                        <a href="' . route('products.activity', $row->id) . '" class="dropdown-item">
                                            <i class="ri-history-line align-bottom me-2 text-muted"></i> View Activity
                                        </a>
                                    </li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->rawColumns(['action', 'image', 'gst_val', 'qty_val', 'marketplaces'])
                ->setRowClass('main-row')
                ->make(true);
        } catch (\Throwable $e) {
            \Log::error('Product datatable failed', [
                'message' => $e->getMessage(),
                'user_id' => \Auth::id(),
                'database' => config('database.connections.mysql.database'),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'draw' => (int) $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load products right now.',
            ]);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->can('create product & service')) {
            // $data['type_array'] = ['inhouse' => 'inhouse'];
            $data['unitTypes'] = Utility::getUnitTypes();
            $categories = Category::whereNull('parent_id')
                ->with('children')
                ->get();
            $data['categories'] = $this->buildCategoryDropdown($categories);
            // $data['categories'] = Category::pluck('name', 'id')->toArray();

            $data['gst_all'] = GstSlabMaster::where('created_by',\Auth::user()->creatorId())->pluck('rate', 'id')->toArray();
            $data['supportedPlatforms'] = $this->supportedPlatforms;
            $data['listingStatuses'] = $this->listingStatuses;
            $data['fulfillmentTypes'] = $this->fulfillmentTypes;
            $data['marketplaceEnabled'] = Schema::hasTable('marketplace_listings');
            return view('products.create', $data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    private function buildCategoryDropdown($categories, $prefix = '')
    {
        $result = [];

        foreach ($categories as $category) {
            $result[$category->id] = $prefix . $category->name;

            if ($category->children && $category->children->count()) {
                $result += $this->buildCategoryDropdown(
                    $category->children,
                    $prefix . '— '
                );
            }
        }

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:products,name,NULL,id,created_by,' . \Auth::user()->creatorId(),
                    'category_id' => 'nullable|exists:categories,id',
                    'code' => 'required|string|max:255|unique:products,sku_code,NULL,id,created_by,' . \Auth::user()->creatorId(),
                    'price' => 'required|numeric',
                    'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                    'gst_slab_master_id' => 'required|not_in:0|exists:gst_slab_masters,id',
                    'marketplace_listings' => 'nullable|array',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $image = "";
            if ($request->hasFile('image')) {

                $filenameWithExt = $request->file('image')->getClientOriginalName();

                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/products/';
                $image_path = $dir . $fileNameToStore;
                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }
                $url = '';
                $path = Utility::upload_file($request, 'image', $fileNameToStore, $dir, []);

                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                $image  = !empty($request->image) ? $fileNameToStore : '';
            }

            $product             = new Products();
            $product->category_id = $request->category_id ?? null;
            $product->name       = $request['name'];
            $product->sku_code   = $request['code'];
            $product->price      = $request['price'];
            // $product->dealer_price       = $request['dealer_price'] ?? 0;
            $product->image      = $image;
            $product->created_by = \Auth::user()->creatorId();
            // $product->type       = $request['type'] == 'inhouse' ? 'inhouse':'vendor';
            $product->unit_type       = $request['unit_type'];
            $product->unit       = $request['unit'];
            $product->hsn_code = $request->hsn_code;
            $product->gst_slab_master_id = $request->gst_slab_master_id;
            $product->stock_qty = $request->stock_qty ?? 0;

            $product->save();
            $this->syncMarketplaceListings($product, $request);
            $product->load(['getCategory', 'getGstSlabMaster']);
            $after = $this->productSnapshot($product);

            ActivityLogger::writeFor('products', 'create', $product, null, [
                'event_key' => 'product.created',
                'description' => 'Created product ' . $product->name . '.',
                'properties' => [
                    'after' => $after,
                    'changes' => collect($after)->map(fn ($value) => [
                        'before' => null,
                        'after' => $value,
                    ])->all(),
                ],
            ]);

            $this->recordProductStockChange(
                $product,
                null,
                (float) ($product->stock_qty ?? 0),
                'Initial stock set during product creation.'
            );

            return redirect()->route('products.index')->with('success', 'Product successfully created.', 'Product ' . $product->name . ' added!');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        if (\Auth::user()->can('edit product & service')) {
            $product = Products::findOrFail($id);
            $unitTypes = Utility::getUnitTypes();
            $units = Utility::getUnits($product->unit_type);
            $categories = Category::whereNull('parent_id')
                ->with('children')
                ->get();

            $gst_all = GstSlabMaster::where('created_by',\Auth::user()->creatorId())->pluck('rate', 'id')->toArray();
            $supportedPlatforms = $this->supportedPlatforms;
            $listingStatuses = $this->listingStatuses;
            $fulfillmentTypes = $this->fulfillmentTypes;
            $marketplaceEnabled = Schema::hasTable('marketplace_listings');
            if ($marketplaceEnabled) {
                $product->load('marketplaceListings');
            }
            return view('products.edit', compact('product', 'unitTypes', 'units', 'categories','gst_all', 'supportedPlatforms', 'listingStatuses', 'fulfillmentTypes', 'marketplaceEnabled'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function marketplace(string $id)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($id);
        $supportedPlatforms = $this->supportedPlatforms;
        $listingStatuses = $this->listingStatuses;
        $fulfillmentTypes = $this->fulfillmentTypes;
        $marketplaceEnabled = Schema::hasTable('marketplace_listings');

        if ($marketplaceEnabled) {
            $product->load('marketplaceListings');
        }

        $quoteSummary = QuoteProducts::query()
            ->where('product_id', $product->id)
            ->selectRaw('COUNT(DISTINCT quote_id) as total_quotes, COALESCE(SUM(qty), 0) as total_qty, COALESCE(SUM(total), 0) as total_value')
            ->first();

        $orderSummary = OrderProduct::query()
            ->where('product_id', $product->id)
            ->selectRaw('COUNT(DISTINCT order_id) as total_orders, COALESCE(SUM(qty), 0) as total_qty, COALESCE(SUM(total), 0) as total_value')
            ->first();

        $listingBreakdown = collect();
        if ($marketplaceEnabled) {
            $listingBreakdown = MarketplaceListing::query()
                ->where('product_id', $product->id)
                ->select(
                    'platform',
                    DB::raw('COUNT(*) as total_listings'),
                    DB::raw("SUM(CASE WHEN listing_status = 'active' THEN 1 ELSE 0 END) as active_listings"),
                    DB::raw('COALESCE(SUM(allocated_stock), 0) as allocated_stock'),
                    DB::raw('COALESCE(SUM(reserved_stock), 0) as reserved_stock')
                )
                ->groupBy('platform')
                ->orderBy('platform')
                ->get();
        }

        $recentQuotes = QuoteProducts::query()
            ->leftJoin('quotes', 'quote_products.quote_id', '=', 'quotes.id')
            ->leftJoin('entities', 'quotes.customer_id', '=', 'entities.id')
            ->leftJoin('marketplace_listings', 'quote_products.marketplace_listing_id', '=', 'marketplace_listings.id')
            ->where('quote_products.product_id', $product->id)
            ->orderByDesc('quote_products.id')
            ->limit(8)
            ->get([
                'quote_products.qty',
                'quote_products.price',
                'quote_products.total',
                'quotes.id as quote_id',
                'quotes.code as quote_code',
                'quotes.date as quote_date',
                'entities.name as customer_name',
                'marketplace_listings.platform',
                'marketplace_listings.platform_sku',
                'marketplace_listings.listing_title',
            ]);

        $recentOrders = OrderProduct::query()
            ->leftJoin('orders', 'order_products.order_id', '=', 'orders.id')
            ->leftJoin('entities', 'orders.customer_id', '=', 'entities.id')
            ->leftJoin('marketplace_listings', 'order_products.marketplace_listing_id', '=', 'marketplace_listings.id')
            ->where('order_products.product_id', $product->id)
            ->orderByDesc('order_products.id')
            ->limit(8)
            ->get([
                'order_products.qty',
                'order_products.price',
                'order_products.total',
                'orders.id as order_id',
                'orders.order_number',
                'orders.date as order_date',
                'orders.order_source_type',
                'entities.name as customer_name',
                'marketplace_listings.platform',
                'marketplace_listings.platform_sku',
                'marketplace_listings.listing_title',
            ]);

        $defaultBaseUnitPrice = (float) (($product->dealer_price ?? 0) > 0 ? $product->dealer_price : 0);

        $rawListingSales = collect();
        if ($marketplaceEnabled) {
            $rawListingSales = OrderProduct::query()
                ->join('marketplace_listings', 'order_products.marketplace_listing_id', '=', 'marketplace_listings.id')
                ->where('order_products.product_id', $product->id)
                ->whereNotNull('order_products.marketplace_listing_id')
                ->select(
                    'marketplace_listings.id as listing_id',
                    'marketplace_listings.platform',
                    DB::raw('COUNT(DISTINCT order_products.order_id) as order_count'),
                    DB::raw('COALESCE(SUM(order_products.qty), 0) as sold_qty'),
                    DB::raw('COALESCE(SUM(order_products.total), 0) as revenue'),
                    DB::raw('COALESCE(AVG(order_products.price), 0) as avg_sale_price')
                )
                ->groupBy('marketplace_listings.id', 'marketplace_listings.platform')
                ->get()
                ->keyBy('listing_id');
        }

        $listingAnalytics = collect();
        $platformAnalytics = collect();
        if ($marketplaceEnabled) {
            $listingAnalytics = $product->marketplaceListings->map(function ($listing) use ($rawListingSales, $defaultBaseUnitPrice) {
                $sales = $rawListingSales->get($listing->id);
                $internalOrderCount = (int) ($sales->order_count ?? 0);
                $internalSoldQty = (float) ($sales->sold_qty ?? 0);
                $internalRevenue = (float) ($sales->revenue ?? 0);
                $baseUnitPrice = (float) (($listing->base_price ?? 0) > 0 ? $listing->base_price : $defaultBaseUnitPrice);
                $hasExternalFeed = !is_null($listing->external_last_synced_at)
                    || (int) ($listing->external_orders_count ?? 0) > 0
                    || (float) ($listing->external_sold_qty ?? 0) > 0
                    || (float) ($listing->external_revenue ?? 0) > 0;
                $reportedOrderCount = $hasExternalFeed ? (int) ($listing->external_orders_count ?? 0) : $internalOrderCount;
                $reportedSoldQty = $hasExternalFeed ? (float) ($listing->external_sold_qty ?? 0) : $internalSoldQty;
                $reportedRevenue = $hasExternalFeed ? (float) ($listing->external_revenue ?? 0) : $internalRevenue;
                $estimatedCost = $baseUnitPrice > 0 ? $reportedSoldQty * $baseUnitPrice : 0;
                $profit = $reportedRevenue - $estimatedCost;
                $marginPercent = $reportedRevenue > 0 ? ($profit / $reportedRevenue) * 100 : null;

                return (object) [
                    'listing' => $listing,
                    'platform' => $listing->platform,
                    'analytics_source' => $hasExternalFeed ? 'api' : 'internal',
                    'order_count' => $reportedOrderCount,
                    'sold_qty' => $reportedSoldQty,
                    'revenue' => $reportedRevenue,
                    'avg_sale_price' => (float) ($sales->avg_sale_price ?? 0),
                    'base_unit_price' => $baseUnitPrice,
                    'estimated_cost' => $estimatedCost,
                    'profit' => $profit,
                    'margin_percent' => $marginPercent,
                    'internal_order_count' => $internalOrderCount,
                    'internal_sold_qty' => $internalSoldQty,
                    'internal_revenue' => $internalRevenue,
                    'external_order_count' => (int) ($listing->external_orders_count ?? 0),
                    'external_sold_qty' => (float) ($listing->external_sold_qty ?? 0),
                    'external_revenue' => (float) ($listing->external_revenue ?? 0),
                    'external_last_synced_at' => $listing->external_last_synced_at,
                    'external_sync_note' => $listing->external_sync_note,
                ];
            });

            $platformAnalytics = $listingAnalytics
                ->groupBy('platform')
                ->map(function ($rows, $platform) {
                    $soldQty = (float) $rows->sum('sold_qty');
                    $revenue = (float) $rows->sum('revenue');
                    $estimatedCost = (float) $rows->sum('estimated_cost');
                    $profit = (float) $rows->sum('profit');

                    return (object) [
                        'platform' => $platform,
                        'listing_count' => $rows->count(),
                        'order_count' => (int) $rows->sum('order_count'),
                        'sold_qty' => $soldQty,
                        'revenue' => $revenue,
                        'estimated_cost' => $estimatedCost,
                        'profit' => $profit,
                        'margin_percent' => $revenue > 0 ? ($profit / $revenue) * 100 : null,
                    ];
                })
                ->sortBy('platform')
                ->values();
        }

        $stats = [
            'master_stock' => (int) ($product->stock_qty ?? 0),
            'total_listings' => $marketplaceEnabled ? $product->marketplaceListings->count() : 0,
            'active_listings' => $marketplaceEnabled ? $product->marketplaceListings->where('listing_status', 'active')->count() : 0,
            'allocated_stock' => $marketplaceEnabled ? (int) $product->marketplaceListings->sum(fn ($listing) => (int) ($listing->allocated_stock ?? 0)) : 0,
            'reserved_stock' => $marketplaceEnabled ? (int) $product->marketplaceListings->sum(fn ($listing) => (int) ($listing->reserved_stock ?? 0)) : 0,
            'available_listing_stock' => $marketplaceEnabled ? (int) $product->marketplaceListings->sum(fn ($listing) => (int) $listing->available_stock) : 0,
            'quotes_count' => (int) ($quoteSummary->total_quotes ?? 0),
            'quoted_qty' => (float) ($quoteSummary->total_qty ?? 0),
            'quoted_value' => (float) ($quoteSummary->total_value ?? 0),
            'orders_count' => (int) ($orderSummary->total_orders ?? 0),
            'ordered_qty' => (float) ($orderSummary->total_qty ?? 0),
            'ordered_value' => (float) ($orderSummary->total_value ?? 0),
            'base_unit_price' => $defaultBaseUnitPrice,
            'marketplace_sold_qty' => (float) $listingAnalytics->sum('sold_qty'),
            'marketplace_revenue' => (float) $listingAnalytics->sum('revenue'),
            'marketplace_estimated_cost' => (float) $listingAnalytics->sum('estimated_cost'),
            'marketplace_profit' => (float) $listingAnalytics->sum('profit'),
            'marketplace_margin_percent' => (float) (($listingAnalytics->sum('revenue') ?? 0) > 0 ? ($listingAnalytics->sum('profit') / $listingAnalytics->sum('revenue')) * 100 : 0),
            'api_connected_listings' => (int) $listingAnalytics->where('analytics_source', 'api')->count(),
        ];

        return view('products.marketplace', compact(
            'product',
            'supportedPlatforms',
            'listingStatuses',
            'fulfillmentTypes',
            'marketplaceEnabled',
            'stats',
            'listingBreakdown',
            'listingAnalytics',
            'platformAnalytics',
            'recentQuotes',
            'recentOrders'
        ));
    }

    public function activity(string $id)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($id);
        $activityTimeline = ActivityLogger::activityForRecord($product, null, 12, 'product_activities_page');
        $stockActivities = ProductStockActivity::query()
            ->with('created_user:id,name')
            ->where('product_id', $product->id)
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->paginate(12, ['*'], 'stock_activities_page')
            ->withQueryString();

        return view('products.activity', compact('product', 'activityTimeline', 'stockActivities'));
    }

    public function updateMarketplace(Request $request, string $id)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'marketplace_listings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first())->withInput();
        }

        $this->syncMarketplaceListings($product, $request);

        return redirect()->route('products.marketplace', $product->id)->with('success', 'Marketplace listings updated successfully.');
    }

    public function createMarketplaceListing(string $id)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($id);

        if (!Schema::hasTable('marketplace_listings')) {
            return redirect()->route('products.marketplace', $product->id)->with('error', 'Marketplace listing support is unavailable.');
        }

        $listing = new MarketplaceListing([
            'listing_status' => 'active',
            'reserved_stock' => 0,
        ]);

        $supportedPlatforms = $this->supportedPlatforms;
        $listingStatuses = $this->listingStatuses;
        $fulfillmentTypes = $this->fulfillmentTypes;

        return view('products.listings.create', compact('product', 'listing', 'supportedPlatforms', 'listingStatuses', 'fulfillmentTypes'));
    }

    public function storeMarketplaceListing(Request $request, string $id)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($id);

        if (!Schema::hasTable('marketplace_listings')) {
            return redirect()->route('products.marketplace', $product->id)->with('error', 'Marketplace listing support is unavailable.');
        }

        $payload = $this->validateMarketplaceListing($request, $product);
        $listing = new MarketplaceListing();
        $listing->fill($payload);
        $listing->save();

        return redirect()->route('products.marketplace', $product->id)->with('success', 'Marketplace listing added successfully.');
    }

    public function editMarketplaceListing(string $productId, string $listingId)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($productId);
        $listing = MarketplaceListing::where('product_id', $product->id)->findOrFail($listingId);

        $supportedPlatforms = $this->supportedPlatforms;
        $listingStatuses = $this->listingStatuses;
        $fulfillmentTypes = $this->fulfillmentTypes;

        return view('products.listings.edit', compact('product', 'listing', 'supportedPlatforms', 'listingStatuses', 'fulfillmentTypes'));
    }

    public function updateMarketplaceListing(Request $request, string $productId, string $listingId)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($productId);
        $listing = MarketplaceListing::where('product_id', $product->id)->findOrFail($listingId);

        $payload = $this->validateMarketplaceListing($request, $product, $listing->id);
        $listing->fill($payload);
        $listing->save();

        return redirect()->route('products.marketplace', $product->id)->with('success', 'Marketplace listing updated successfully.');
    }

    public function destroyMarketplaceListing(string $productId, string $listingId)
    {
        if (!\Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', 'Permission denied.');
        }

        $product = Products::where('created_by', \Auth::user()->creatorId())->findOrFail($productId);
        $listing = MarketplaceListing::where('product_id', $product->id)->findOrFail($listingId);
        $listing->delete();

        return redirect()->route('products.marketplace', $product->id)->with('success', 'Marketplace listing deleted successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        if (\Auth::user()->can('edit product & service')) {

            $product = Products::find($id);
            $product->loadMissing(['getCategory', 'getGstSlabMaster']);
            $before = $this->productSnapshot($product);
            $previousStockQty = (float) ($product->stock_qty ?? 0);

            $validator = \Validator::make(

                $request->all(),
                [
                    'name' => 'required|max:100|unique:products,name,' . $id . ',id,created_by,' . \Auth::user()->creatorId(),
                    'category_id' => 'nullable|exists:categories,id',
                    'sku_code' => 'required|string|max:255|unique:products,sku_code,' . $id . ',id,created_by,' . \Auth::user()->creatorId(),
                    'price' => 'required|numeric',
                    'image' => 'file|mimes:jpg,jpeg,png|max:2048',
                    'gst_slab_master_id' => 'required|not_in:0|exists:gst_slab_masters,id',
                    'marketplace_listings' => 'nullable|array',
                ]

            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            if ($request->hasFile('image')) {

                $filenameWithExt = $request->file('image')->getClientOriginalName();

                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/products/';
                $image_path = $dir . $fileNameToStore;
                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }
                $url = '';
                $path = Utility::upload_file($request, 'image', $fileNameToStore, $dir, []);

                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                $product->image = !empty($request->image) ? $fileNameToStore : '';
            }


            $product->name = $request->name;
            $product->category_id = $request->category_id ?? null;
            $product->sku_code = $request->sku_code;
            $product->price = $request->price;
            if ($request['type'] == 'inhouse') {
                $product->dealer_price =  0;
            } else {
                $product->dealer_price = $request->dealer_price ?? 0;
            }
            // $product->type = $request['type'] == 'inhouse' ? 'inhouse':'vendor';
            $product->unit_type       = $request['unit_type'];
            $product->unit       = $request['unit'];
            $product->hsn_code       = $request['hsn_code'];
            $product->gst_slab_master_id       = $request['gst_slab_master_id'];
            $product->stock_qty = $request['stock_qty'];
            $product->save();
            $this->syncMarketplaceListings($product, $request);
            $product->load(['getCategory', 'getGstSlabMaster']);
            $after = $this->productSnapshot($product);
            $changes = ActivityLogger::diff($before, $after);

            if (!empty($changes)) {
                ActivityLogger::writeFor('products', 'update', $product, null, [
                    'event_key' => 'product.updated',
                    'description' => 'Updated product ' . $product->name . '.',
                    'properties' => [
                        'before' => $before,
                        'after' => $after,
                        'changes' => $changes,
                    ],
                ]);
            }

            $this->recordProductStockChange(
                $product,
                $previousStockQty,
                (float) ($product->stock_qty ?? 0),
                'Stock adjusted from the product edit form.'
            );


            return redirect()->route('products.index')->with('success', 'Product successfully updated.', 'Product ' . $product->name . ' updated!');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getSelectedProducts(Request $request)
    {
        $products = Products::whereIn('id', $request->productIds)->get();
        if ($products) {
            $raw_usage = Products::where('product_id', $request->productId)->select('id', 'qty')->get()->toArray();
            $usage = array_column($raw_usage, null, 'item_id');

            return view('products.selected_products', compact('products', 'usage'));
        }
    }

    public function product_quick_create(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {
            $data['unitTypes'] = Utility::getUnitTypes();
            $data['gst_all'] = GstSlabMaster::where('created_by', \Auth::user()->creatorId())
                ->pluck('rate', 'id')
                ->toArray();
            return view('products.quick_create', $data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function product_quick_store(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:100|unique:products,name,NULL,id,created_by,' . \Auth::user()->creatorId(),
                    'code' => 'required',
                    'price' => 'required|numeric',
                    'gst_slab_master_id' => 'required|exists:gst_slab_masters,id',
                    'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $image = "";
            if ($request->hasFile('image')) {

                $filenameWithExt = $request->file('image')->getClientOriginalName();

                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/products/';
                $image_path = $dir . $fileNameToStore;
                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }
                $url = '';
                $path = Utility::upload_file($request, 'image', $fileNameToStore, $dir, []);

                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                $image  = !empty($request->image) ? $fileNameToStore : '';
            }

            $product             = new Products();
            $product->name       = $request['name'];
            $product->sku_code   = $request['code'];
            $product->price      = $request['price'];
            // $product->dealer_price       = $request['dealer_price'] ?? 0;
            $product->image      = $image;
            $product->created_by = \Auth::user()->creatorId();
            // $product->type       = $request['type'] == 'inhouse' ? 'inhouse':'vendor';
            $product->unit_type       = $request['unit_type'];
            $product->hsn_code       = $request['hsn_code'];
            $product->unit       = $request['unit'];
            $product->gst_slab_master_id = $request['gst_slab_master_id'];

            $product->save();
            $this->syncMarketplaceListings($product, $request);
            $product->load(['getCategory', 'getGstSlabMaster']);
            $after = $this->productSnapshot($product);

            ActivityLogger::writeFor('products', 'create', $product, null, [
                'event_key' => 'product.quick_created',
                'description' => 'Created product ' . $product->name . ' from quick create.',
                'properties' => [
                    'after' => $after,
                    'changes' => collect($after)->map(fn ($value) => [
                        'before' => null,
                        'after' => $value,
                    ])->all(),
                ],
            ]);

            return redirect()->back()->with('success', 'Product successfully created.', 'Product ' . $product->name . ' added!');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function upload_excel_product(Request $request)
    {
        return view('products.up_excel_product');
    }

    public function get_header(Request $request)
    {
        Log::info('---------------------- START UPLOAD PRODUCT --------------');
        Log::info('------------get Headers step-1---------------');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            // Convert excel to collection
            $allSheets = Excel::toCollection(null, $request->file('excel_file'));

            if ($allSheets->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.'
                ]);
            }

            $firstSheet = $allSheets->first();

            if ($firstSheet->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'The first sheet is empty.'
                ]);
            }

            $headers = array_values($firstSheet->shift()->toArray());

            Log::info('headers list: ', [$headers]);

            return response()->json([
                'status' => true,
                'headers_list' => $headers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function excel_preview(Request $request)
    {

        Log::info('-------- excel_preview step-2------');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {

            $preDefined_Col = ['name', 'sku_code', 'price', 'unit_type', 'unit'];
            $mapping = $request->input('mapping', []);
            // $mapping = is_array($mapping[0]) ? $mapping[0] : $mapping;

            $import = new ProductImport($mapping);
            Excel::import($import, $request->file('excel_file'));
            $allSheets = Excel::toCollection(null, $request->file('excel_file'));

            $data = $allSheets->map(function ($sheet) {
                return $sheet->toArray();
            });


            Log::info('Predefined columns:', $preDefined_Col);
            Log::info('Mapping received:', $mapping);
            $allData = $allSheets->first()->toArray();
            $preDefined_Col = ['name', 'sku_code', 'price', 'unit_type', 'unit'];

            foreach ($preDefined_Col as $i => $colName) {
                if (!isset($mapping[$i])) {
                    Log::warning("No mapping for {$colName}");
                    continue;
                }

                $excelIndexName  = (int) $mapping[0];
                $excelIndexSKUCode = (int) $mapping[1];
                $excelIndexPrice = (float) $mapping[2];
                $excelIndexUnitType = (int) $mapping[3];
                $excelIndexUnit = (int) $mapping[4];

                foreach (array_slice($allData, 1) as $rowIndex => $dataRow) {

                    // --- Name Validation ---
                    $cellValueName = $dataRow[$excelIndexName] ?? null;
                    if ($cellValueName) {

                        if (preg_match('/^\d+$/', $cellValueName) || str_contains($cellValueName, '@')) {
                            Log::info('excel name ', [$cellValueName]);
                            return response()->json([
                                'status' => false,
                                'message' => 'Name error',
                                'data' => [],
                                'messages' => "Invalid name in row " . ($rowIndex + 2) . ": '{$cellValueName}'",
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Name is empty',
                            'data' => [],
                            'messages' => "Name is empty in row " . ($rowIndex + 2),
                        ]);
                    }

                    //sku code validation
                    // if (isset($mapping[1]) && $mapping[1] !== null && $mapping[1] !== '') {
                    //     $excelIndexSKUCode = (int) $mapping[1];
                    //     $skuValue = $dataRow[$excelIndexSKUCode] ?? null;
                    //     if (empty($skuValue)) {
                    //         return response()->json([
                    //             'status'   => false,
                    //             'message'  => 'SKU Code is empty',
                    //             'data'     => [],
                    //             'messages' => "SKU code is empty in row " . ($rowIndex + 2),
                    //         ]);
                    //     }
                    // }

                    //price validation
                    if (isset($mapping[2]) && $mapping[2] !== null && $mapping[2] !== '') {
                        $excelIndexPrice = (float) $mapping[2];
                        $priceValue = $dataRow[$excelIndexPrice] ?? null;

                        if (empty($priceValue)) {
                            return response()->json([
                                'status'   => false,
                                'message'  => 'Price is empty',
                                'data'     => [],
                                'messages' => "Price is empty in row " . ($rowIndex + 2),
                            ]);
                        }

                        $cleanPrice = str_replace(',', '', $priceValue);

                        if (!preg_match('/^\d+(\.\d+)?$/', $cleanPrice)) {
                            return response()->json([
                                'status'   => false,
                                'message'  => 'Invalid Price format',
                                'data'     => [],
                                'messages' => "Invalid price in row " . ($rowIndex + 2) . ": '{$priceValue}'",
                            ]);
                        }
                    }

                    //unit type validation
                    if (isset($mapping[3]) && $mapping[3] !== null && $mapping[3] !== '') {
                        $excelIndexUnitType = (int) $mapping[3];
                        $unitTypeValue = $dataRow[$excelIndexUnitType] ?? null;

                        if (empty(trim($unitTypeValue))) {
                            return response()->json([
                                'status'   => false,
                                'message'  => 'Unit Type is empty',
                                'data'     => [],
                                'messages' => "Unit Type is empty in row " . ($rowIndex + 2),
                            ]);
                        }

                        // Only alphabets and space allowed
                        if (!preg_match('/^[A-Za-z ]+$/', $unitTypeValue)) {
                            return response()->json([
                                'status'   => false,
                                'message'  => 'Invalid Unit Type',
                                'data'     => [],
                                'messages' => "Invalid Unit Type in row " . ($rowIndex + 2) . ": '{$unitTypeValue}' & only alphabets are allowed",
                            ]);
                        }
                    }

                    // unit validation
                    if (isset($mapping[4]) && $mapping[4] !== null && $mapping[4] !== '') {
                        $excelIndexUnit = (int) $mapping[4];
                        $unitValue = $dataRow[$excelIndexUnit] ?? null;

                        if (empty(trim($unitValue))) {
                            return response()->json([
                                'status'   => false,
                                'message'  => 'Unit is empty',
                                'data'     => [],
                                'messages' => "Unit is empty in row " . ($rowIndex + 2),
                            ]);
                        }

                        // Only alphabets and space allowed
                        if (!preg_match('/^[A-Za-z ]+$/', $unitValue)) {
                            return response()->json([
                                'status'   => false,
                                'message'  => 'Invalid Unit value',
                                'data'     => [],
                                'messages' => "Invalid Unit in row " . ($rowIndex + 2) . ": '{$unitValue}' & only alphabets are allowed",
                            ]);
                        }
                    }
                }
            }

            // Log::info('all data ',[$allData]);

            $filteredData = [];

            $validMappings = [];
            foreach ($mapping as $i => $mapIndex) {
                if ($mapIndex !== null && $mapIndex !== '') {
                    $validMappings[$i] = (int) $mapIndex;
                }
            }

            foreach ($allData as $rowIndex => $row) {
                $filteredRow = [];
                foreach ($validMappings as $i => $colIndex) {
                    $filteredRow[$preDefined_Col[$i]] = $row[$colIndex] ?? null;
                }
                $filteredData[] = $filteredRow;
            }

            Log::info('Filtered Excel Data:', $filteredData);

            return response()->json([
                'status' => true,
                'message' => 'Filtered data extracted successfully',
                'data' => $filteredData,
                'messages' => $import->rowErrors,
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadProducts(Request $request)
    {
        try {
            Log::info('------ upload products -----------');
            $products = json_decode($request->product_data, true);
            $jobKey = 'product_upload_' . uniqid();

            Log::info('products : ', ['products' => $products]);
            Log::info('job key : ', ['job' => $jobKey]);


            Cache::put($jobKey, 'pending', now()->addMinutes(60));

            $user = auth()->user()->id;
            Log::info('user: ', ['user' => $user]);

            ProcessProductUpload::dispatch($user, $jobKey, $products);

            return response()->json(['status' => true, 'job_key' => $jobKey]);
        } catch (\Exception $e) {
            \Log::error('product Upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Product Upload failed: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function bulkStockUpdate(Request $request)
    {
        foreach ($request->products as $item) {
            $product = Products::where('created_by', \Auth::user()->creatorId())->find($item['id']);

            if (!$product) {
                continue;
            }

            $beforeQty = (float) ($product->stock_qty ?? 0);
            $afterQty = (float) ($item['qty'] ?? 0);

            $product->update([
                'stock_qty' => $afterQty,
            ]);

            $this->recordProductStockChange(
                $product->fresh(),
                $beforeQty,
                $afterQty,
                'Stock updated from the product list screen.'
            );
        }

        return response()->json([
            'success' => true
        ]);
    }

    private function syncMarketplaceListings(Products $product, Request $request): void
    {
        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }

        $rows = $request->input('marketplace_listings', []);

        if (!is_array($rows)) {
            return;
        }

        $existingIds = $product->marketplaceListings()->pluck('id')->all();
        $persistedIds = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $platformSku = trim((string) ($row['platform_sku'] ?? ''));
            $platform = strtolower(trim((string) ($row['platform'] ?? '')));

            if ($platformSku === '' && $platform === '') {
                continue;
            }

            $payload = [
                'product_id' => $product->id,
                'created_by' => \Auth::user()->creatorId(),
                'platform' => $platform,
                'platform_sku' => $platformSku,
                'marketplace_item_id' => trim((string) ($row['marketplace_item_id'] ?? '')) ?: null,
                'listing_title' => trim((string) ($row['listing_title'] ?? '')),
                'pack_size' => trim((string) ($row['pack_size'] ?? '')) ?: null,
                'selling_price' => (float) ($row['selling_price'] ?? 0),
                'mrp' => (float) ($row['mrp'] ?? 0),
                'base_price' => isset($row['base_price']) && $row['base_price'] !== '' ? (float) $row['base_price'] : null,
                'listing_status' => strtolower(trim((string) ($row['listing_status'] ?? 'active'))) ?: 'active',
                'fulfillment_type' => trim((string) ($row['fulfillment_type'] ?? '')) ?: null,
                'allocated_stock' => $row['allocated_stock'] !== null && $row['allocated_stock'] !== '' ? (int) $row['allocated_stock'] : null,
                'reserved_stock' => (int) ($row['reserved_stock'] ?? 0),
                'external_orders_count' => (int) ($row['external_orders_count'] ?? 0),
                'external_sold_qty' => (float) ($row['external_sold_qty'] ?? 0),
                'external_revenue' => (float) ($row['external_revenue'] ?? 0),
                'external_last_synced_at' => !empty($row['external_last_synced_at']) ? $row['external_last_synced_at'] : null,
                'external_sync_note' => trim((string) ($row['external_sync_note'] ?? '')) ?: null,
            ];

            validator($payload, [
                'platform' => 'required|in:amazon,flipkart',
                'platform_sku' => 'required|string|max:255',
                'listing_title' => 'required|string|max:255',
                'selling_price' => 'nullable|numeric|min:0',
                'mrp' => 'nullable|numeric|min:0',
                'base_price' => 'nullable|numeric|min:0',
                'listing_status' => 'required|string|max:30',
                'allocated_stock' => 'nullable|integer|min:0',
                'reserved_stock' => 'nullable|integer|min:0',
                'external_orders_count' => 'nullable|integer|min:0',
                'external_sold_qty' => 'nullable|numeric|min:0',
                'external_revenue' => 'nullable|numeric|min:0',
                'external_last_synced_at' => 'nullable|date',
                'external_sync_note' => 'nullable|string|max:255',
            ])->validate();

            $listingId = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            $query = $product->marketplaceListings();
            if ($listingId) {
                $query->where('id', $listingId);
            }

            $listing = $query->first() ?? new MarketplaceListing();
            $listing->fill($payload);
            $listing->save();
            $persistedIds[] = $listing->id;
        }

        $deleteIds = array_diff($existingIds, $persistedIds);
        if (!empty($deleteIds)) {
            $product->marketplaceListings()->whereIn('id', $deleteIds)->delete();
        }
    }

    private function validateMarketplaceListing(Request $request, Products $product, ?int $listingId = null): array
    {
        $validated = $request->validate([
            'platform' => 'required|in:amazon,flipkart',
            'platform_sku' => 'required|string|max:255',
            'marketplace_item_id' => 'nullable|string|max:255',
            'listing_title' => 'required|string|max:255',
            'pack_size' => 'nullable|string|max:255',
            'selling_price' => 'nullable|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'listing_status' => 'required|string|max:30',
            'fulfillment_type' => 'nullable|string|max:50',
            'allocated_stock' => 'nullable|integer|min:0',
            'reserved_stock' => 'nullable|integer|min:0',
            'external_orders_count' => 'nullable|integer|min:0',
            'external_sold_qty' => 'nullable|numeric|min:0',
            'external_revenue' => 'nullable|numeric|min:0',
            'external_last_synced_at' => 'nullable|date',
            'external_sync_note' => 'nullable|string|max:255',
        ]);

        $platform = strtolower(trim((string) $validated['platform']));
        $platformSku = trim((string) $validated['platform_sku']);
        $marketplaceItemId = trim((string) ($validated['marketplace_item_id'] ?? '')) ?: null;

        $duplicateSku = MarketplaceListing::query()
            ->where('created_by', \Auth::user()->creatorId())
            ->where('platform', $platform)
            ->where('platform_sku', $platformSku);

        if ($listingId) {
            $duplicateSku->where('id', '!=', $listingId);
        }

        if ($duplicateSku->exists()) {
            throw ValidationException::withMessages([
                'platform_sku' => 'This platform SKU is already used for another listing.',
            ]);
        }

        if ($marketplaceItemId !== null) {
            $duplicateItem = MarketplaceListing::query()
                ->where('created_by', \Auth::user()->creatorId())
                ->where('platform', $platform)
                ->where('marketplace_item_id', $marketplaceItemId);

            if ($listingId) {
                $duplicateItem->where('id', '!=', $listingId);
            }

            if ($duplicateItem->exists()) {
                throw ValidationException::withMessages([
                    'marketplace_item_id' => 'This marketplace item id is already used for another listing.',
                ]);
            }
        }

        return [
            'product_id' => $product->id,
            'created_by' => \Auth::user()->creatorId(),
            'platform' => $platform,
            'platform_sku' => $platformSku,
            'marketplace_item_id' => $marketplaceItemId,
            'listing_title' => trim((string) $validated['listing_title']),
            'pack_size' => trim((string) ($validated['pack_size'] ?? '')) ?: null,
            'selling_price' => (float) ($validated['selling_price'] ?? 0),
            'mrp' => (float) ($validated['mrp'] ?? 0),
            'base_price' => isset($validated['base_price']) && $validated['base_price'] !== '' ? (float) $validated['base_price'] : null,
            'listing_status' => strtolower(trim((string) $validated['listing_status'])),
            'fulfillment_type' => trim((string) ($validated['fulfillment_type'] ?? '')) ?: null,
            'allocated_stock' => $validated['allocated_stock'] ?? null,
            'reserved_stock' => (int) ($validated['reserved_stock'] ?? 0),
            'external_orders_count' => (int) ($validated['external_orders_count'] ?? 0),
            'external_sold_qty' => (float) ($validated['external_sold_qty'] ?? 0),
            'external_revenue' => (float) ($validated['external_revenue'] ?? 0),
            'external_last_synced_at' => !empty($validated['external_last_synced_at']) ? $validated['external_last_synced_at'] : null,
            'external_sync_note' => trim((string) ($validated['external_sync_note'] ?? '')) ?: null,
        ];
    }

    private function productSnapshot(Products $product): array
    {
        return [
            'name' => (string) ($product->name ?? ''),
            'category' => optional($product->getCategory)->name,
            'sku_code' => (string) ($product->sku_code ?? ''),
            'price' => (float) ($product->price ?? 0),
            'dealer_price' => (float) ($product->dealer_price ?? 0),
            'unit_type' => (string) ($product->unit_type ?? ''),
            'unit' => (string) ($product->unit ?? ''),
            'hsn_code' => (string) ($product->hsn_code ?? ''),
            'gst_rate' => (string) (optional($product->getGstSlabMaster)->rate ?? ''),
            'stock_qty' => (float) ($product->stock_qty ?? 0),
            'image' => (string) ($product->getRawOriginal('image') ?? ''),
        ];
    }

    private function recordProductStockChange(Products $product, ?float $beforeQty, ?float $afterQty, string $contextMessage): void
    {
        $beforeQty = $beforeQty === null ? null : (float) $beforeQty;
        $afterQty = $afterQty === null ? null : (float) $afterQty;

        if ($beforeQty !== null && $afterQty !== null && abs($beforeQty - $afterQty) < 0.00001) {
            return;
        }

        $message = $beforeQty === null
            ? 'Stock initialized to ' . $this->formatStockValue($afterQty) . '.'
            : 'Stock changed from ' . $this->formatStockValue($beforeQty) . ' to ' . $this->formatStockValue($afterQty) . '.';

        ProductStockActivity::create([
            'product_id' => $product->id,
            'date_time' => now(),
            'message' => $message . ' ' . $contextMessage,
            'user_id' => \Auth::id(),
        ]);

        ActivityLogger::writeFor('products', 'update', $product, null, [
            'event_key' => 'product.stock_updated',
            'description' => $message,
            'properties' => [
                'changes' => [
                    'stock_qty' => [
                        'before' => $beforeQty,
                        'after' => $afterQty,
                    ],
                ],
                'context' => $contextMessage,
            ],
        ]);
    }

    private function formatStockValue(?float $value): string
    {
        $value = (float) ($value ?? 0);

        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return Str::of(number_format($value, 2, '.', ''))
            ->rtrim('0')
            ->rtrim('.')
            ->toString();
    }

}
