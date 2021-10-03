@extends('adminlte::page')

@section('title','CV Erlangga')
<style>
 { box-sizing: border-box; }
body {
  font: 16px Arial; 
}
.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}
/*input {
  border: 1px solid transparent;
  background-color: #f1f1f1;
  padding: 10px;
  font-size: 16px;
}*/
/*input[type=text] {
  background-color: #f1f1f1;
  width: 100%;
}*/
.autocomplete-items {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 0;
  right: 0;
}
.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color: #fff; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #e9e9e9; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
</style>
@section('content')
        <div class="col-md-12">
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Transaksi</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <form class="form-horizontal" action="{{url('/transaksi/edit/simpan')}}" method="post" id="form_pesanan" autocomplete="off">
                        {{csrf_field()}}
                        <input type="hidden" name="penjualan_id" value="{{$penjualan->id}}">
                        <div class="box-body">
                            <div class="form-group">
                                <tr>
                                <label for="Pembeli" class="col-sm-2 control-label">Nama Pelanggan</label>
                                  <td>
                                    <div class="autocomplete col-sm-5">
                                      <input class="form-control" 
                                      id="myInput" type="text" name="namapelanggan" value="{{$penjualan->pelanggan->nama}}" placeholder="Nama Pelanggan">
                                      <input type="hidden" name="id_pelanggan" id="idpelanggan" value="{{$penjualan->pelanggan_id}}">
                                    </div>
                                  </td>
                                  <td>
                                  <label for="Pembeli" class="col-sm-2 control-label">No Telpon</label>
                                    <div class="autocomplete col-sm-3">
                                      <input class="form-control" 
                                      id="nohp" type="text" name="nomor" placeholder="No. Telpon" value="{{$penjualan->pelanggan->telepon}}" disabled>
                                    </div>
                                  </td>  
                                </tr>
                            </div>
                            <div class="form-group">
                                <label for="Sisa Limit" class="col-sm-2 control-label">Limit Pembeli</label>
                                @php $sisa = $penjualan->pelanggan->limit - $penjualan->pelanggan->kredit @endphp
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="sisalimit" value="{{$sisa}}" disabled>
                                </div>
                            </div>
                            <div class="form-group" id="listbarang">
                                @php $i = 0 @endphp
                                @foreach($detail as $d)
                                <div id="row_{{$i}}">
                                  @if($i == 0)<label for="NamaBarang" class="col-sm-2 control-label">Barang</label>
                                  @else
                                  <label for="NamaBarang" class="col-sm-2 control-label"></label>
                                  @endif
                                  <td>
                                    <div class="autocomplete col-sm-3">
                                      <input type="text" class="form-control barang" id="barang_{{$i}}" name="nama_barang[]" placeholder="Nama Barang" value="{{$d->barang->nama}}">
                                      <input type="hidden" id="id_barang_{{$i}}" name="id_barang[{{$i}}]" value="{{$d->barang->id}}">
                                    </div>
                                  </td>
                                  <td>
                                    <div class="col-sm-2" style="margin-left:-15px; margin-right:5px;">
                                      <input style="width:95px;" type="text" class="form-control" id="hargabarang_{{$i}}" placeholder="harga" name="harga_barang[{{$i}}]" value="{{$d->harga_satuan}}">
                                    </div>
                                  </td>
                                  <td>
                                    <label for="JumlahBarang" style="margin-left:-65px;" class="col-sm-1 control-label">Kuantitas</label>
                                    <div class="col-sm-1" style="margin-left:-10px;">
                                      <input style="width:55px;" type="text" class="form-control" id="jumlahbarang_{{$i}}" name="jumlah_barang[{{$i}}]" value="{{$d->jumlah}}">
                                    </div>
                                  </td>
                                  <td>
                                    <label for="SatuanBarang" style="margin-left:-40px;" class="col-sm-1 control-label">Satuan</label>
                                    <div class="col-sm-1" style="margin-left:-20px;">
                                      <input style="width:55px;" type="text" class="form-control" id="satuanbarang_{{$i}}" disabled value="{{$d->barang->satuan}}">
                                    </div>
                                  </td>                                  
                                  <td>
                                    <div class="col-sm-2" style="margin-left:-15px;">
                                      <input style="width:110px;" type="text" class="form-control" id="subtotal_{{$i}}" placeholder="Subtotal"
                                      disabled value="{{$d->total_satuan}}">
                                      <input type="hidden" id="subtotal_{{$i}}_sent" name="subtotal[{{$i}}]" value="{{$d->total_satuan}}">
                                    </div>
                                  </td>
                                  <td>
                                    @if($i == 0)
                                    <a style="margin-left:-32px; height:35px;" id="add" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a>
                                    @else
                                    <a style="margin-left:5px; height:35px; margin-top:-22px;" id="{{$i}}" class="btn btn-danger btn_remove_awal"><span class="glyphicon glyphicon-remove"></span></a>
                                    @endif
                                  </td>    
                                </div>
                                @php $i++; @endphp
                                @endforeach
                            </div>
                            <div class="form-group">
                                <label for="TotalHarga" class="col-sm-2 control-label">Total Harga</label>

                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="total_harga" value="{{$penjualan->total}}" disabled>
                                    <input type="hidden" id="total_harga_sent" name="total_harga">
                                </div>
                            </div>
                            <div class="form-group">
                              <label for="TotalHarga" class="col-sm-2 control-label">Promo</label>                              
                              <select class="col-sm-5"  style="height:34px; margin-left:15px;" id="pilihan_diskon">
                                <option href="#" class="dropdown-item" @if(empty($penjualan->diskon) && empty($penjualan->potongan)) selected @endif>Tidak ada</option>
                                <option href="#" class="dropdown-item" @if(!empty($penjualan->diskon)) selected @endif>Diskon</option>
                                <option href="#" class="dropdown-item" @if(!empty($penjualan->potongan)) selected @endif>Potongan Harga</option>                                
                              </select>
                            </div>
                            <div class="form-group" id="potongan" style="display:none;">
                                <label for="PotonganHarga" class="col-sm-2 control-label">Potongan Harga</label>

                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="jumlah_potongan" name="potongan_harga"  @if(!empty($penjualan->potongan)) value="{{$penjualan->potongan}}" @endif>
                                </div>
                            </div>
                            <div class="form-group" id="diskon" style="display:none;">
                                <label for="DiskonTransaksi" class="col-sm-2 control-label">Diskon</label>

                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="jumlah_diskon" name="diskon_transaksi" @if(!empty($penjualan->diskon)) value="{{$penjualan->diskon}}" @endif>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="TotalAkhir" class="col-sm-2 control-label">Harga Akhir</label>

                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="harga_akhir" disabled value="{{$penjualan->total_akhir}}">
                                    <input type="hidden" id="harga_akhir_sent" name="harga_akhir">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="CaraPembayaran" class="col-sm-2 control-label">Metode Pembayaran</label>
                                <div class="radio col-sm-3">
                                  <label><input type="radio" name="tunai" id="bayar_tunai" @if(empty($penjualan->tanggal_jatuh_tempo)) checked @endif>Tunai</label>
                                </div>
                                <div class="radio col-sm-3">
                                  <label><input type="radio" name="kredit" id="bayar_kredit" @if(!empty($penjualan->tanggal_jatuh_tempo)) checked @endif>Kredit</label>
                                </div>
                            </div>
                            <div class="form-group" id="tunai" style="display:none;">
                                <label for="TerimaTunai" class="col-sm-2 control-label">Tunai</label>

                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="uang_tunai" name="uang_tunai" value="{{$penjualan->uang_dibayar}}">
                                </div>
                            </div>
                            <div class="form-group" id="kembalian" style="display:none;">
                                <label for="UangKembalian" class="col-sm-2 control-label">Kembalian</label>

                                <div class="col-sm-10">
                                    <input type="number" class="form-control" id="uang_kembalian" disabled value="{{$penjualan->kembalian}}">
                                    <input type="hidden" id="uang_kembalian_sent" name="uang_kembalian">
                                </div>
                            </div>
                            <div class="form-group" style="display:none;" id="jatuh_tempo">
                              <label for="JatuhTempo" class="col-sm-2 control-label">Jatuh Tempo</label>
                              <div class="col-sm-10">
                                  <input type="text" name="jatuhtempo" class="form-control" id="datepicker" value="{{$penjualan->tanggal_jatuh_tempo}}">
                              </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <a href="{{url('/transaksi')}}">
                                <button type="button" class="btn btn-default">Batal</button>
                            </a>
                            <button type="submit" class="btn btn-success pull-right">Simpan</button>


                        </div>
                        <!-- /.box-footer -->
                    </form>
                </div>
            </div>
        </div>
<script>
 var j = jQuery.noConflict();
    j( function() {
        j( "#datepicker" ).datepicker();
    } );
var nama = [];
var result = [];
var detail = {!! $detail !!}
var subtotal = [], total_harga = 0, harga_akhir = 0, uang_kembalian = 0;
var k;
$(document).ready(function(){
  var i = {{count($detail)-1}};
  $('#add').click(function(){
    i++;
    $('#listbarang').append('<tr id="row_'+i+'"><td><div class="autocomplete col-sm-3" style="margin-left:177.5px;"><input style="width:236px;" type="text" class="form-control barang" id="barang_'+i+'" name="nama_barang['+i+']" placeholder="Nama Barang"><input type="hidden" id="id_barang_'+i+'" name="id_barang['+i+']"></div></td><td><div class="col-sm-2" style="margin-left:-15px; margin-right:5px;"><input style="width:95px;" type="text" class="form-control" id="hargabarang_'+i+'" name="harga_barang['+i+']" placeholder="harga"></div></td><td><label for="JumlahBarang" style="margin-left:-12px;" class="col-sm-1 control-label">Kuantitas</label><div class="col-sm-1" style="margin-left:48.5px;"><input style="width:55px;" type="text" class="form-control" id="jumlahbarang_'+i+'" name="jumlah_barang['+i+']"></div></td><td><label for="SatuanBarang" style="margin-left:-81.5px;" class="col-sm-1 control-label">Satuan</label><div class="col-sm-1" style="margin-left:-28px;"><input style="width:55px;" type="text" class="form-control" id="satuanbarang_'+i+'" name="satuan_barang['+i+']" disabled></div></td><td><div class="col-sm-2" style="margin-left:-11px;"><input style="width:110px;" type="text" class="form-control" id="subtotal_'+i+'" placeholder="Subtotal" disabled><input type="hidden" id="subtotal_'+i+'_sent" name="subtotal['+i+']"></div></td><td><a style="margin-left:5px; height:35px; margin-top:-22px;" id="'+i+'" class="btn btn-danger btn_remove"><span class="glyphicon glyphicon-remove"></span></a></td></tr>');
    autocomplete(document.getElementById('barang_'+i+''), nama, result, 'barang',i, document.getElementById('jumlahbarang_'+i+''), document.getElementById('hargabarang_'+i+''));
  });
});
$(document).on('click', '.btn_remove', function(){
  //console.log("a");
  var button_id = $(this).attr("id");
  var harga_current = $('#total_harga').val();
  var row_subtotal = $('#subtotal_'+button_id+'').val();
  subtotal.splice(button_id,1); 
  $('#total_harga').val(harga_current - row_subtotal);
  $('#total_harga_sent').val(harga_current - row_subtotal);
  $('#row_'+button_id+'').remove();
});
$(document).on('click','.btn_remove_awal', function(){
  console.log($($(this)[0]).parent());
  var button_id = $($(this)[0]).attr("id");
  var harga_current = $('#total_harga').val();
  var row_subtotal = $('#subtotal_'+button_id+'').val();
  subtotal.splice(button_id,1); 
  $('#total_harga').val(harga_current - row_subtotal);
  $('#total_harga_sent').val(harga_current - row_subtotal);
  $($(this)[0]).parent().remove();
})
$(document).ready(function(){

  $.ajax({
    type:"GET",
    dataType:"json",
    url:"/erlangga/public/search/barang/beli",
    success: function(data){
      //console.log(data);
      data.nama.forEach(function(response){
        nama.push(response.nama);
      });
      data.result.forEach(function(response){
        result.push(response);
      });
      data.stok.forEach(function(response){
        result.push(response.stok);
      });
      var j = 1;
      for(k=0;k<{{count($detail)}};k++)
      { 
        autocomplete(document.getElementById('barang_'+k+''), nama, result, 'barang',k, document.getElementById('jumlahbarang_'+k+''), document.getElementById('hargabarang_'+k+''));
        console.log(detail[k].total_satuan); 
        subtotal[k] = detail[k].total_satuan;
        j++;
      }
    }
  });
});
$(document).ready(function (){
    //var nama = [];
    $.ajax({
        type:"GET",
        dataType:"json",
        url:"/erlangga/public/search/pelanggan",
        success: function(data){
            var nama = [];
            var result = [];
            data.nama.forEach(function(response){ //callback function
                nama.push(response.nama);
            });
            data.result.forEach(function(response){
                result.push(response);
                
            });
            autocomplete(document.getElementById("myInput"), nama, result, 'pelanggan',null, null, null);
        }
    });
});

function countAll()
{
  var harga_asli = subtotal.reduce(getSum);
  if(document.getElementById("pilihan_diskon").value == "Tidak ada")
  {
    harga_akhir = subtotal.reduce(getSum);
    $('#harga_akhir').val(harga_akhir);
    $('#harga_akhir_sent').val(harga_akhir);  
  }
  else if(document.getElementById("pilihan_diskon").value == "Diskon")
  {
    $('#harga_akhir').val(harga_asli);
    $('#harga_akhir_sent').val(harga_asli);
    document.getElementById("jumlah_diskon").addEventListener("input", function(e)
    {
      harga_akhir = subtotal.reduce(getSum) - ((subtotal.reduce(getSum) * this.value) / 100);
      $('#harga_akhir').val(harga_akhir);
      $('#harga_akhir_sent').val(harga_akhir);  
    });
  }
  else
  {
    $('#harga_akhir').val(harga_asli);
    $('#harga_akhir_sent').val(harga_asli);
    document.getElementById("jumlah_potongan").addEventListener("input", function(e)
    {
      harga_akhir = subtotal.reduce(getSum) - this.value;
      $('#harga_akhir').val(harga_akhir);
      $('#harga_akhir_sent').val(harga_akhir);  
    });
  }
}

document.getElementById("bayar_tunai").addEventListener("click", function(e){
  $('#uang_tunai').val('');
  $('#uang_kembalian').val('');
  $('#bayar_kredit').prop("checked", false);
  $('#jatuh_tempo').hide();
  $('#tunai').show();
  $('#kembalian').show();
  document.getElementById("uang_tunai").addEventListener("input", function(e){
    uang_kembalian = this.value - harga_akhir;
    $('#uang_kembalian').val(uang_kembalian);
    $('#uang_kembalian_sent').val(uang_kembalian);
  });

});

$(document).ready(function(){
  if($('#bayar_kredit').is(':checked'))
  {
    $('#bayar_tunai').prop("checked", false);
    $('#jatuh_tempo').show();
    $('#tunai').hide();
    $('#kembalian').hide();
  }
  else if($('#bayar_tunai').is(':checked'))
  {
    $('#bayar_kredit').prop("checked", false);
    $('#jatuh_tempo').hide();
    $('#tunai').show();
    $('#kembalian').show();
    document.getElementById("uang_tunai").addEventListener("input", function(e){
      uang_kembalian = this.value - harga_akhir;
      $('#uang_kembalian').val(uang_kembalian);
      $('#uang_kembalian_sent').val(uang_kembalian);
    });
  }
});

document.getElementById("bayar_kredit").addEventListener("click", function(e){
  $('#uang_tunai').val('');
  $('#uang_kembalian').val('');
  $('#bayar_tunai').prop("checked", false);
  $('#jatuh_tempo').show();
  $('#tunai').hide();
  $('#kembalian').hide();
});

document.getElementById("pilihan_diskon").addEventListener("click", function(e){
  if(this.value == "Diskon")
  {
    $('#jumlah_diskon').val('');
    $('#diskon').show();
    $('#potongan').hide();
    countAll();
  }
  else if(this.value == "Potongan Harga")
  {
    $('#jumlah_potongan').val('');
    $('#diskon').hide();
    $('#potongan').show();
    countAll();
  }
  else
  {
    $('#diskon').hide();
    $('#potongan').hide();
    countAll();
  }
});

function getSum(total, num){
  return total + num;
}
//function subtotal()
function autocomplete(inp, arr, result, flag, counter, quantity, price) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  // console.log(quantity);
  //console.log(inp);
  var currentFocus, harga_jual_now, banyak;
  //console.log(inp);
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        //console.log(arr[i].substr(0,2));
        /*check if the item starts with the same letters as the text field value:*/
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          if(flag == 'pelanggan')
          {
            b.innerHTML += "<input type='hidden' data-id='"+ result[i].id + "' data-nomor= '"+ result[i].telepon + "' data-limit= '" + result[i].limit + "' data-kredit= '" + result[i].kredit + "' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              $('#nohp').val($(this.getElementsByTagName("input")[0]).data('nomor'));
              $('#sisalimit').val($(this.getElementsByTagName("input")[0]).data('limit') - $(this.getElementsByTagName("input")[0]).data('kredit'));
              $('#idpelanggan').val($(this.getElementsByTagName("input")[0]).data('id'));

              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
            });  
          }
          else if(flag == 'barang')
          {
            b.innerHTML += "<input type='hidden' data-id='"+ result[i].id + "' data-harga= '"+ result[i].harga_jual + "' data-satuan= '" + result[i].satuan + "' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) {

              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              $('#id_barang_'+counter+'').val($(this.getElementsByTagName("input")[0]).data('id'));
              $('#hargabarang_'+counter+'').val($(this.getElementsByTagName("input")[0]).data('harga'));
              $('#satuanbarang_'+counter+'').val($(this.getElementsByTagName("input")[0]).data('satuan'));
              harga_jual_now = $(this.getElementsByTagName("input")[0]).data('harga');
              $('#subtotal_'+counter+'').val(harga_jual_now * banyak);
              $('#subtotal_'+counter+'_sent').val(harga_jual_now * banyak);
              subtotal[counter] = parseInt(document.getElementById('subtotal_'+counter+'').value);
              $('#total_harga').val(subtotal.reduce(getSum));
              $('#total_harga_sent').val(subtotal.reduce(getSum));
              countAll();
              quantity.addEventListener("input", function(e){
                banyak = this.value;
                harga_jual_new = $('#hargabarang_'+counter+'').val();
                $('#subtotal_'+counter+'').val(harga_jual_new * banyak);
                $('#subtotal_'+counter+'_sent').val(harga_jual_new * banyak);
                subtotal[counter] = parseInt(document.getElementById('subtotal_'+counter+'').value);
                $('#total_harga').val(subtotal.reduce(getSum));
                $('#total_harga_sent').val(subtotal.reduce(getSum));
                countAll();
              });
              price.addEventListener("input", function(e){
                banyak = quantity.value;
                harga_jual_new = this.value;
                $('#subtotal_'+counter+'').val(harga_jual_new * banyak);
                $('#subtotal_'+counter+'_sent').val(harga_jual_new * banyak);
                subtotal[counter] = parseInt(document.getElementById('subtotal_'+counter+'').value);
                $('#total_harga').val(subtotal.reduce(getSum));
                $('#total_harga_sent').val(subtotal.reduce(getSum));
                countAll();
              });
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
            });
          }
          a.appendChild(b);
        }
      }
  });

  
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});
}
</script>
@stop