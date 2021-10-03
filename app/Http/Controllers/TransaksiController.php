<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pelanggan;
use App\Barang;
use App\Supplier;
use App\Penjualan_detail;
use App\BarangDetail;
use App\LogBarang;
//use Surat_jalan;
use DB;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use DataTables;
use DOMPDF;
class TransaksiController extends Controller
{
    public function index(Request $request)
    {   
        if(empty($request->tanggal1))
        {
            $start = Carbon::now("Asia/Bangkok")->startOfDay();
        }
        else
        {
            $start = Carbon::createFromFormat('m/d/Y', $request->tanggal1)->startOfDay();
        }
        if(empty($request->tanggal2))
        {
            $end = Carbon::now("Asia/Bangkok")->endOfDay();
        }
        else
        {
            $end = Carbon::createFromFormat('m/d/Y', $request->tanggal2)->endOfDay();
        }
        // dd($start,$end);
    	$acc = Penjualan::whereBetween('tanggal_transaksi',[$start,$end])->orderBy('tanggal_transaksi','desc')->paginate(10);
    	return view('transaksi',['acc'=>$acc,'start'=>$request->tanggal1,'end'=>$request->tanggal2])->with('nav','transaksi');
    }
    public function tambah()
    {	
    	$pel = Pelanggan::all();
    	$bar = Barang::all();
    	return view('tambahtransaksi',['pel'=>$pel],['bar'=>$bar])->with('nav','transaksi');
    }
    public function simpan(Request $req)
    {   
        DB::beginTransaction();
        try {
            $penjualan = new Penjualan;
            $penjualan->pelanggan_id = $req->input('id_pelanggan');
            $penjualan->users_id = Auth::user()->id;
            if(is_null($req->input('tanggal_transaksi')))
            {
                $penjualan->tanggal_transaksi = Carbon::now("Asia/Bangkok");
            }
            else
            {
                $penjualan->tanggal_transaksi = Carbon::parse($req->input('tanggal_transaksi'));
            }
            if(!empty($req->input('kredit')))
            {
                $penjualan->tanggal_jatuh_tempo = Carbon::parse($req->input('jatuh_tempo'));
                $penjualan->jenis_penjualan = 1;
                $this->kredit($req->input('id_pelanggan'),$req->input('harga_akhir'));
            }
            else if(!empty($req->transfer))
            {
                $penjualan->tanggal_jatuh_tempo = null;
                $penjualan->jenis_penjualan = 4;
            }
            else if(!empty($req->giro))
            {
                $penjualan->tanggal_jatuh_tempo = null;
                $penjualan->jenis_penjualan = 3;
            }
            else
            {
                $penjualan->tanggal_jatuh_tempo = null;
                $penjualan->jenis_penjualan = 2;
            }
            $penjualan->total = $req->input('total_harga');
            $penjualan->diskon = $req->input('diskon_transaksi');
            $penjualan->potongan = $req->input('potongan_harga');
            $penjualan->total_akhir = $req->input('harga_akhir');
            $penjualan->kembalian = $req->input('uang_kembalian');
            $penjualan->uang_dibayar = $req->input('uang_tunai');
            // dd($penjualan);
            $penjualan->save();
            if(!empty($req->giro))
                app('App\Http\Controllers\Giro\CreateController')->create($penjualan->id,$req->no_seri_giro,$req->tanggal_pencairan,$req->nominal_giro); //buat record giro
            if(!empty($req->transfer))
                app('App\Http\Controllers\Transfer\CreateController')->create($penjualan->id); //buat record transfer
            $cek = $this->inputdetail($req->input('id_barang'),$req->input('jumlah_barang'),$req->input('subtotal'),$penjualan->id,$req->input('harga_barang'));
            if($cek == -1)
            {   
                DB::rollBack();
                $req->session()->flash('alert-danger', 'Terdapat Barang yang stoknya 0! silahkan tambah stok dahulu!');
                return back();
            }
            DB::commit();
            return redirect('/transaksi/detail/'.$penjualan->id.'');
        } 
        catch (Exception $e) {
            DB::rollBack();
            dd($e);
        }
    }
    public function inputdetail($barang,$jumlah,$subtotal,$penjualan,$harga_satuan)
    {   
        foreach ($barang as $key => $value) {
            // dd($value);
            if(empty($value))
                continue;
            $barang = Barang::where('id',$value)->first();
            $detail = new Penjualan_detail;
            $detail->penjualan_id = $penjualan;
            $detail->barang_id = $value;
            $detail->jumlah = $jumlah[$key];
            $detail->total_satuan = $subtotal[$key];
            $detail->harga_satuan = $harga_satuan[$key];
            // $PPN = $barang->PPN * $subtotal[$key] / 100;
            // $detail->PPN = $PPN;
            // dd($detail);
            $detail->save();
            $flag = $this->logKeuntungan($detail,2);
            if($flag == -1)
            {
                return -1;
            }
        }
        return;
    }
    public function kredit($id,$total)
    {
        $pelanggan = Pelanggan::where('id',$id)->first();
        $pelanggan->kredit = $total;
        $pelanggan->save();
        return;
    }
    public function detail($id)
    {
        //dd($id);
        $penjualan = Penjualan::where('id',$id)->first();
        $detail = Penjualan_detail::where('penjualan_id',$id)->get();
        // dd($penjualan,$detail);
        return view('detailpenjualan',['penjualan'=>$penjualan,'detail'=>$detail]);
    }
    public function delete(Request $request)
    {   
        //dd($id);
        $penjualan = Penjualan::where('id',$request->id)->first();
        $this->deletedetail($penjualan->id);
        if($penjualan->delete())
        {
            $request->session()->flash('alert-success', 'Data transaksi berhasil dihapus.');
            return redirect ('/transaksi');
        }
        else{
            $request->session()->flash('alert-danger', 'Data transaksi gagal dihapus.');
            return redirect ('/transaksi');
        }  
    }
    public function edit($id)
    {
        $penjualan = Penjualan::find($id);
        $detail = Penjualan_detail::where('penjualan_id',$penjualan->id)->get();
        $pel = Pelanggan::all();
        $bar = Barang::all();
        return view('edittransaksi',['pel'=>$pel, 'bar'=>$bar, 'penjualan'=>$penjualan, 'detail'=>$detail])->with('nav','transaksi');
    }
    public function editsimpan(Request $req)
    {   
        DB::beginTransaction();
        try {
            $penjualan = Penjualan::find($req->penjualan_id);
            $penjualan->pelanggan_id = $req->input('id_pelanggan');
            $penjualan->users_id = Auth::user()->id;
            if(is_null($req->input('tanggal_transaksi')))
            {
                $penjualan->tanggal_transaksi = Carbon::now("Asia/Bangkok");
            }
            else
            {
                $penjualan->tanggal_transaksi = Carbon::parse($req->input('tanggal_transaksi'));
            }
            if(!empty($req->input('kredit')))
            {
                $penjualan->tanggal_jatuh_tempo = Carbon::parse($req->input('jatuh_tempo'));
                $penjualan->jenis_penjualan = 1;
                $this->kredit($req->input('id_pelanggan'),$req->input('harga_akhir'));
            }
            else if(!empty($req->transfer))
            {
                $penjualan->tanggal_jatuh_tempo = null;
                $penjualan->jenis_penjualan = 4;
            }
            else if(!empty($req->giro))
            {
                $penjualan->tanggal_jatuh_tempo = null;
                $penjualan->jenis_penjualan = 3;
            }
            else
            {
                $penjualan->tanggal_jatuh_tempo = null;
                $penjualan->jenis_penjualan = 2;
            }
            $penjualan->total = $req->input('total_harga');
            $penjualan->diskon = $req->input('diskon_transaksi');
            $penjualan->potongan = $req->input('potongan_harga');
            $penjualan->total_akhir = $req->input('harga_akhir');
            $penjualan->kembalian = $req->input('uang_kembalian');
            $penjualan->uang_dibayar = $req->input('uang_tunai');
            $penjualan->save();
            $this->deletedetail($req->penjualan_id);
            if(!empty($req->giro))
                app('App\Http\Controllers\Giro\CreateController')->create($penjualan->id,$req->no_seri_giro,$req->tanggal_pencairan,$req->nominal_giro); //buat record giro
            if(!empty($req->transfer))
                app('App\Http\Controllers\Transfer\CreateController')->create($penjualan->id); //buat record transfer
            $cek = $this->inputdetail($req->input('id_barang'),$req->input('jumlah_barang'),$req->input('subtotal'),$penjualan->id,$req->input('harga_barang'));
            if($cek == -1)
            {   
                DB::rollBack();
                $req->session()->flash('alert-danger', 'Terdapat Barang yang stoknya 0! silahkan tambah stok dahulu!');
                return back();
            }
            DB::commit();
            return redirect('/transaksi/detail/'.$penjualan->id.'');
        } 
        catch (Exception $e) {
            DB::rollBack();
            $req->session()->flash('alert-danger', 'Data transaksi gagal diubah.');
                return redirect ('/transaksi');
        }
        
        
    }
    public function deletedetail($id)
    {
        $detail = Penjualan_detail::where('penjualan_id',$id)->get();
        // dd($detail);
        foreach ($detail as $item) 
        {   
            app('App\Http\Controllers\LogController')->rollBack($item,2);
            $item->delete();
        }
        return;
    }
    public function print($id)
    {   
        date_default_timezone_set('Asia/Jakarta');
        $penjualan = Penjualan::where('id',$id)->first();
        // dd(Carbon::now());
        $detail = Penjualan_detail::where('penjualan_id',$id)->get();
        $data['nomor_surat'] = $penjualan->id + 20000;
        $data['jam'] = Carbon::now()->format('H:i:s');
        if(!empty($penjualan->tanggal_jatuh_tempo))
            $data['tanggal_jatuh_tempo'] = Carbon::parse($penjualan->tanggal_jatuh_tempo)->format('d-M-Y');
        $data['tanggal'] = Carbon::parse($penjualan->tanggal_transaksi)->format('d-M-Y');
        $data['admin'] = Auth::user()->name;
        $data['penjualan'] = $penjualan;
        $data['detail'] = $detail;
        // dd($data);
        $pdf = DOMPDF::loadView('amik.print-faktur',['data'=>$data]);
        return $pdf->stream('print.pdf');
        //dd($penjualan,$detail);
        // return view('printpenjualan',['penjualan'=>$penjualan,'detail'=>$detail]);   
    }
    public function logKeuntungan($data,$flag)
    {
        // dd($data);
        $barang = Barang::where('id',$data->barang_id)->first();
        if($barang->stok <= 0)
        {
            return -1;
        }
        $query = BarangDetail::where('barang_id',$data->barang_id)
                            ->orderBy('harga_beli','asc')
                            ->get();
        // dd($query);
        $temp_jumlah_beli = $data->jumlah;
        foreach ($query as $item) 
        {   
            if($item->jumlah == 0)
            {
                continue;
            }
            else if($item->jumlah >= $temp_jumlah_beli)
            {
                $item->jumlah -= $temp_jumlah_beli;
                $item->save();
                app('App\Http\Controllers\LogController')->create($data,$flag,$item->id,$temp_jumlah_beli);
                break;    
            }
            else if($item->jumlah < $temp_jumlah_beli)
            {
                $temp_jumlah_beli -= $item->jumlah;
                app('App\Http\Controllers\LogController')->create($data,$flag,$item->id,$item->jumlah);
                $item->jumlah = 0;
                $item->save();
            }
            
        }
        return;
    }
    public function pelunasan($id)
    {
        $data['penjualan'] = Penjualan::find($id);
        return view('pelunasan',$data)->with('nav','transaksi');
    }
    public function pelunasanBayar($id,Request $request)
    {
        //dd($id,$request);
        $query = Penjualan::find($id);
        $query->terbayar += $request->bayar;
        //$query->save();
        if($query->save())
        {
            $request->session()->flash('alert-success', 'Berhasil Terbayarkan');
            return redirect ('/pelanggan/detail/'.$query->pelanggan->id.'');
        }
        else{
            $request->session()->flash('alert-danger', 'Data transaksi gagal diubah.');
            return redirect ('/transaksi');
        }
    }
    public function konfirmasiGiro($penjualan_id,$giro_id)
    {
        try {
            DB::beginTransaction();
            $giro = app('App\Http\Controllers\Giro\EditController')->konfirmasi($giro_id);
            $penjualan = Penjualan::where('id',$penjualan_id)->first();
            $penjualan->terbayar = $giro->nominal;
            $penjualan->save();
            DB::commit();
            Session::flash('alert-success', 'Berhasil Terbayarkan');
            return back();
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('alert-danger', 'Gagal Terbayarkan');
            return back();
        }
    }
    public function konfirmasiTransfer($penjualan_id,$transfer_id)
    {
        try {
            DB::beginTransaction();
            $giro = app('App\Http\Controllers\Transfer\EditController')->konfirmasi($transfer_id);
            $penjualan = Penjualan::where('id',$penjualan_id)->first();
            $penjualan->terbayar = $penjualan->total;
            $penjualan->save();
            DB::commit();
            Session::flash('alert-success', 'Berhasil Terbayarkan');
            return back();
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('alert-danger', 'Gagal Terbayarkan');
            return back();
        }
    }
    public function all(Request $request)
    {   
        // dd($request->tanggal1,$request->tanggal2);
        if(empty($request->tanggal1))
        {
            $start = Carbon::now("Asia/Bangkok")->startOfDay();
        }
        else
        {   
            // dd('abc');
            $start = Carbon::createFromFormat('m/d/Y', $request->tanggal1)->startOfDay();
        }
        if(empty($request->tanggal2))
        {
            $end = Carbon::now("Asia/Bangkok")->endOfDay();
        }
        else
        {
            $end = Carbon::createFromFormat('m/d/Y', $request->tanggal2)->endOfDay();
        }
        $penjualan = Penjualan::whereBetween('tanggal_transaksi',[$start,$end])->with('kasir','pelanggan')->orderBy('tanggal_transaksi','desc')->get();
        // dd($penjualan);
        return DataTables::of($penjualan)
            ->addColumn('nomor',function($penjualan){
                $nomor = $penjualan->id + 20000;
              return 'TA '.$nomor;
            })
            ->addColumn('kasir',function($penjualan){
                return $penjualan->kasir->name;
            })
            ->addColumn('pembeli', function($penjualan){
                return $penjualan->pelanggan->nama;
            })
            ->addColumn('jenis',function($penjualan){
                if($penjualan->jenis_penjualan == 1)
                    return 'Kredit';
                else
                    return 'Tunai';
            })
            ->addColumn('detail',function($penjualan){
                return '<td align="center" width="30px">
                            <a class="btn btn-default edit-button" 
                            href="'.url('transaksi/detail/'.$penjualan->id.'').'">
                                Detail
                            </a>
                        </td>';
            })
            ->addColumn('total', function($penjualan){
                return 'Rp'.$number = number_format($penjualan->total,0,",",".");
            })
            ->escapeColumns([])
            ->make(true);
    }
}
