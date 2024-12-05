<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->get();
        return response()->json($invoices);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'details.product'])->findOrFail($id);
        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $invoice = new Invoice();
        $invoice->date = now();
        $invoice->customer_id = $request->customer_id;
        $invoice->total = 0;
        $invoice->save();

        $total = 0;
        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            $subtotal = $product->price * $productData['quantity'];
            $total += $subtotal;

            $detail = new InvoiceDetail();
            $detail->invoice_id = $invoice->id;
            $detail->product_id = $product->id;
            $detail->quantity = $productData['quantity'];
            $detail->subtotal = $subtotal;
            $detail->save();
        }

        $invoice->total = $total;
        $invoice->save();

        return response()->json($invoice);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->details()->delete();
        $invoice->delete();

        return response()->json(['message' => 'Factura eliminada con éxito']);
    }
}
