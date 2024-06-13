<?php

namespace App\Http\Controllers;
use App\Models\Barangmasuk;
use App\Models\Barang;
use App\Models\Barangkeluar; 
use Illuminate\Http\Request;

class BarangmasukController extends Controller
{
    public function index()
    {
        $rsetBarangmasuk = Barangmasuk::latest()->paginate(10);
        return view('v_barangmasuk.index', compact('rsetBarangmasuk'));
    }

    public function create()
    {
        $barangOptions = Barang::all();
        return view('v_barangmasuk.create', compact('barangOptions'));
    }

    public function store(Request $request)
    {
        // Pesan error
        $messages = [
            'tgl_masuk.required' => 'Kolom Tanggal Masuk tidak boleh kosong.',
            'qty_masuk.required' => 'Kolom Jumlah Masuk tidak boleh kosong.',
            'qty_masuk.min' => 'Nilai Input minimal 1!',
            'barang_id.required' => 'Kolom Barang tidak boleh kosong.', 
            'barang_id.exists' => 'Barang yang dipilih tidak valid.',
        ];

        // Validasi data input
        $request->validate([
            'tgl_masuk' => 'required|date',
            'qty_masuk' => 'required|integer|min:1',
            'barang_id' => 'required|exists:barang,id',
        ], $messages);

        // Check apakah ada barang keluar dengan ID dan tanggal lebih awal
        $earliestTglKeluar = Barangkeluar::where('barang_id', $request->barang_id)
        ->where('tgl_keluar', '<', $request->tgl_masuk)
        ->min('tgl_keluar');

        // Jika ada barang keluar dengan tanggal lebih awal, perbolehkan data masuk melebihi tanggal keluar
        if ($earliestTglKeluar && strtotime($request->tgl_masuk) < strtotime($earliestTglKeluar)) {
        // Tambah record baru
        Barangmasuk::create([
            'tgl_masuk' => $request->tgl_masuk,
            'qty_masuk' => $request->qty_masuk,
            'barang_id' => $request->barang_id,
        ]);
        $message = 'Data barang masuk berhasil ditambah';
        } else {
        // Check udah ada tanggal yang sama belum
        $existingBarangMasuk = Barangmasuk::where('tgl_masuk', $request->tgl_masuk)
            ->where('barang_id', $request->barang_id)
            ->first();

        if ($existingBarangMasuk) {
            // Update record yang sudah ada
            $existingBarangMasuk->update([
                'qty_masuk' => $request->qty_masuk,
            ]);
            $message = 'Data barang masuk berhasil diperbarui';
        } else {
            // Tambah record baru
            Barangmasuk::create([
                'tgl_masuk' => $request->tgl_masuk,
                'qty_masuk' => $request->qty_masuk,
                'barang_id' => $request->barang_id,
            ]);
            $message = 'Data barang masuk berhasil ditambah';
        }
        }

        return redirect()->route('barangmasuk.index')->with('success', $message);
    }

    public function show(string $id)
    {
        $rsetBarangmasuk = Barangmasuk::find($id);
        return view('v_barangmasuk.show', compact('rsetBarangmasuk'));
    }

    public function edit(string $id)
    {
        $rsetBarangmasuk = Barangmasuk::find($id);
        $selectedbarang = Barang::find($rsetBarangmasuk->barang_id); 
        return view('v_barangmasuk.edit', compact('rsetBarangmasuk','selectedbarang'));
    }

    public function update(Request $request, string $id)
    {

        // pesan error
        $messages = [
            'tgl_masuk.required' => 'Kolom Tanggal Masuk tidak boleh kosong.',
            'qty_masuk.required' => 'Kolom Jumlah Masuk tidak boleh kosong.',
            'qty_masuk.min' => 'Nilai Input minimal 1!',
        ];

        // Validasi data input
        $request->validate([
            'tgl_masuk' => 'required|date',
            'qty_masuk' => 'required|integer|min:1',
        ], $messages);


        // Ambil data barang masuk yang akan diupdate
        $rsetBarangmasuk = Barangmasuk::find($id);

        // Ambil data barang terkait
        $barang = Barang::find($rsetBarangmasuk->barang_id);

        // Hitung tanggal keluar paling awal untuk barang yang sama
        $earliestTglKeluar = Barangkeluar::where('barang_id', $barang->id)
            ->where('tgl_keluar', '>=', $rsetBarangmasuk->tgl_masuk) // Tanggal keluar setelah atau sama dengan tanggal masuk saat ini
            ->min('tgl_keluar');

        // Jika ada tanggal keluar yang lebih awal, maka perbolehkan tanggal masuk melebihi tanggal keluar
        if ($earliestTglKeluar && strtotime($request->tgl_masuk) < strtotime($earliestTglKeluar)) {
            // Update data barang masuk
            $rsetBarangmasuk->update([
                'tgl_masuk' => $request->tgl_masuk,
                'qty_masuk' => $request->qty_masuk,
            ]);
            $message = 'Data barang masuk berhasil diperbarui';
        } else {
            // Tidak boleh melebihi tanggal keluar
            return redirect()->route('barangmasuk.index')->with(['Gagal' => 'Tanggal masuk tidak boleh melebihi tanggal keluar terdekat!']);
        }

        // Hitung perubahan stok
        $stokLama = $barang->stok - $rsetBarangmasuk->qty_masuk;
        $stokBaru = $stokLama + $request->qty_masuk;

        // Periksa stok barang setelah pembaruan
        if ($stokBaru < 0) {
            return redirect()->route('barangmasuk.index')->with(['Gagal' => 'Stok barang tidak bisa negatif setelah pembaruan!']);
        }

        // Update data barang masuk
        $rsetBarangmasuk->update([
            'tgl_masuk' => $request->tgl_masuk,
            'qty_masuk' => $request->qty_masuk,
        ]);

        // // Ambil data barang masuk yang akan diupdate
        // $rsetBarangmasuk = Barangmasuk::find($id);

        // // Ambil data barang terkait
        // $barang = Barang::find($rsetBarangmasuk->barang_id);

        // // Hitung perubahan stok
        // $stokLama = $barang->stok - $rsetBarangmasuk->qty_masuk;
        // $stokBaru = $stokLama + $request->qty_masuk;

        // // Periksa stok barang
        // if ($stokBaru < 0) {
        //     return redirect()->route('barangmasuk.index')->with(['Gagal' => 'Stok barang tidak bisa negatif setelah pembaruan!']);
        // }

        // // Update data barang masuk
        // $rsetBarangmasuk->update([
        //     'tgl_masuk' => $request->tgl_masuk,
        //     'qty_masuk' => $request->qty_masuk,
        // ]);

        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function destroy($id)
    {
        $barangmasuk = Barangmasuk::findOrFail($id);

        // cek 
        $barang = Barang::find($barangmasuk->barang_id);
        $new_stock = $barang->stok - $barangmasuk->qty_masuk;

        if ($new_stock < 0) {
            return redirect()->route('barangmasuk.index')->with('Gagal', 'Tidak dapat menghapus barang masuk karena stok akan menjadi minus.');
        }

        // Check if there are any related barangkeluar entries
        $barangkeluar = Barangkeluar::where('barang_id', $barangmasuk->barang_id)
            ->where('tgl_keluar', '>=', $barangmasuk->tgl_masuk)
            ->first();

        if ($barangkeluar) {
            return redirect()->route('barangmasuk.index')->with('Gagal', 'Tidak dapat menghapus barang masuk karena ada barang keluar terkait.');
        }

        // Hapus data barang masuk berdasarkan ID
        $barangmasuk->delete();

        return redirect()->route('barangmasuk.index')->with('success', 'Data barang masuk berhasil dihapus');
    }

    
}