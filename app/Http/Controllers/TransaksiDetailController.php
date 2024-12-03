<?php

namespace App\Http\Controllers;

use App\Models\TransaksiDetail;
use Illuminate\Http\Request;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class TransaksiDetailController extends Controller
{
    public function index()
    {
        $transaksidetail = TransaksiDetail::with('transaksi')->orderBy('id','DESC')->get();

        return view('transaksidetail.index', );
    }

    public function detail(Request $request)
    {
        $transaksi = Transaksi::with('transaksidetail')->findOrFail($request->id_transaksi);

        return view('transaksidetail.detail', compact('transaksi'));
    }

    public function edit($id)
    {
        $transaksidetail = TransaksiDetail::findOrFail($id);
        return view('transaksidetail.edit', );
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required|string',
            'harga_satuan' => 'required|numeric',
            'jumlah' => 'required|numeric',
        ]);

        // Gunakan transaction
        $transaksidetail = TransaksiDetail::findOrFail($id);
        $transaksi = Transaksi::find($transaksidetail->id_transaksi);
        try {
            $transaksidetail->nama_produk = $request->input('nama_produk');
            $transaksidetail->harga_satuan = $request->input('harga_satuan');
            $transaksidetail->jumlah = $request->input('jumlah');
            $transaksidetail->subtotal = $request -> harga_satuan * $request -> jumlah;


            $transaksi->total_harga = sum($transaksidetail->subtotal);
            // $transaksi->kembalian = bayar - total_harga; // hapus rumus
            $transaksi -> kembalian = $transaksi -> bayar - $transaksi -> total_harga;

            return redirect('transaksidetail/'.$transaksidetail->id_transaksi)->with('pesan', 'Berhasil mengubah data');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['Transaction' => 'Gagal menambahkan data'])->withInput();
        }
    }

    public function destroy($id)
    {
        $transaksidetail = TransaksiDetail::findOrFail($id);

        // $transaksi = Transaksi::with('transaksidetail')->findOrFail($transaksidetail->id_transaksi);
        $transaksi = Transaksi::find($transaksidetail->id_transaksi);
        $transaksi->total_harga = sum($transaksidetail->subtotal);
        // $transaksi->kembalian = bayar - total_harga;
        $transaksi -> kembalian = $transaksi -> bayar - $transaksi -> total_harga;
        $transaksi->save();

        return redirect('transaksidetail/'.$transaksidetail->id_transaksi)->with('pesan', 'Berhasil menghapus data');
    }
}
