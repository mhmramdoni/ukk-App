<?php

namespace App\Http\Controllers;

use App\Models\customers;
use App\Models\detail_sales;
use App\Models\products;
use App\Models\saless;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $saless = saless::with('customer', 'user', 'detail_sales')->orderBy('id','desc')->get();
        return view('module.pembelian.index', compact('saless'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = products::all();
        return view('module.pembelian.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->has('shop')) {
            return back()->with('error', 'Pilih produk terlebih dahulu!');
        }

        // Hapus data sebelumnya agar tidak terjadi duplikasi
        session()->forget('shop');

        $selectedProducts = $request->shop;

        // Pastikan data dikirim dalam bentuk array
        if (!is_array($selectedProducts)) {
            return back()->with('error', 'Format data tidak valid!');
        }

        // Simpan hanya produk yang memiliki jumlah lebih dari 0, hapus duplikasi
        $filteredProducts = collect($selectedProducts)
            ->mapWithKeys(function ($item) {
                $parts = explode(';', $item);
                if (count($parts) > 3) {
                    $id = $parts[0];
                    return [$id => $item]; // Pastikan hanya 1 produk per ID
                }
                return [];
            })
            ->values()
            ->toArray();

        // Simpan ke sesi
        session(['shop' => $filteredProducts]);

        return redirect()->route('sales.post');
    }


    public function post()
    {
        $shop = session('shop', []);
        return view('module.pembelian.detail', compact('shop'));
    }

    public function createsales(Request $request)
    {
        $request->validate([
            'total_pay' => 'required',
            'no_hp' => 'required|digits_between:10,13',
        ], [
            'total_pay.required' => 'Berapa jumlah uang yang dibayarkan?',
            'no_hp.required' => 'Nomor HP wajib diisi!',
            'no_hp.digits_between' => 'Nomor HP harus terdiri dari 10 sampai 13 digit.',
        ]);

        $newPrice = (int) preg_replace('/\D/', '', $request->total_price);
        $newPay = (int) preg_replace('/\D/', '', $request->total_pay);
        $newreturn = $newPay - $newPrice;

        if ($request->member === 'Member') {
            $existCustomer = customers::where('no_hp', $request->no_hp)->first();
            $usePoint = $request->has('use_point'); // checkbox: apakah pengguna ingin pakai poin
            $earnedPoint = 0;
            $potongan = 0;

            if ($existCustomer) {
                $customer_id = $existCustomer->id;

                // Ambil poin dari transaksi sebelumnya (sebelum transaksi ini)
                $previousPoints = $existCustomer->point;

                if ($usePoint) {
                    // Hanya gunakan poin sebelumnya
                    $potongan = min($previousPoints * 100, $newPrice);
                }

                $newPriceAfterDiscount = $newPrice - $potongan;
                $earnedPoint = floor($newPriceAfterDiscount / 100);

                // Update total poin setelah transaksi selesai
                $finalPoint = $usePoint
                    ? ($previousPoints - floor($potongan / 100)) + $earnedPoint
                    : $previousPoints + $earnedPoint;

                $existCustomer->update([
                    'point' => $finalPoint,
                ]);
            } else {
                // Customer baru — belum punya poin
                $earnedPoint = floor($newPrice / 100);
                $existCustomer = customers::create([
                    'name' => "",
                    'no_hp' => $request->no_hp,
                    'point' => $earnedPoint,
                ]);
                $customer_id = $existCustomer->id;
                $potongan = 0;
            }

            // Simpan data transaksi
            $sales = saless::create([
                'sale_date' => Carbon::now()->format('Y-m-d'),
                'total_price' => $newPrice,
                'total_pay' => $newPay,
                'total_return' => $newPay - ($newPrice - $potongan),
                'customer_id' => $customer_id,
                'user_id' => Auth::id(),
                'point' => $earnedPoint,
                'total_point' => $usePoint ? floor($potongan / 100) : 0,
            ]);




            $detailSalesData = [];

            foreach ($request->shop as $shopItem) {
                $item = explode(';', $shopItem);
                $productId = (int) $item[0];
                $amount = (int) $item[3];
                $subtotal = (int) $item[4];

                $detailSalesData[] = [
                    'sale_id' => $sales->id,
                    'product_id' => $productId,
                    'amount' => $amount,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // //menyebabkan duplikasi data
                // detail_sales::insert($detailSalesData);

                // Update stok produk di database
                $product = products::find($productId);
                if ($product) {
                    $newStock = $product->stock - $amount;
                    if ($newStock < 0) {
                        return redirect()->back()->withErrors(['error' => 'Stok tidak mencukupi untuk produk ' . $product->name]);
                    }
                    $product->update(['stock' => $newStock]);
                }
            }
            detail_sales::insert($detailSalesData);
            return redirect()->route('sales.create.member', ['id' => saless::latest()->first()->id])
                ->with('message', 'Silahkan daftar sebagai member');
        } else {
            $sales = saless::create([
                'sale_date' => Carbon::now()->format('Y-m-d'),
                'total_price' => $newPrice,
                'total_pay' => $newPay,
                'total_return' => $newreturn,
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'point' => 0,
                'total_point' => 0,
            ]);

            $detailSalesData = [];

            foreach ($request->shop as $shopItem) {
                $item = explode(';', $shopItem);
                $productId = (int) $item[0];
                $amount = (int) $item[3];
                $subtotal = (int) $item[4];

                $detailSalesData[] = [
                    'sale_id' => $sales->id,
                    'product_id' => $productId,
                    'amount' => $amount,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];



                // Update stok produk di database
                $product = products::find($productId);
                if ($product) {
                    $newStock = $product->stock - $amount;
                    if ($newStock < 0) {
                        return redirect()->back()->withErrors(['error' => 'Stok tidak mencukupi untuk produk ' . $product->name]);
                    }
                    $product->update(['stock' => $newStock]);
                }
            }
            detail_sales::insert($detailSalesData);
            return redirect()->route('sales.print.show', ['id' => $sales->id])->with('Silahkan Print');
        }

    }


    /**
     * Display the specified resource.
     */
    public function createmember($id)
    {
        $sale = saless::with('detail_sales.product')->findOrFail($id);
        // Menentukan apakah customer sudah pernah melakukan pembelian sebelumnya
        $notFirst = saless::where('customer_id', $sale->customer->id)->count() != 1 ? true : false;
        return view('module.pembelian.view-member', compact('sale','notFirst'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(saless $saless)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, saless $saless)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(saless $saless)
    {
        //
    }
}
