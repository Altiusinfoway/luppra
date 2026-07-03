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
;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage product & service')) {
            if ($request->ajax()) {
                try {
                    $canEdit = auth()->user()->can('edit product & service');
                    $canDelete = auth()->user()->can('delete product & service');

                    $query = Products::query()
                        ->where('created_by', \Auth::user()->creatorId())
                        ->orderByDesc('id');

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

                        // ->addColumn('is_active', function ($row) {
                        //     if($row->is_active == 1)
                        //     {
                        //         return '<h5><span class="badge bg-success-subtle text-success">' .'Active' . '</span></h5>';
                        //     }
                        //     else
                        //     {
                        //         return '<h5><span class="badge bg-success-subtle text-danger" >' .'In-Active' . '</span></h5>';
                        //     }

                        // })
                        ->addColumn('image', function ($row) {
                            $img = $row->image;
                            $html = ' <div class="flex-shrink-0  bg-light rounded p-1" style="width: 50px; height: 50px;">
                                            <img src="' . $img . '"alt="Products" style="width: 100%; height: 100%; object-fit: cover">
                                      </div>';
                            return $html;
                        })
                        ->addColumn('action', function ($row) {
                            $editUrl = route("products.edit", $row->id);

                            $editUrl = route('products.edit', $row->id);

                            $deleteUrl = route('products.destroy', $row->id);

                            $html = '<div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">';

                            // Edit
                            $canEdit = \Auth::user()->can('edit product & service');
                            if ($canEdit) {
                                $html .= '<li>
                                                <a href="javascript:void(0);" class="dropdown-item edit-item-btn"
                                                data-size="lg" data-url="' . $editUrl . '"
                                                data-ajax-popup="true" data-bs-original-title="Edit Product">
                                                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                </a>
                                            </li>';
                            }

                            // Delete or Restore
                            // if ($canDelete) {
                            //     $html .= '<li>
                            //                 <form method="POST" action="' . $deleteUrl . '" id="delete-form-' . $row->id . '">
                            //                     ' . csrf_field() . method_field('DELETE') . '
                            //                     <a href="javascript:void(0);" class="dropdown-item remove-item-btn">
                            //                         <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> ' .
                            //                         ($row->delete_status != 0 ? 'Delete' : 'Restore') .
                            //                     '</a>
                            //                 </form>
                            //             </li>';
                            // }


                            $html .= '</ul></div>';

                            return $html;
                        })

                        ->rawColumns(['action', 'image','gst_val','qty_val'])
                        ->setRowClass('main-row')
                        ->make(true);
                } catch (\Exception $e) {

                    return response()->json([
                        'error' => 'Server Error: ' . $e->getMessage()
                    ], 500);
                }
            }

            return view('products.index');
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
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
                    // 'code' => 'required',
                    'price' => 'required|numeric',
                    'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                     'gst_slab_master_id' => 'required|not_in:0|exists:gst_slab_masters,id',
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

            // ProductStockActivity::create([
            //     'product_id'=>$product->id,
            //     'message'=>'Product added by',
            //     'user_id'=>\Auth::user()->id,
            // ]);

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
            return view('products.edit', compact('product', 'unitTypes', 'units', 'categories','gst_all'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        if (\Auth::user()->can('edit product & service')) {

            $product = Products::find($id);

            $validator = \Validator::make(

                $request->all(),
                [
                    'name' => 'required|max:100|unique:products,name,' . $id . ',id,created_by,' . \Auth::user()->creatorId(),
                    'category_id' => 'nullable|exists:categories,id',
                    // 'sku_code' => 'required',
                    'price' => 'required|numeric',
                    'image' => 'file|mimes:jpg,jpeg,png|max:2048',
                    'gst_slab_master_id' => 'required|not_in:0|exists:gst_slab_masters,id',

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

            Products::where('id',$item['id'])
                ->update([
                    'stock_qty' => $item['qty']
                ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

}
