<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\SpankoController;
use App\Http\Controllers\SalesTargetController;
use App\Http\Controllers\PayRollController;
use App\Http\Controllers\WorkingHoursController;
use App\Http\Controllers\LeaveRuleController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeadFollowUpController;
use App\Http\Controllers\SalesEmployeeTarget;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\getDataController;
use App\Http\Controllers\BankAccountDetailController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\WhatsappBotController;
use App\Http\Controllers\TenantAdminController;
use App\Http\Controllers\LandlordInvoiceTemplateController;
use App\Http\Controllers\CompanyInvoiceTemplateController;
use App\Http\Controllers\InvoiceTemplatePreviewController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\RazorpaySettingsController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\GoogleSheetImportController;
use App\Http\Controllers\RegionsController;
use App\Http\Controllers\FacebookController;
use Illuminate\Support\Facades\DB;

Auth::routes(['login' => false, 'logout' => false]);

require __DIR__ . '/auth.php';

// Public pre-login frontend removed: send guests directly to authentication.
Route::redirect('/', '/login')->name('website.home');
Route::redirect('/website', '/login');
Route::redirect('/features', '/login');
Route::redirect('/workflow', '/login');
Route::redirect('/integrations', '/login');
Route::redirect('/pricing', '/login');
Route::post('/website/checkout/draft', [PublicWebsiteController::class, 'saveDraft'])->name('website.checkout.draft');
Route::post('/website/checkout/status', [PublicWebsiteController::class, 'updateCheckoutStatus'])->name('website.checkout.status');
Route::get('/website/checkout/status', [PublicWebsiteController::class, 'checkoutStatus'])->name('website.checkout.status.poll');
Route::post('/website/checkout/order', [PublicWebsiteController::class, 'createOrder'])->name('website.checkout.order');
Route::post('/website/checkout/verify', [PublicWebsiteController::class, 'verifyPayment'])->name('website.checkout.verify');
Route::get('/website/thank-you', [PublicWebsiteController::class, 'thankYou'])->name('website.thankyou');

Route::redirect('/privacy-policy', '/login')->name('website.privacy.policy');
Route::redirect('/terms-and-conditions', '/login')->name('terms.conditions');
Route::redirect('/contact-us', '/login')->name('contact.us');

//require __DIR__ . '/auth.php';
Route::get('/clear-cache', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return '✅ Route cache cleared!';
});

Route::get('/permission-reset', function () {
    Artisan::call('permission:cache-reset');
    return '✅ Permission   reset';
});


# authenticated routes
Route::group(['prefix' => '', 'middleware' => ['auth']], function () {

    Route::get('/subscription/plans', [PublicWebsiteController::class, 'home'])->name('subscription.plans');

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Role Module
    Route::resource('roles', RoleController::class);

    // User Module
    Route::resource('users', UserController::class);
    Route::get('users/{view?}', [UserController::class, 'index'])->name('users');


    // address
    Route::post('/get-states', [AddressController::class, 'getStates'])->name('get.states');
    Route::post('/get-cities', [AddressController::class, 'getCities'])->name('get.cities');

    //company profile
    Route::get('settings/{user_id}', [UserController::class, 'company_profile'])->name('settings.edit');
    Route::post('settings-update/{user_id}', [UserController::class, 'company_profile_update'])->name('settings.update');

    //user profile
    Route::get('profile/{user_id}', [UserController::class, 'user_profile'])->name('user_profile.edit');
    Route::post('profile-update/{user_id}', [UserController::class, 'user_profile_update'])->name('user_profile.update');


    // Settings
    Route::prefix('setting')->as('setting.')->group(function () {

        // Leads
        Route::get('lead/{type?}', [SettingsController::class, 'lead'])->name('lead.index');
        Route::post('lead/save', [SettingsController::class, 'lead_save'])->name('lead.save');
        Route::get('lead/edit/{type}/{id}', [SettingsController::class, 'lead_edit'])->name('lead.edit');
        Route::post('lead/update/{type}/{id}', [SettingsController::class, 'lead_update'])->name('lead.update');
        Route::get('lead/delete/{type}/{id}', [SettingsController::class, 'destroy'])->name('lead.delete');

        // Orders
        Route::get('order', [SettingsController::class, 'order'])->name('order.index');
        Route::post('order/save', [SettingsController::class, 'order_save'])->name('order.save');
        Route::get('order/edit/{type}/{id}', [SettingsController::class, 'order_edit'])->name('order.edit');
        Route::post('order/update/{type}/{id}', [SettingsController::class, 'order_update'])->name('order.update');
        Route::get('order/delete/{type}/{id}', [SettingsController::class, 'order_destroy'])->name('order.delete');

        // Invoice View Settings
        Route::get('invoice-view', [InvoiceController::class, 'layoutSettings'])->name('invoice.view');
        Route::post('invoice-view/save', [InvoiceController::class, 'layoutSettingsUpdate'])->name('invoice.save');

        // Terms & Conditions
        Route::get('terms-and-conditions', [SettingsController::class, 'terms'])->name('terms.index');
        Route::post('terms-and-conditions/save', [SettingsController::class, 'terms_save'])->name('terms.save');

        // WhatsApp Bot Settings
        Route::get('whatsapp-bot', [WhatsappBotController::class, 'index'])->name('whatsapp-bot.index');
        Route::post('whatsapp-bot/save', [WhatsappBotController::class, 'save'])->name('whatsapp-bot.save');
        Route::post('whatsapp-bot/knowledge', [WhatsappBotController::class, 'storeKnowledge'])->name('whatsapp-bot.knowledge.store');
        Route::post('whatsapp-bot/knowledge/{id}', [WhatsappBotController::class, 'updateKnowledge'])->name('whatsapp-bot.knowledge.update');
        Route::get('whatsapp-bot/knowledge/delete/{id}', [WhatsappBotController::class, 'deleteKnowledge'])->name('whatsapp-bot.knowledge.delete');

        // Landlord Invoice Templates
        Route::get('invoice-templates', [LandlordInvoiceTemplateController::class, 'index'])->name('invoice-templates.index');
        Route::get('invoice-templates/create', [LandlordInvoiceTemplateController::class, 'create'])->name('invoice-templates.create');
        Route::post('invoice-templates/store', [LandlordInvoiceTemplateController::class, 'store'])->name('invoice-templates.store');
        Route::get('invoice-templates/{invoiceTemplate}', [LandlordInvoiceTemplateController::class, 'show'])->name('invoice-templates.show');
        Route::get('invoice-templates/{invoiceTemplate}/edit', [LandlordInvoiceTemplateController::class, 'edit'])->name('invoice-templates.edit');
        Route::post('invoice-templates/{invoiceTemplate}/update', [LandlordInvoiceTemplateController::class, 'update'])->name('invoice-templates.update');

        // Company Invoice Template Selection
        Route::get('company-invoice-templates', [CompanyInvoiceTemplateController::class, 'index'])->name('company-invoice-templates.index');
        Route::get('company-invoice-templates/{invoiceTemplate}', [CompanyInvoiceTemplateController::class, 'show'])->name('company-invoice-templates.show');
        Route::post('company-invoice-templates/{invoiceTemplate}/select', [CompanyInvoiceTemplateController::class, 'select'])->name('company-invoice-templates.select');

        // Razorpay Settings (Super Admin)
        Route::get('razorpay', [RazorpaySettingsController::class, 'index'])->name('razorpay.index');
        Route::post('razorpay', [RazorpaySettingsController::class, 'save'])->name('razorpay.save');
        Route::get('razorpay/transactions', [PublicWebsiteController::class, 'transactions'])->name('razorpay.transactions');

        // Taxes
        Route::get('taxes', [SettingsController::class, 'taxes'])->name('taxes');
        Route::post('taxes/save', [SettingsController::class, 'tax_save'])->name('tax.save');
        Route::get('taxes/edit/{type}/{id}', [SettingsController::class, 'tax_edit'])->name('tax.edit');
        Route::post('taxes/update/{type}/{id}', [SettingsController::class, 'tax_update'])->name('tax.update');
        Route::get('taxes/delete/{type}/{id}', [SettingsController::class, 'tax_destroy'])->name('tax.delete');
    });

    // Products Management
    Route::post('get-selected-product', [ProductController::class, 'getSelectedProducts'])->name('products.get-selected-product');
    Route::get('/product-create-quick', [ProductController::class, 'product_quick_create'])->name('products.quick_create'); //quote section
    Route::post('/product-store-quick', [ProductController::class, 'product_quick_store'])->name('products.quick_store'); //quote section

    //bulk product add excel
    Route::post('/get-excel-headers', [ProductController::class, 'get_header'])->name('products.get_header'); //header get from excel
    Route::post('product/excel_preview', [ProductController::class, 'excel_preview'])->name('products.excel-preview');
    Route::post('/product/upload', [ProductController::class, 'uploadProducts'])->name('products.upload');
    Route::get('/product/check-product-job', function (Request $request) {
        $key = $request->get('job_key');
        return response()->json(['status' => Cache::get($key, 'pending')]);
    })->name('products.job.status');
    Route::get('product/upload-excel-product', [ProductController::class, 'upload_excel_product'])->name('products.upload_excel_product');

    Route::resource('category', CategoryController::class);

    Route::post('/bulk-stock-update',[ProductController::class,'bulkStockUpdate'])->name('products.bulk_stock_update');
    Route::get('products/data', [ProductController::class, 'data'])->name('products.data');
    Route::get('products/{product}/marketplace', [ProductController::class, 'marketplace'])->name('products.marketplace');
    Route::patch('products/{product}/marketplace', [ProductController::class, 'updateMarketplace'])->name('products.marketplace.update');
    Route::get('products/{product}/marketplace/create-listing', [ProductController::class, 'createMarketplaceListing'])->name('products.marketplace.listings.create');
    Route::post('products/{product}/marketplace/listings', [ProductController::class, 'storeMarketplaceListing'])->name('products.marketplace.listings.store');
    Route::get('products/{product}/marketplace/listings/{listing}/edit', [ProductController::class, 'editMarketplaceListing'])->name('products.marketplace.listings.edit');
    Route::patch('products/{product}/marketplace/listings/{listing}', [ProductController::class, 'updateMarketplaceListing'])->name('products.marketplace.listings.update');
    Route::delete('products/{product}/marketplace/listings/{listing}', [ProductController::class, 'destroyMarketplaceListing'])->name('products.marketplace.listings.destroy');
    Route::resource('products', ProductController::class);

    // Get Comman get
    Route::prefix('get')->as('get.')->group(function () {

        Route::get('/units/{type}', [getDataController::class, 'getUnits'])->name('units');
    });


    // Lead Management
    Route::prefix('leads')->group(function () {

        Route::get('/', [LeadController::class, 'index'])->name('leads.index'); // Grid View
        Route::get('/list/{slug?}', [LeadController::class, 'list'])->name('leads.list'); // List View
        Route::get('/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/save', [LeadController::class, 'store'])->name('leads.save');
        Route::get('/get-more-product', [LeadController::class, 'getMoreProduct'])->name('leads.product.add-more');
        Route::get('/get_lead_fetch', [LeadController::class, 'get_lead_fetch'])->name('leads.get_lead_fetch'); // lead section lead fetch model
        Route::get('fetch', [LeadController::class, 'fetchLeads'])->name('leads.fetch_leads'); //store

        Route::get('{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('{lead}/update', [LeadController::class, 'update'])->name('leads.update');
        Route::get('{id}', [LeadController::class, 'view'])->name('leads.view');
        Route::post('{id}/attachments', [LeadController::class, 'uploadAttachment'])->name('leads.attachments.upload');
        Route::get('{id}/attachments', [LeadController::class, 'listAttachments'])->name('leads.attachments.list');
        Route::get('{id}/attachments/{callId}/download', [LeadController::class, 'downloadAttachment'])->name('leads.attachments.download');

        Route::get('{lead}/products', [LeadController::class, 'products'])->name('leads.products'); // Add products on lead
        Route::post('{product}/products', [LeadController::class, 'productUpdate'])->name('leads.products.update'); // Update (price / qty) products on lead
        Route::delete('{product}/products', [LeadController::class, 'productDelete'])->name('leads.products.delete'); // Delete products on lead
        Route::post('{lead}/add-product', [LeadController::class, 'productAdd'])->name('leads.product.save');

        Route::get('{lead}/get-products', [LeadController::class, 'getProducts'])->name('leads.get-products'); // Get products list

        Route::get('{customer}/get-customer-lead-products', [LeadController::class, 'get_customer_lead_product'])->name('leads.get_customer_lead_product'); //customer last lead product

        Route::get('{lead}/get-products/{quote_id}', [LeadController::class, 'edit_getProducts'])->name('leads.edit-get-products'); // Edit product list

        Route::get('{customer}/get-customer-products/{quote_id}', [LeadController::class, 'edit_customer_getProducts'])->name('leads.edit-customer-get-products'); // Edit product list

        Route::get('{lead}/users', [LeadController::class, 'users'])->name('leads.users'); // Add users on lead
        Route::post('{lead}/users', [LeadController::class, 'usersUpdate'])->name('leads.users.save'); // Add users on lead
        Route::delete('{lead}/{user}/users', [LeadController::class, 'usersDelete'])->name('leads.users.delete'); // Delete user on lead
        Route::get('assign/{lead}/{user_id}/{slug}', [LeadController::class, 'assignTo'])->name('leads.assign.user'); // Add users on lead


        Route::get('{lead}/sources', [LeadController::class, 'sources'])->name('leads.sources'); // Add sources on lead
        Route::post('{lead}/sources', [LeadController::class, 'sourcesUpdate'])->name('leads.sources.save'); // Add sources on lead
        Route::delete('{lead}/{source}/sources', [LeadController::class, 'sourcesDelete'])->name('leads.sources.delete'); // Delete source on lead

        Route::get('{lead}/{stage}/stage/update', [LeadController::class, 'stageUpdate'])->name('leads.stage.update'); // Update stage (status)

        Route::post('lead-description/{id}', [LeadController::class, 'update_description'])->name('leads.description'); //leads desc update

        Route::get('lead-chat/{lead}', [LeadController::class, 'lead_chat'])->name('leads.chat');
        Route::post('lead-chat-save/{lead}', [LeadController::class, 'lead_chat_save'])->name('leads.chat.save');

        Route::get('lead/duplicate/{lead}', [LeadController::class, 'lead_duplicate'])->name('leads.duplicate');

        Route::get('gst/{id}', [LeadController::class, 'getGst'])->name('leads.get-gst'); //check lead gst-no exist for quote create

        Route::post('lead/upload_data', [LeadController::class, 'upload_data'])->name('leads.upload_data'); // upload excel data

        Route::post('/check-entity-phone', [App\Http\Controllers\LeadController::class, 'checkEntityPhone'])->name('check.entity.phone'); //lead create chk phone accrd cust get

        Route::get('lead/fetch-recent/{id}',[LeadController::class,'fetchRecent'])->name('leads.fetch-recent');

        //bluck upload
        Route::post('/get-excel-headers', [LeadController::class, 'get_header'])->name('leads.get_header');
        Route::post('lead/excel_preview', [LeadController::class, 'excel_preview'])->name('leads.excel-preview'); // bulk excel upload
        Route::post('/lead/excel-preview-view', [LeadController::class, 'excelPreviewView'])->name('leads.excel-preview-view'); // excel data table formate disp
        Route::post('/leads/upload', [LeadController::class, 'uploadLeads'])->name('leads.upload');
        Route::get('/leads/check-lead-job', function (Request $request) {
            $key = $request->get('job_key');
            return response()->json(['status' => Cache::get($key, 'pending')]);
        })->name('leads.job.status');
        Route::get('lead/upload-excel-lead', [LeadController::class, 'upload_excel_lead'])->name('leads.upload_excel_lead');

        //facebook lead upload
        Route::post('lead/upload-facebook-lead', [GoogleSheetImportController::class, 'import'])->name('leads.upload_fb_lead');
        // Route::post('lead/upload-facebook-lead', [LeadController::class, 'upload_facebook_leads'])->name('leads.upload_fb_lead');

        //india mart
        Route::post('lead/upload-indiamart-lead', [GoogleSheetImportController::class, 'india_mart_import'])->name('leads.india_mart_import');

    });

    //lead-follow up
    Route::prefix('follow-ups')->as('follow-ups.')->group(function () {

        Route::get('/list/{slug?}', [LeadFollowUpController::class, 'list'])->name('list');
        Route::get('/create/{slug}', [LeadFollowUpController::class, 'create'])->name('create');
        Route::post('/store/{slug}', [LeadFollowUpController::class, 'store'])->name('store');
    });

    // Quotes Management
    Route::prefix('quotes')->as('quotes.')->group(function () {

        Route::get('/', [QuoteController::class, 'index'])->name('index');
        Route::get('/create/{customer_id?}/{lead_id?}', [QuoteController::class, 'create'])->name('create');
        Route::post('/store', [QuoteController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [QuoteController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [QuoteController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [QuoteController::class, 'delete'])->name('delete');
        Route::get('/pdf/{id}', [QuoteController::class, 'pdf'])->name('pdf');

        Route::get('/edit-status/{id}', [QuoteController::class, 'edit_status'])->name('edit_status'); //quote final
        Route::post('/status-update/{id}', [QuoteController::class, 'status_update'])->name('status_update');

        Route::post('/customer-store', [QuoteController::class, 'customer_store'])->name('customer_store'); //create quote inside customer,leads generate

        Route::get('/check-customer-address/{customer_id}', [QuoteController::class, 'check_cust_address'])->name('check_cust_adr'); //lead cust address check

        //get existing customer previous price,discount (lead-id store cust-id)
        Route::get('/cust-price-discount/{lead_id}/{product_id}', [QuoteController::class, 'get_customer_price_history'])->name('get_customer_price_history');

        Route::get('/preview-invoice/{quote}', [QuoteController::class, 'previewInvoice'])->name('invoice.preview'); // Preview.
        Route::get('/invoice/files/{quote}', [QuoteController::class, 'invoiceOptions'])->name('invoice.file'); // Invoice Options
        Route::get('/invoice-new/{quote}', [QuoteController::class, 'invoice_new'])->name('invoice_new'); // Download.

        Route::get('/{id?}', [QuoteController::class, 'index'])->name('index');
    });

    //Customer Management
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/store', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/edit/{id}', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::post('/update/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::get('/view/{id}', [CustomerController::class, 'view'])->name('customers.view');
        Route::delete('/delete/{id}', [CustomerController::class, 'delete'])->name('customers.delete');
    });

    //Vendor Management
    Route::prefix('vendors')->group(function () {
        Route::get('/', [VendorController::class, 'index'])->name('vendors.index');
        Route::get('/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('/store', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('/edit/{id}', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::post('/update/{id}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('/delete/{id}', [VendorController::class, 'delete'])->name('vendors.delete');

        Route::post('get-selected-product', [VendorController::class, 'addMoreProducts'])->name('vendors.get-selected-product');
    });

    //Transport Management
    Route::prefix('transports')->group(function () {
        Route::get('/', [TransportController::class, 'index'])->name('transports.index');
        Route::get('/create', [TransportController::class, 'create'])->name('transports.create');
        Route::post('/store', [TransportController::class, 'store'])->name('transports.store');
        Route::get('/create-quick', [TransportController::class, 'quick_create'])->name('transports.quick_create');
        Route::post('/store-quick', [TransportController::class, 'quick_store'])->name('transports.quick_store');
        Route::get('/edit/{id}', [TransportController::class, 'edit'])->name('transports.edit');
        Route::post('/update/{id}', [TransportController::class, 'update'])->name('transports.update');
        Route::delete('/delete/{id}', [TransportController::class, 'delete'])->name('transports.delete');
        Route::get('/transports/address-block', [AddressController::class, 'loadAddressBlock'])->name('transports.address.block');
    });

    // Employees Management
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/edit/{id}', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::post('/update/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('/update_password/{id}', [EmployeeController::class, 'update_password'])->name('employees.update_pwd');
        Route::post('/update_bank_detail/{id}', [EmployeeController::class, 'update_bank_detail'])->name('employees.update_bank_detail');
        Route::delete('/delete/{id}', [EmployeeController::class, 'delete'])->name('employees.delete');
        Route::get('/get-designations/{department_id}', [EmployeeController::class, 'getDesignations'])->name('employees.get_designation');

        Route::get('/sales-map/{user_id}', [EmployeeController::class, 'map_index'])->name('employees.map_index');
        Route::get('map/locations/{user_id}', [EmployeeController::class, 'getLocations'])->name('employees.map_locations');
    });

    //Department Management
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/store', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/edit/{id}', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::post('/update/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/delete/{id}', [DepartmentController::class, 'delete'])->name('departments.delete');
    });

    //Designation Management
    Route::prefix('designations')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('designations.index');
        Route::get('/create', [DesignationController::class, 'create'])->name('designations.create');
        Route::post('/store', [DesignationController::class, 'store'])->name('designations.store');
        Route::get('/edit/{id}', [DesignationController::class, 'edit'])->name('designations.edit');
        Route::post('/update/{id}', [DesignationController::class, 'update'])->name('designations.update');
        Route::delete('/delete/{id}', [DesignationController::class, 'delete'])->name('designations.delete');
    });

    //Holiday Management
    Route::prefix('holidays')->group(function () {
        Route::get('/', [HolidayController::class, 'index'])->name('holidays.index');
        Route::get('/create', [HolidayController::class, 'create'])->name('holidays.create');
        Route::post('/store', [HolidayController::class, 'store'])->name('holidays.store');
        Route::get('/edit/{id}', [HolidayController::class, 'edit'])->name('holidays.edit');
        Route::post('/update/{id}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/delete/{id}', [HolidayController::class, 'delete'])->name('holidays.delete');
    });

    //Advertisement Management
    Route::prefix('advertisements')->group(function () {
        Route::get('/', [AdvertisementController::class, 'index'])->name('advertisements.index');
        Route::get('/create', [AdvertisementController::class, 'create'])->name('advertisements.create');
        Route::post('/store', [AdvertisementController::class, 'store'])->name('advertisements.store');
        Route::get('/edit/{id}', [AdvertisementController::class, 'edit'])->name('advertisements.edit');
        Route::post('/update/{id}', [AdvertisementController::class, 'update'])->name('advertisements.update');
        Route::delete('/delete/{id}', [AdvertisementController::class, 'delete'])->name('advertisements.delete');
    });

    //Leave Management
    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('leaves.index');
        Route::get('/create', [LeaveController::class, 'create'])->name('leaves.create');
        Route::post('/store', [LeaveController::class, 'store'])->name('leaves.store');
        Route::get('/edit/{id}', [LeaveController::class, 'edit'])->name('leaves.edit');
        Route::post('/update/{id}', [LeaveController::class, 'update'])->name('leaves.update');
        Route::delete('/delete/{id}', [LeaveController::class, 'delete'])->name('leaves.delete');
        Route::post('/update_status_reject/{id}', [LeaveController::class, 'update_status_reject'])->name('leaves.update_status_reject');
    });

    //Attendance Management
    Route::prefix('attendances')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::post('/store', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::get('/report', [AttendanceController::class, 'report'])->name('attendances.report');
        Route::post('/update/{id}', [AttendanceController::class, 'update'])->name('attendances.update'); // attendance report update

        Route::post('/attendance-update/{id}', [AttendanceController::class, 'attendance_update'])->name('attendances.attendance-update'); // attendance check-out

    });

    // Payments
    Route::prefix('payments')->as('payments.')->group(function () {
        Route::get('/', [PaymentsController::class, 'index'])->name('index');
        Route::get('/create', [PaymentsController::class, 'create'])->name('create');
        Route::post('/store', [PaymentsController::class, 'store'])->name('store');

        Route::get('/payment-credit', [PaymentsController::class, 'payment_credit'])->name('payment_credit');
        Route::get('/payment-debit', [PaymentsController::class, 'payment_debit'])->name('payment_debit');
        Route::get('/get-customer-transport', [PaymentsController::class, 'getDropdownData'])->name('getDropdownData');
        Route::get('/payments/get-due-amount', [PaymentsController::class, 'getEntityDueAmount'])->name('getDueAmount');
    });

    // Accounts
    Route::prefix('accounts')->as('accounts.')->group(function () {
        Route::get('/', [AccountsController::class, 'index'])->name('index');
        Route::get('/customers', [AccountsController::class, 'customers'])->name('customers');
        Route::get('/customers/{customer}/ledger', [AccountsController::class, 'customerLedger'])->name('customers.ledger');
    });

    //Order Management
    Route::prefix('orders')->as('orders.')->group(function () {

        Route::get('/', [OrdersController::class, 'index'])->name('index');
        Route::get('/unpaidSearch/{search}', [OrdersController::class, 'unpaidOrders'])->name('unpaidSearch');
        Route::get('/view/{id}', [OrdersController::class, 'view'])->name('view');

        Route::get('/collect-payment/{order}', [OrdersController::class, 'collectPayment'])->whereNumber('order')->name('collect-payment');
        Route::post('/collect-payment/{order}', [OrdersController::class, 'storecollectedPayment'])->whereNumber('order')->name('collect-payment');

        Route::get('/invoice/{order}', [OrdersController::class, 'invoice'])->whereNumber('order')->name('invoice');
        Route::post('/add-bill-number/{order}', [OrdersController::class, 'addBillNumber'])->whereNumber('order')->name('add-bill-number');

        Route::post('/orders/update-status', [OrdersController::class, 'updateStatus'])->name('update.status'); //order-status update

        Route::get('/preview-invoice/{order}', [OrdersController::class, 'previewInvoice'])->whereNumber('order')->name('invoice.preview'); // Preview.
        Route::get('/invoice/files/{order}', [OrdersController::class, 'invoiceOptions'])->whereNumber('order')->name('invoice.file'); // Invoice Options
        Route::get('/invoice-new/{order}', [OrdersController::class, 'invoice_new'])->whereNumber('order')->name('invoice_new'); // Download.
        Route::get('/template-invoice/{order}', [InvoiceTemplatePreviewController::class, 'preview'])->whereNumber('order')->name('template-invoice.preview');
        Route::get('/template-invoice/{order}/download', [InvoiceTemplatePreviewController::class, 'download'])->whereNumber('order')->name('template-invoice.download');
        Route::get('/{order}', [OrdersController::class, 'orderDetails'])->whereNumber('order')->name('detail');

    });

    // Invoice
    Route::prefix('invoices')->as('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create/{customer_id?}/{lead_id?}', [InvoiceController::class, 'create'])->name('create');
        Route::post('/store', [InvoiceController::class, 'store'])->name('store');
        Route::get('/view/{id}', [InvoiceController::class, 'view'])->name('view');
    });

    //spanko
    Route::prefix('spanko')->as('spanko.')->group(function () {
        Route::get('/', [SpankoController::class, 'index'])->name('index');
    });

    // Sales Target
    Route::prefix('sales-targets')->as('sales-targets.')->group(function () {
        Route::get('/', [SalesTargetController::class, 'index'])->name('index');
        Route::get('/create', [SalesTargetController::class, 'create'])->name('create');
        Route::post('/store', [SalesTargetController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [SalesTargetController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [SalesTargetController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [SalesTargetController::class, 'delete'])->name('delete');
    });

    Route::prefix('payrolls')->as('payrolls.')->group(function () {
        Route::get('/', [PayRollController::class, 'index'])->name('index');
        Route::get('/other-emp-payroll', [PayRollController::class, 'other_emp_payroll'])->name('other_emp_payroll');
        Route::get('/cal-emp-salary/{emp_id}/{salary}/{is_sale_emp}/{sales_bonus}', [PayRollController::class, 'cal_emp_salary'])->name('cal_emp_salary');
        Route::get('/cal-all-emp-sal', [PayRollController::class, 'cal_all_emp_sal'])->name('cal_all_emp_sal');
        Route::get('/logs/{id}', [PayRollController::class, 'payrollLogs'])->name('logs'); // View All Transactions Emp Wise.

        Route::get('/process', [PayRollController::class, 'unpaidPayments'])->name('process');
        Route::get('/pay/{selected}', [PayRollController::class, 'pay'])->name('pay');
        Route::post('/pay/{selected}', [PayRollController::class, 'payroll_emp'])->name('pay');

        //payment-history
        Route::get('/download-payroll-log/{payment_id}', [PayRollController::class, 'download_payroll_attachment'])->name('download_payroll_attachment');
        Route::get('/view-payment-history/{payment_id}', [PayRollController::class, 'view_payment_history'])->name('view_payment_history');
        Route::get('/logs/filter/{emp_id}', [PayRollController::class, 'filter_payment_history'])->name('logs.filter');
        Route::get('/download-payment-history/{payment_id}', [PayRollController::class, 'download_payment_history'])->name('download_payment_history');
    });

    // target (sal emp monthly target)
    Route::prefix('sales-employee-targets')->as('sales-employee-targets.')->group(function () {
        Route::get('/create', [SalesEmployeeTarget::class, 'create'])->name('create');
        Route::post('/store', [SalesEmployeeTarget::class, 'store'])->name('store');

        //sales-target accoring its incentive get
        Route::get('/get-sales-target-incentive/{sales_target_id}', [SalesEmployeeTarget::class, 'get_sales_target_incentive'])->name('get_sales_target_incentive');
        Route::get('/get-month-lead/{user_id}/{employee_id}/{sales_target_assign_date}', [SalesEmployeeTarget::class, 'get_month_lead'])->name('get_month_lead');

        Route::get('/{slug}/{user_id?}', [SalesEmployeeTarget::class, 'index'])->name('index');
    });

    Route::prefix('working-hours')->as('working-hours.')->group(function () {
        Route::get('/', [WorkingHoursController::class, 'index'])->name('index');
        Route::get('/create', [WorkingHoursController::class, 'create'])->name('create');
        Route::post('/store', [WorkingHoursController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [WorkingHoursController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [WorkingHoursController::class, 'update'])->name('update');
    });

    Route::prefix('leave-rules')->as('leave-rules.')->group(function () {
        Route::get('/edit/{id}', [LeaveRuleController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [LeaveRuleController::class, 'update'])->name('update');
    });

    Route::prefix('leave-types')->as('leave-types.')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index'])->name('index');
        Route::get('/create', [LeaveTypeController::class, 'create'])->name('create');
        Route::post('/store', [LeaveTypeController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [LeaveTypeController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [LeaveTypeController::class, 'update'])->name('update');
    });

    // bank account detail
    Route::prefix('bank-account-details')->as('bank-account-details.')->group(function () {
        Route::get('/', [BankAccountDetailController::class, 'index'])->name('index');
        Route::get('/create', [BankAccountDetailController::class, 'create'])->name('create');
        Route::post('/store', [BankAccountDetailController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BankAccountDetailController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [BankAccountDetailController::class, 'update'])->name('update');
    });

    Route::prefix('device')->as('device.')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('index');
        Route::get('/create', [DeviceController::class, 'create'])->name('create');
        Route::post('/store', [DeviceController::class, 'store'])->name('store');
        Route::get('/{id}/qr', [DeviceController::class, 'scanQr'])->name('scan');
    });

    //chat
    Route::get('/device/chats/{uuid}', [ChatController::class, 'chats']);
    Route::post('/get-chats/{uuid}', [ChatController::class, 'chatHistory']);
    Route::post('/get-chat-messages/{uuid}', [ChatController::class, 'chatMessages'])->name('chat.messages');
    Route::post('/chats/download-media', [ChatController::class, 'downloadMedia'])->name('chat.download-media');
    Route::post('/send-message/{uuid}', [ChatController::class, 'sendMessage'])->name('chat.send-message');

    //single send or custom text routes
    Route::get('/sent-text-message', [ChatController::class, 'index'])->name('demo');
    Route::post('/sent-whatsapp-custom-text/{type}', [ChatController::class, 'sentCustomText'])->name('sent.customtext');
    Route::get('/bulk-message', [ChatController::class, 'bulkMessage'])->name('bulk-message.index');
    Route::post('/bulk-message/send', [ChatController::class, 'sendBulkMessage'])->name('bulk-message.send');

    Route::post('create-session/{id}', [DeviceController::class, 'getQr']);
    Route::post('check-session/{id}', [DeviceController::class, 'checkSession']);
    Route::post('/logout-session/{id}', [DeviceController::class, 'logoutSession']);


    Route::prefix('reports')->as('reports.')->group(function () {
        Route::get('/sales-outstanding-report', [ReportController::class, 'sales_outstanding_report'])->name('sales_outstanding_report');
        Route::get('/total-sales', [ReportController::class, 'total_sale'])->name('total_sale');
        Route::get('/customer-sales', [ReportController::class, 'customer_sales'])->name('customer_sales');
        Route::get('/customer-type', [ReportController::class, 'customer_type'])->name('customer_type');
        Route::get('/income-expense-report', [ReportController::class, 'income_expense_report'])->name('income_expense_report');
        Route::get('/user-login-report', [ReportController::class, 'user_login_report'])->name('user_login_report');
        Route::get('/sales-analytics', [ReportController::class, 'sales_analytics'])->name('sales_analytics');
        Route::get('/sales-person-performance', [ReportController::class, 'sales_person_performance'])->name('sales_person_performance');
        Route::get('/product-sales-analysis', [ReportController::class, 'product_sales_analysis'])->name('product_sales_analysis');
    });


    // Address Management (quote,invoice section)
    Route::prefix('addresses')->as('addresses.')->group(function(){
        Route::get('/create/{type}/{company_id}/{id?}',[AddressController::class,'create'])->name('create');
        Route::get('/fetch/{entity_id}/{billing?}/{shipping?}',[AddressController::class,'fetchAddresses'])->name('fetch');
        Route::post('/store/{type}/{company_id}/{id?}',[AddressController::class,'store'])->name('store');
        Route::get('addresses/customer-ids/{company_id}',[AddressController::class, 'getCustAddress'])->name('get_cust_address');
    });

    //
    Route::prefix('regions')->as('regions.')->group(function () {

        Route::controller(RegionsController::class)->prefix('countries')->as('countries.')->group(function () {

            Route::get('/', 'countries')->name('index');
            Route::get('create', 'createCountry')->name('create');
            Route::post('store', 'storeCountry')->name('store');
            Route::get('edit/{country}', 'editCountry')->name('edit');
            Route::post('update/{id}', 'updateCountry')->name('update');
            // Route::delete('delete/{id}', 'deleteCountry')->name('delete');

        });

        Route::controller(RegionsController::class)->prefix('states')->as('states.')->group(function () {

            Route::get('/', 'states')->name('index');
            Route::get('/list/{id}', 'fetchState')->name('list');
            Route::get('create', 'createState')->name('create');
            Route::post('store', 'storeState')->name('store');
            Route::get('edit/{state}', 'editState')->name('edit');
            Route::post('update/{id}', 'updateState')->name('update');
            // Route::delete('delete/{id}', 'deleteState')->name('delete');

        });

        Route::controller(RegionsController::class)->prefix('cities')->as('cities.')->group(function () {

            Route::get('/', 'cities')->name('index');
            Route::get('create', 'createCity')->name('create');
            Route::post('store', 'storeCity')->name('store');
            Route::get('edit/{city}', 'editCity')->name('edit');
            Route::post('update/{id}', 'updateCity')->name('update');
            // Route::delete('delete/{id}', 'deleteState')->name('delete');

        });

    });


    //facebook lead
    Route::prefix('facebooks')->as('facebooks.')->group(function () {
        Route::get('/', [FacebookController::class, 'create'])->name('create');
        Route::get('/get-data', [FacebookController::class, 'getData'])->name('get_data');
        Route::get('/facebook/login',[FacebookController::class,'login'])->name('login');;
    });












    Route::get("ok", function () {

        // return \App\Models\Utility::insertMonthlyAttendance(1);
    });
});

// Route::get('/facebook/callback', function () {
//  dd(request()->all());
// });

Route::get('auth/facebook/callback',[FacebookController::class,'callback']);

Route::get('/ab', function () {
    return response()->json([
        'default_connection' => config('database.default'),
        'database' => DB::connection()->getDatabaseName(),
        'host' => config('database.connections.' . config('database.default') . '.host'),
        'username' => config('database.connections.' . config('database.default') . '.username'),
        'port' => config('database.connections.' . config('database.default') . '.port'),
    ]);
});
