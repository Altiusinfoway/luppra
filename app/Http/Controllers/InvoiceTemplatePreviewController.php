<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceTemplateRenderService;
use Illuminate\Http\Request;
use PDF;

class InvoiceTemplatePreviewController extends Controller
{
    public function __construct(private InvoiceTemplateRenderService $invoiceTemplateRenderService)
    {
    }

    public function preview(Request $request, $order)
    {
        $order = $this->resolveOrderFromRoute($order);
        $viewData = $this->invoiceTemplateRenderService->buildViewData($order);
        $invoiceNumber = $viewData['invoice']['meta']['invoice_number'] ?? str_replace('ORDER', 'INV', (string) $order->order_number);

        return response()->view('pdf.invoice.master', array_merge($viewData, [
            'preview_mode' => true,
            'download_url' => route('orders.template-invoice.download', $order->id),
            'invoice_number' => $invoiceNumber,
        ]));
    }

    public function download(Request $request, $order)
    {
        $order = $this->resolveOrderFromRoute($order);
        $viewData = $this->invoiceTemplateRenderService->buildViewData($order);
        $invoiceNumber = str_replace('/', '-', $viewData['invoice']['meta']['invoice_number'] ?? str_replace('ORDER', 'INV', (string) $order->order_number));

        $pdf = PDF::loadView('pdf.invoice.master', array_merge($viewData, [
            'preview_mode' => false,
            'download_url' => '',
            'invoice_number' => $invoiceNumber,
        ]));

        return $pdf->download('template-invoice-' . $invoiceNumber . '.pdf');
    }

    private function resolveOrderFromRoute($routeValue): Order
    {
        $user = auth()->user();
        $value = trim((string) $routeValue);

        $query = Order::query();

        if ($user && $user->type === 'Sales') {
            $query->where('user_id', $user->id);
        } elseif ($user) {
            $query->where('created_by', $user->creatorId());
        }

        if (is_numeric($value)) {
            return (clone $query)->whereKey((int) $value)->firstOrFail();
        }

        $normalizedOrderNumber = str_replace('INVOICE-', 'ORDER-', $value);

        return (clone $query)
            ->where(function ($q) use ($value, $normalizedOrderNumber) {
                $q->where('order_number', $value)
                    ->orWhere('order_number', $normalizedOrderNumber);
            })
            ->firstOrFail();
    }
}
