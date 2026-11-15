<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - <?= htmlspecialchars($order['order_number']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #111827;
            background: #f3f4f6;
            padding: 24px;
        }
        .spk-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f2a4a;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .company-info h1 {
            font-size: 20px;
            color: #0f2a4a;
            font-weight: 800;
        }
        .company-info p {
            font-size: 11px;
            color: #4b5563;
            margin-top: 2px;
            line-height: 1.4;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 18px;
            color: #0f2a4a;
            text-transform: uppercase;
            font-weight: 800;
        }
        .doc-title .wo-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            margin-top: 4px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 14px;
            background: #f9fafb;
        }
        .info-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
            letter-spacing: 0.05em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: 700;
            color: #374151;
        }
        .checklist-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .checkbox-square {
            width: 14px;
            height: 14px;
            border: 1.5px solid #4b5563;
            border-radius: 2px;
            display: inline-block;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 36px;
            text-align: center;
        }
        .sig-space {
            height: 70px;
            margin-top: 8px;
            border-bottom: 1px dashed #9ca3af;
        }
        .action-bar {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .spk-container { box-shadow: none; padding: 0; }
            .action-bar { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <a href="javascript:history.back()" style="color:#2563eb; text-decoration:none; font-weight:600;">&larr; Kembali ke Work Order</a>
        <button onclick="window.print()" style="background:#0f2a4a; color:#ffffff; border:none; padding:8px 18px; border-radius:6px; font-weight:600; cursor:pointer;">
            🖨️ Cetak SPK (PDF/Print)
        </button>
    </div>

    <div class="spk-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1><?= htmlspecialchars(app_config('company.name')) ?></h1>
                <p>
                    <?= htmlspecialchars(app_config('company.address')) ?><br>
                    Telp / WhatsApp: <?= htmlspecialchars(app_config('company.phone')) ?> &bull; Email: <?= htmlspecialchars(app_config('company.email')) ?>
                </p>
            </div>
            <div class="doc-title">
                <h2>SURAT PERINTAH KERJA</h2>
                <div class="wo-badge"><?= htmlspecialchars($order['order_number']) ?></div>
                <div style="font-size:11px; color:#6b7280; margin-top:4px;">Tanggal: <?= format_date($order['scheduled_date']) ?></div>
            </div>
        </div>

        <!-- 2 Columns: Customer & AC Unit -->
        <div class="grid-2">
            <div class="info-box">
                <div class="info-title">1. Data Pelanggan</div>
                <div style="font-size:14px; font-weight:700; color:#0f2a4a;"><?= htmlspecialchars($order['customer_name']) ?></div>
                <div style="font-size:12px; margin-top:2px;">No. HP / WA: <strong><?= htmlspecialchars($order['customer_phone']) ?></strong></div>
                <div style="font-size:12px; margin-top:4px; line-height:1.4;">
                    Alamat: <?= htmlspecialchars($order['address_text'] ?: $order['customer_address']) ?>
                </div>
            </div>

            <div class="info-box">
                <div class="info-title">2. Data Unit AC</div>
                <?php if (!empty($order['ac_brand'])): ?>
                    <div style="font-size:14px; font-weight:700; color:#0f2a4a;">
                        <?= htmlspecialchars($order['ac_brand']) ?> &bull; <?= htmlspecialchars($order['capacity_pk']) ?>
                    </div>
                    <div style="font-size:12px; margin-top:2px;">
                        Tipe: <?= htmlspecialchars($order['ac_model'] ?? 'Split Wall') ?> &bull; Freon: <?= htmlspecialchars($order['refrigerant_type'] ?? 'R32') ?>
                    </div>
                    <div style="font-size:12px; margin-top:4px;">
                        Lokasi Unit: <strong><?= htmlspecialchars($order['room_location'] ?? 'Dalam Ruangan') ?></strong>
                    </div>
                <?php else: ?>
                    <div style="font-size:13px; color:#4b5563;">Unit Baru / Pemeriksaan Multi-unit</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Technicians & Schedule -->
        <div class="info-box" style="margin-bottom:20px;">
            <div class="info-title">3. Penugasan Teknisi & Waktu Pelaksanaan</div>
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; font-size:12px;">
                <div>
                    Jadwal Kunjungan: <strong><?= format_date($order['scheduled_date']) ?>, Pukul <?= htmlspecialchars($order['scheduled_time']) ?> WIB</strong>
                </div>
                <div>
                    Teknisi Bertugas:
                    <?php if (empty($technicians)): ?>
                        <em>Belum ditetapkan</em>
                    <?php else: ?>
                        <strong><?= implode(', ', array_map(fn($t) => $t['full_name'] . ' (' . ($t['is_lead'] ? 'Ketua' : 'Asisten') . ')', $technicians)) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Complaints & Required Services Table -->
        <div style="margin-bottom:20px;">
            <div class="info-title">4. Keluhan & Daftar Layanan yang Diminta</div>
            <div style="background:#fef3c7; border:1px solid #fde68a; padding:10px 12px; border-radius:4px; font-size:12px; margin-bottom:12px; color:#92400e;">
                <strong>Keluhan Pelanggan:</strong> <?= nl2br(htmlspecialchars($order['complaints'])) ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Deskripsi Layanan Servis</th>
                        <th style="width:60px; text-align:center;">Qty</th>
                        <th style="width:140px; text-align:right;">Estimasi Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr><td colspan="4" style="text-align:center;">Layanan umum / pemeriksaan awal lapangan</td></tr>
                    <?php else: ?>
                        <?php foreach ($services as $idx => $s): ?>
                            <tr>
                                <td style="text-align:center;"><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($s['service_name']) ?></strong></td>
                                <td style="text-align:center;"><?= (int)$s['quantity'] ?></td>
                                <td style="text-align:right;"><?= format_rupiah($s['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Field Inspection Checklist Template -->
        <div style="margin-bottom:24px;">
            <div class="info-title">5. Checklist Pemeriksaan Standar Lapangan</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:12px;">
                <div class="checklist-item"><span class="checkbox-square"></span> Pemeriksaan Tegangan Listrik (Volt): _______ V</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Tekanan Freon Awal: _______ PSI</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Pemeriksaan Arus Listrik (Ampere): _______ A</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Tekanan Freon Akhir: _______ PSI</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Pembersihan Filter & Talang Evaporator</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Suhu Hembus Dingin: _______ °C</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Pembersihan Kondensor Outdoor</div>
                <div class="checklist-item"><span class="checkbox-square"></span> Uji Coba Fungsi Remote & Sensor</div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div>
                <div>Petugas / Teknisi Lapangan</div>
                <div class="sig-space"></div>
                <div style="font-weight:700; font-size:12px; margin-top:4px;">( <?= !empty($technicians[0]['full_name']) ? htmlspecialchars($technicians[0]['full_name']) : 'Teknisi Bertugas' ?> )</div>
            </div>
            <div>
                <div>Pelanggan / Penerima Pekerjaan</div>
                <div class="sig-space"></div>
                <div style="font-weight:700; font-size:12px; margin-top:4px;">( <?= htmlspecialchars($order['customer_name']) ?> )</div>
            </div>
        </div>
    </div>
</body>
</html>
