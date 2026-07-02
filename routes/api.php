<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\LocationHistoryController;
use App\Http\Controllers\Api\WhatsappBotConfigController;
use App\Http\Controllers\Api\DashboardController;

Route::post('whatsapp-bot/config', [WhatsappBotConfigController::class, 'resolve']);
Route::post('whatsapp-bot/lead-message', [WhatsappBotConfigController::class, 'captureLeadMessage']);

Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::get('get_user', [AuthController::class, 'get_user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('dashboard', [DashboardController::class, 'sales']);


    //customer
    Route::post('add_customer', [CustomerController::class, 'add_customer']);
    Route::get('customer_list', [CustomerController::class, 'get_customers']);
    Route::post('edit_customer', [CustomerController::class, 'edit_customer']);
    Route::post('check_customer_phone', [CustomerController::class, 'check_customer_phone']);


    //master
    Route::post('lead_type_list', [MasterController::class, 'lead_type_list']);
    Route::post('country_list', [MasterController::class, 'country_list']);
    Route::post('state_list', [MasterController::class, 'state_list']);
    Route::post('city_list', [MasterController::class, 'city_list']);
    Route::post('product_list', [MasterController::class, 'product_list']);
    Route::get('lead_source_list', [LeadController::class, 'get_lead_sources']);
    Route::get('lead_stage_list', [LeadController::class, 'get_lead_stages']);
    Route::get('unit_type_list', [MasterController::class, 'unit_type_list']);
    Route::post('unit_list', [MasterController::class, 'unit_list']);
    Route::post('leave_type_list', [MasterController::class, 'leave_type_list']);
    Route::post('day_options', [MasterController::class, 'day_options']);
    Route::post('holiday_list', [MasterController::class, 'holiday_list']);
    Route::post('working_hours_list', [MasterController::class, 'working_hours_list']);
    Route::post('attendance_update', [MasterController::class, 'attendance_update']);
    Route::post('attendance_list', [MasterController::class, 'attendance_list']);
    Route::post('transport_list', [MasterController::class, 'transport_list']);
    Route::post('category_list', [MasterController::class, 'category_list']);
     Route::post('gst_list', [MasterController::class, 'gst_list']);


    // lead
    Route::post('add_lead', [LeadController::class, 'add_lead']);
    Route::post('edit_lead', [LeadController::class, 'edit_lead']);
    Route::post('lead_list', [LeadController::class, 'list_lead']);
    Route::post('lead_duplicate', [LeadController::class, 'lead_duplicate']);
    Route::post('lead_detail', [LeadController::class, 'lead_detail']);
    Route::post('lead_description_update', [LeadController::class, 'lead_description_update']);
    Route::post('lead_status_update', [LeadController::class, 'lead_status_update']);
    Route::post('import_leads', [LeadController::class, 'import_leads']);


    //lead-product
    Route::post('lead_product_list', [LeadController::class, 'leadProductList']);
    Route::post('/add_lead_product', [LeadController::class, 'addLeadProduct']);
    Route::post('/edit_lead_product', [LeadController::class, 'editLeadProduct']);

    Route::post('/lead_chat_list', [LeadController::class, 'listLeadChat']);
    Route::post('/add_lead_chat', [LeadController::class, 'addLeadChat']);

    //add lead call
    Route::post('lead_call_list', [LeadController::class, 'lead_call_list']);
    Route::post('add_lead_call', [LeadController::class, 'add_lead_call']);


    //quote
    Route::post('quote_list', [QuoteController::class, 'quote_list']);
    Route::post('quote_detail', [QuoteController::class, 'quote_detail']);
    Route::post('customer_price_history_product_list', [QuoteController::class, 'customer_price_history_product_list']); // single single product add
    Route::post('customer_gst_list', [QuoteController::class, 'customer_gst_list']);
    Route::post('add_quote', [QuoteController::class, 'add_quote']);
    Route::post('edit_quote', [QuoteController::class, 'edit_quote']);
    Route::post('generate_pdf', [QuoteController::class, 'generate_pdf']);
    Route::post('quote_final', [QuoteController::class, 'quote_final']);
    Route::post('get_customer_lead_product', [QuoteController::class, 'get_customer_lead_product']); //quote create ,customer select that according product list

    //order list
    Route::post('order_list', [OrderController::class, 'order_list']);

    //follow-up
    Route::post('follow_up_list', [FollowUpController::class, 'follow_up_list']);


    // leaves
    Route::post('leave_list', [LeaveController::class, 'leave_list']);
    Route::post('add_leave', [LeaveController::class, 'add_leave']);
    Route::post('edit_leave', [LeaveController::class, 'edit_leave']);

    // sales location
    Route::post('add_location', [LocationHistoryController::class, 'add_location']);
    Route::post('location_list', [LocationHistoryController::class, 'location_list']);
});
