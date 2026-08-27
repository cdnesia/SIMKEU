<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tahun Akademik</th>
            <th>Nomor Pokok Mahasiswa</th>
            <th>Total Pembayaran</th>
            @foreach ($firstDetail as $detail)
                <th>{{ $detail['nama_bipot'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach ($pembayaran as $tahun => $npms)
            @foreach ($npms as $npm => $val)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $tahun }}</td>
                    <td>{{ $npm }}</td>
                    <td>{{ $val['total_terbayar'] }}</td>
                    @foreach ($val['detail'] as $item)
                        <td>{{ $item['nominal'] }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
