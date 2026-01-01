<table>
    <tr>
        <td colspan="7">List Barang</td>
    </tr>
    <tr>
        <td colspan="7">{{$tanggal}}</td>
    </tr>
    <tr>
        <td align="center">ID Barang</td>
        <td align="center">Nama Barang</td>
        <td align="center">Jenis</td>
        <td align="center">KMS</td>
        <td align="center">On Hand</td>
        <td align="center">Harga Jual</td>
        <td align="center">Harga Beli</td>
    </tr>
    @foreach($barang as $item)
        <tr>
            <td>{{$item->id}}</td>
            <td>{{$item->nama}}</td>
            <td>@if(!empty($item->jenis_barang))
                {{$item->jenis_barang->nama}}
                @else
                -
                @endif
            </td>
            <td>{{$item->satuan}}</td>
            <td>{{$item->stok}}</td>
            <td class="righted"><p>Rp {{number_format($item->harga_jual,2,",",".")}}</p></td>
            <td class="righted"><p>Rp @if(!empty($item->harga_beli)) 
                {{number_format($item->harga_beli->harga_beli,2,",",".")}}
                @else 0
                @endif </p></td>        
        </tr>
    @endforeach
    <tr>
    </tr>
</table>