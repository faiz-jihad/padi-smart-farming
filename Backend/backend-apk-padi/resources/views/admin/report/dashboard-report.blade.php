<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Agroklimat</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 11px;
        }

        .stats {
            width: 100%;
            margin-bottom: 20px;
        }

        .stat {
            border: 1px solid #ddd;
            padding: 12px;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 4px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
        }

        ul {
            margin-top: 5px;
            padding-left: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>LAPORAN MONITORING AGROKLIMAT & LAHAN</h1>

        <p>
            Tanggal:
            <strong>{{ now()->translatedFormat('l, d F Y') }}</strong>
            &nbsp;•&nbsp;
            Status:
            <strong>
                {{ $disasterSummary['system_status'] === 'danger'
                    ? 'SIAGA BAHAYA'
                    : 'TERKENDALI' }}
            </strong>
        </p>
    </div>

    <table class="stats" width="100%" cellspacing="10">
        <tr>
            <td class="stat" width="50%">
                <div class="stat-label">Rata-rata Suhu Lahan</div>
                <div class="stat-value">
                    {{ $liveWeather['temp'] }}°C
                </div>
            </td>

            <td class="stat" width="50%">
                <div class="stat-label">Kelembapan & Lengas</div>
                <div class="stat-value">
                    {{ $liveWeather['humidity'] }}%
                    /
                    {{ $liveWeather['soil_moisture'] }}%
                </div>
            </td>
        </tr>
    </table>

    <div>
        <div class="section-title">
            Ringkasan Radar Bencana
        </div>

        <ul>
            @foreach($disasterThreats as $t)
                <li>
                    <strong>{{ $t['title'] }}:</strong>
                    {{ $t['recommendation'] }}
                </li>
            @endforeach
        </ul>
    </div>

</body>
</html>