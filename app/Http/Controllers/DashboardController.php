<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;

class DashboardController extends Controller
{
    public function index()
    {
        $transaksi_count = TransaksiDetail::count();
        $transaksi_item = TransaksiDetail::sum('jumlah');
        $transaksi_omzet = TransaksiDetail::sum(\DB::raw('jumlah * harga_satuan'));
        return view('dashboard', compact('transaksi_count', 'transaksi_item', 'transaksi_omzet'));
    }
}
