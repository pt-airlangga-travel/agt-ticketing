<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">
    <style>
        @page { margin: 0px; margin-top: 0px; }

        * {
            margin: 0px;
            padding: 0px;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            margin: 30px 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Roboto', sans-serif;
        }

        table tr td {
            padding: 0 4px;
            font-family: 'Roboto', sans-serif;
        }

        table tr td:last-child {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .large {
            font-size: 1.75em;
        }

        .total {
            font-weight: bold;
            color: #fb7578;
        }

        .invoice-info-container {
            font-size: 0.875em;
        }

        .invoice-info-container td {
            padding: 0;
        }
    </style>
    <style>
        table.tg {
            border-collapse: collapse;
            border-spacing: 0;
            font-family: 'Roboto', sans-serif !important;
        }

        table.tg td {
            border-color: black;
            border-style: solid;
            border-width: 2px;
            font-family: 'Roboto', sans-serif !important;
            font-size: 10px;
            overflow: hidden;
            padding: 0px 4px;
            word-break: normal;
        }

        table.tg th {
            border-color: black;
            border-style: solid;
            border-width: 2px;
            font-family: 'Roboto', sans-serif !important;
            font-size: 10px;
            font-weight: normal;
            overflow: hidden;
            padding: 0px 4px;
            word-break: normal;
        }

        .tg {
            border-color: inherit;
            text-align: left;
            vertical-align: top;
            font-family: 'Roboto', sans-serif !important;
        }
    </style>
    <style>
        table,
        table.tg {
            font-family: 'Roboto', sans-serif !important;
        }

        table.invoice-info-container td {
            vertical-align: top;
        }

        table.invoice-info-container td:last-child {
            width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        table.invoice-info-container td:nth-child(3) {
            text-align: right;
        }

        table.invoice-info-container td:first-child {
            width: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        table.invoice-info-container tr#row_4 td:first-child,
        table.invoice-info-container tr#row_5 td:first-child {
            padding-right: 0px;
            margin-right: 0px;
        }
    </style>
    <title>Invoice Hotel</title>
</head>

<body>

    <table style="width: 100%; border: none;">
        <thead>
            <tr>
                <td style="border: none; padding: 0; text-align: left;">
                    <div class="logo-container" style="position: relative">
        <div style="float: left">
            <img style="height: 32px" src="{{ $logo_agt }}" />
            <div style="width: 192px; position: relative">
                <p style="font-size: 8px">A Company Of</p>
                <img src="{{ $logo_unair }}" alt=""
                    style="
                            height: 16px;
                            position: absolute;
                            left: 60px;
                            top: 0px;
                        " />
            </div>
        </div>
        <h1
            style="
                    float: right;
                    position: absolute;
                    transform: translateY(-50%);
                ">
            INVOICE
        </h1>
    </div>

    <table class="invoice-info-container" style="margin-top: 50px">
        <tr>
            <td class="alamat" colspan="2">
                Jl. Dharmawangsa No.1 Gubeng Surabaya 60286
            </td>
            <td style="text-align: right;">Invoice No<b>:</b></td>
            <td>
                {{ $invoice }}
            </td>
        </tr>
        <tr>
            <td class="kontak" colspan="2">(031)99022433 / 081233020117</td>
            <td style="text-align: right;">Date<b>:</b> </td>
            <td style="font-family: 'Roboto', sans-serif;">
                {{ $tanggal_pemesanan }}
            </td>
        </tr>
        <tr>
            <td colspan="2">info@airlanggatravel.com</td>
            <td style="text-align: right;">Jatuh Tempo<b>:</b> </td>
            <td>
                {{ $jatuh_tempo }}
            </td>
        </tr>
        <tr>
            <td>
                NPWP
            </td>
            <td>{{ ': 0754007870606000' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr id="row_4">
            <td>
                Pemesan
            </td>
            <td>{{ ': ' . $nama_pemesan }}</td>
            <td>Pembayar<b>:</b> </td>
            <td>
                {{ $nama_pembayar }}
            </td>
        </tr>
        <tr id="row_5">
            <td>
                Unit Kerja
            </td>
            <td>{{ ': ' . $unit_kerja_pemesan }}</td>
            <td>Unit Kerja<b>:</b> </td>
            <td>
                {{ $unit_kerja_pembayar }}
            </td>
        </tr>
    </table>

                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: none; padding: 0; text-align: left;">
                    <table class="tg" style="font-weight: 700; font-family: 'Roboto', sans-serif; margin-top: 12px; margin-bottom: 12px;">
        <thead>
            <tr style="width: 100%">
                <th class=""
                    style="
                            width: 32px;
                            font-weight: 700;
                            text-align: center;
                        ">
                    NO
                </th>
                <th class="" style="text-align: center; font-weight: 700; width: 10%">
                    DATE
                </th>
                <th class="" colspan="7" style="text-align: center; font-weight: 700">
                    DESCRIPTION
                </th>
                <th class="" style="text-align: center; font-weight: 700; width: 10%">
                    LINE TOTAL
                </th>
            </tr>
        </thead>
        <tbody style="font-weight: normal; padding: 0px;">
            <tr style="font-weight: 700;">
                <td class=""></td>
                <td class="" style="text-align: center">
                    Reservation
                </td>
                <td class="" style="text-align: center; width: 10%;">
                    Penumpang ID
                </td>
                <td class="" style="text-align: center; width: 15%">
                    Guest
                </td>
                <td class="" style="text-align: center; width: 15%">
                    Hotel's Name
                </td>
                <td class="" style="text-align: center; line-height: 11px; width: 10%;">
                    <span>Number of Rooms</span>
                </td>
                <td class="" style="text-align: center; width: 20%;" colspan="2">
                    Check In
                </td>
                <td class="" style="text-align: center; width: 15%;">Check Out</td>
                <td class=""></td>
            </tr>
            @foreach ($penumpangs as $index => $penumpang)
                <tr>
                    <td class="" style="text-align: center; font-weight: 400; padding: 0px;">
                        <span>{{ $loop->iteration }}</span>
                    </td>
                    <td class="" style="padding: 0px; text-align: center;">
                        <span>{{ $tanggal_pemesanan }}</span>
                    </td>
                    <td class="" style="padding: 0px; text-align: center;">
                        <span>{{ $penumpang['penumpang_id'] }}</span>
                    </td>
                    <td class="" style="padding: 0px;">
                        <span>{{ $penumpang['nama'] }}</span>
                    </td>
                    <td class="" style="padding: 0px;font-size:12px">
                        <span>{{ $nama_hotel }}</span>
                    </td>
                    <td class="" style="padding: 0px; text-align: center;">
                        <span style="text-align: center;">{{ $jumlah_kamar }}</span>
                    </td>
                    <td class="" colspan="2" style="padding: 0px; text-align: center;font-size:12px">
                        <span>{{ $check_in }}</span>
                    </td>
                    <td class="" style="padding: 0px; text-align: center;font-size:12px">
                        <span>{{ $check_out }}</span>
                    </td>
                    <td class="" style="padding: 0px;">
                        <span>Rp.{{ number_format($harga_satuan, 0, ',', '.') }}</span>
                    </td>
                </tr>
            @endforeach
            <tr style="width: 100%; font-weight: 700;">
                <td id="total" class="" colspan="9" style="text-align: right">
                    TOTAL IDR
                </td>
                <td class="">Rp.{{ number_format($harga_total, 0, ',', '.') }}</td>
            </tr>
            <tr style="width: 100%; font-weight: 700;">
                <td id="tagihan" class="" colspan="9"
                    style="text-align: right; font-family: 'Roboto', sans-serif;">
                    Ditagihkan
                </td>
                <td class="">Rp.{{ number_format($harga_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- terbilang -->
    <table style="margin-bottom: 12px">
        <thead>
            <tr style="width: 100%">
                <td style="width: 15%">Terbilang :</td>
                <td style="text-align: left; font-weight: 700">
                    {{ $terbilang }}
                </td>
            </tr>
        </thead>
    </table>
    <table style="margin-bottom: 24px;">
        <thead>
            <tr style="width: 100%;">
                <td style="text-align: center; width: 20%">
                    Received By :
                </td>
                <td style="width: 60%"></td>
                <td style="text-align: center; width: 20%">Sales :</td>
            </tr>
        </thead>
    </table>
    <table>
        <thead>
            <tr style="width: 100%">
                <td style="width: 20%">[</td>
                <td>]</td>
                <td style="text-align: right">[</td>
                <td style="width: 20%">]</td>
            </tr>
        </thead>
    </table>

    <table style="margin-bottom: 10px;margin-top:20px">
        <thead>
            <tr style="width: 100%">
                <td style="width: 15%">Remark :</td>
                <td style="text-align: left; font-style: italic;">
                    Pembayaran transfer via Bank Mandiri (IDR) A/C: <span
                        style="font-weight: 700;">1420055001001</span>, via Bank BNI (IDR) A/C <span
                        style="font-weight: 700;">1101119540</span> A/N: PT
                    Airlangga Global Traveling
                </td>
            </tr>
            <tr style="width: 100%">
                <td style="width: 15%"></td>
                <td style="text-align: left; font-style: italic;">
                    Konfirmasi pembayaran PT.Airlangga Global Traveling (+62 8123302-0117)
                </td>
            </tr>
        </thead>
    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
