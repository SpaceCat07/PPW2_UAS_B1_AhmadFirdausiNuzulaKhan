<?php

namespace App\Http\Controllers;

use App\Models\TransaksiDetail;
use Illuminate\Http\Request;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::orderBy('tanggal_pembelian','DESC') -> get();
        // dd($transaksi);
        return view('transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        return view('transaksi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pembelian' => 'required|date',
            'bayar' => 'required|numeric',
            'nama_produk1' => 'required|string',
            'harga_satuan1' => 'required|numeric',
            'jumlah1' => 'required|numeric',
            'nama_produk2' => 'required|string',
            'harga_satuan2' => 'required|numeric',
            'jumlah2' => 'required|numeric',
            'nama_produk3' => 'required|string',
            'harga_satuan3' => 'required|numeric',
            'jumlah3' => 'required|numeric',
        ]);

        // Gunakan transaction
        try {
            $transaksi = new Transaksi();
            $transaksi->tanggal_pembelian = $request->input('tanggal_pembelian');
            $transaksi->total_harga = 0;
            $transaksi->bayar = $request->input('bayar');
            $transaksi->kembalian = 0;
            $transaksi->save();
            
            $total_harga = 0;
            for ($i = 1; $i <= 3; $i++) {
                $transaksidetail = new TransaksiDetail();
                $transaksidetail->id_transaksi = $transaksi->id;
                $transaksidetail->nama_produk = $request->input('nama_produk'.$i);
                $transaksidetail->harga_satuan = $request->input('harga_satuan'.$i);
                $transaksidetail->jumlah = $request->input('jumlah'.$i);
                $transaksidetail->subtotal = $request->input('harga_satuan'.$i) * $request->input('jumlah'.$i);
                $transaksidetail->save();
                $total_harga += $transaksidetail->subtotal;
            }
            $transaksi->total_harga = $total_harga;
            $transaksi->kembalian = $total_harga - $transaksi->bayar;
            $transaksi->save();

            return redirect('transaksidetail/'.$transaksi->id)->with('pesan', 'Berhasil menambahkan data');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['Transaction' => $e -> getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        return view('transaksi.edit',);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bayar' => 'required|numeric'
        ]);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->bayar = $request->input('bayar');
        $transaksi->kembalian = $transaksi->total_harga - $transaksi->bayar;

        return redirect('/transaksi') -> with('pesan', 'Berhasil mengubah data');
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);

        return redirect('/transaksi');
    }
}
