<?php

namespace App\Http\Controllers;

use App\Barang;
use App\Pelanggan;
use App\Supplier;
use DB;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function pelanggan(){
        //$query = Input::get('q');
        $nama = Pelanggan::select('nama')->get();
        $result = Pelanggan::select('id','telepon','limit','kredit')->get();
        return json_encode(['nama'=>$nama,'result'=>$result]);
    }

    public function barang()
    {
    	$nama = Barang::select('nama')->get()->toArray();
    	$result = Barang::select('id','satuan','harga_jual')->get()
                ->filter(function($result){
                    return $result->stok > 0;
                });
        // $result2 = Barang::select('id','satuan','harga_jual')->get();
        // return json_encode(['result'=>$result[0], 'result2'=>$result2[0]]);
        $barang = Barang::all()->filter(function($result){
                    return $result->stok > 0;
                });
        dd($result,$barang);
        $stok = [];
        $jumlah_barang = 0;
        foreach ($barang as $key => $item) {
            $jumlah_barang = 0;
            foreach ($item->barang_detail as $detail) {
                $jumlah_barang += $detail->jumlah;
            }
            if($jumlah_barang > 0)
            {
                array_push($stok,$jumlah_barang);
                $satuan = $item->satuan;
                // dd($jumlah_barang,$nama[$key]);
                $nama[$key]['nama'] .= ' - '.$jumlah_barang.' - '.$satuan;
            } 
        }
        // dd($nama);
    	return json_encode(['result'=>$result, 'nama'=>$nama, 'stok'=>$stok]);
    }

    public function barangBeli()
    {
        $nama = Barang::select('nama')->get()->toArray();
        $result = Barang::select('id','satuan','harga_jual')->get();
        $barang = Barang::all();
        $stok = [];
        $jumlah_barang = 0;
        foreach ($barang as $key => $item) {
            $jumlah_barang = 0;
            foreach ($item->barang_detail as $detail) {
                $jumlah_barang += $detail->jumlah;
            }
            array_push($stok,$jumlah_barang);
            $satuan = $item->satuan;
            // dd($jumlah_barang,$nama[$key]);
            $nama[$key]['nama'] .= ' - '.$jumlah_barang.' - '.$satuan;
        }
        // dd($nama);
        return json_encode(['result'=>$result, 'nama'=>$nama, 'stok'=>$stok]);
    }

    public function supplier()
    {
        $nama = Supplier::select('nama')->get();
        $result = Supplier::select('id','telepon','alamat')->get();
        return json_encode(['result'=>$result,'nama'=>$nama]);
    }
}
