<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAKTUR_TAGIHAN_<?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: #f8fafc;
        }
        .invoice-card {
            max-width: 850px;
            margin: 30px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .text-navy {
            color: #0f2a4a;
        }
        .table-items th {
            background-color: #f1f5f9;
            color: #0f2a4a;
            font-weight: 600;
        }
        .stamp {
            display: inline-block;
            padding: 6px 18px;
            text-transform: uppercase;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 2px;
            border-radius: 4px;
            transform: rotate(-5deg);
        }
        .stamp-paid {
            color: #16a34a;
            border: 3px solid #16a34a;
        }
        .stamp-unpaid {
            color: #dc2626;
            border: 3px solid #dc2626;
        }
        .stamp-partial {
            color: #d97706;
            border: 3px solid #d97706;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .invoice-card {
                box-shadow: none;
                margin: 0;
                padding: 20px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-light border-bottom">
    <button onclick="window.print()" class="btn btn-primary me-2">
        <i class="bi bi-printer"></i> Cetak / Simpan PDF
    </button>
    <button onclick="window.close()" class="btn btn-secondary">Tutup Jendela</button>
</div>

<div class="invoice-card">
    <!-- Header -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
        <div class="col-sm-7">
            <h2 class="fw-bold text-navy mb-1"><?= app_config('company.name', 'AC SERVICE PRO') ?></h2>
            <div class="text-muted small">
                <?= app_config('company.address', 'Jl. Sudirman No. 123, Jakarta') ?><br>
                WhatsApp: <?= app_config('company.phone', '0812-3456-7890') ?> | Email: info@acservicepro.id
            </div>
        </div>
        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
            <h3 class="fw-bold text-secondary mb-1">FAKTUR TAGIHAN</h3>
            <div class="fw-bold fs-5 text-navy">#<?= htmlspecialchars($invoice['invoice_number']) ?></div>
            <?php if (!empty($invoice['order_number'])): ?>
                <div class="text-muted small">No. SPK: #<?= htmlspecialchars($invoice['order_number']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Metadata & Customer -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <div class="text-muted small text-uppercase fw-bold">Ditujukan Kepada:</div>
            <h5 class="fw-bold text-navy mb-1"><?= htmlspecialchars($invoice['customer_name']) ?></h5>
            <div class="text-muted small">
                Kode Pelanggan: <?= htmlspecialchars($invoice['customer_code']) ?><br>
                Telepon / WA: <?= htmlspecialchars($invoice['customer_phone']) ?><br>
                Alamat: <?= nl2br(htmlspecialchars($invoice['customer_address'])) ?>
            </div>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <div class="mb-2">
                <span class="text-muted small">Tanggal Faktur:</span>
                <span class="fw-semibold ms-2"><?= format_date($invoice['issue_date']) ?></span>
            </div>
            <div class="mb-3">
                <span class="text-muted small">Jatuh Tempo:</span>
                <span class="fw-semibold ms-2"><?= format_date($invoice['due_date']) ?></span>
            </div>
            <div>
                <?php if ($invoice['payment_status'] === 'paid'): ?>
                    <span class="stamp stamp-paid">LUNAS</span>
                <?php elseif ($invoice['payment_status'] === 'partial'): ?>
                    <span class="stamp stamp-partial">SEBAGIAN</span>
                <?php else: ?>
                    <span class="stamp stamp-unpaid">BELUM LUNAS</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="table table-bordered table-items mb-4">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th>Deskripsi Layanan & Sparepart</th>
                <th class="text-center" style="width: 12%;">Qty</th>
                <th class="text-end" style="width: 20%;">Harga Satuan</th>
                <th class="text-end" style="width: 22%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $it): ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td>
                        <span class="fw-semibold"><?= htmlspecialchars($it['description']) ?></span>
                        <span class="badge bg-light text-secondary border ms-1"><?= $it['item_type'] === 'service' ? 'Jasa' : 'Part' ?></span>
                    </td>
                    <td class="text-center"><?= (float)$it['quantity'] ?></td>
                    <td class="text-end"><?= format_rupiah($it['unit_price']) ?></td>
                    <td class="text-end fw-semibold"><?= format_rupiah($it['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end fw-semibold">Subtotal Jasa Servis:</td>
                <td class="text-end fw-semibold"><?= format_rupiah($invoice['subtotal_services']) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end fw-semibold">Subtotal Material / Sparepart:</td>
                <td class="text-end fw-semibold"><?= format_rupiah($invoice['subtotal_materials']) ?></td>
            </tr>
            <?php if ($invoice['visit_fee'] > 0): ?>
                <tr>
                    <td colspan="4" class="text-end fw-semibold">Biaya Kunjungan:</td>
                    <td class="text-end fw-semibold"><?= format_rupiah($invoice['visit_fee']) ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($invoice['discount_amount'] > 0): ?>
                <tr>
                    <td colspan="4" class="text-end fw-semibold text-danger">Diskon / Potongan:</td>
                    <td class="text-end fw-semibold text-danger">- <?= format_rupiah($invoice['discount_amount']) ?></td>
                </tr>
            <?php endif; ?>
            <tr class="fs-5 bg-light">
                <td colspan="4" class="text-end fw-bold text-navy">TOTAL PEMBAYARAN:</td>
                <td class="text-end fw-bold text-navy"><?= format_rupiah($invoice['total_amount']) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end fw-semibold text-success">Jumlah Telah Dibayar:</td>
                <td class="text-end fw-bold text-success"><?= format_rupiah($invoice['paid_amount']) ?></td>
            </tr>
            <tr class="fs-6">
                <td colspan="4" class="text-end fw-bold text-danger">Sisa Tagihan (Piutang):</td>
                <td class="text-end fw-bold text-danger"><?= format_rupiah($invoice['balance_due']) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Payment Instructions & Banking -->
    <div class="row pt-2 mb-4">
        <div class="col-sm-7">
            <h6 class="fw-bold text-navy mb-2">Instruksi Pembayaran:</h6>
            <div class="p-3 bg-light rounded small">
                <div><strong>1. Transfer Bank BCA:</strong> 123-456-7890 a.n AC Service Pro</div>
                <div class="mt-1"><strong>2. Transfer Bank Mandiri:</strong> 987-654-3210 a.n AC Service Pro</div>
                <div class="mt-1"><strong>3. QRIS:</strong> Menerima GoPay, OVO, Dana, ShopeePay, LinkAja, & Mobile Banking.</div>
                <div class="mt-2 text-muted">Sertakan nomor invoice <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong> pada berita transfer.</div>
            </div>
        </div>
        <div class="col-sm-5 text-center mt-3 mt-sm-0">
            <div class="small fw-semibold text-muted mb-2">Scan QRIS Cepat:</div>
            <div class="d-inline-block p-2 border rounded bg-white">
                <!-- Clean SVG QR code representation -->
                <svg width="110" height="110" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100" height="100" fill="#fff"/>
                    <rect x="10" y="10" width="25" height="25" fill="#0f2a4a"/>
                    <rect x="15" y="15" width="15" height="15" fill="#fff"/>
                    <rect x="18" y="18" width="9" height="9" fill="#0f2a4a"/>
                    <rect x="65" y="10" width="25" height="25" fill="#0f2a4a"/>
                    <rect x="70" y="15" width="15" height="15" fill="#fff"/>
                    <rect x="73" y="18" width="9" height="9" fill="#0f2a4a"/>
                    <rect x="10" y="65" width="25" height="25" fill="#0f2a4a"/>
                    <rect x="15" y="70" width="15" height="15" fill="#fff"/>
                    <rect x="18" y="73" width="9" height="9" fill="#0f2a4a"/>
                    <rect x="45" y="45" width="10" height="10" fill="#06b6d4"/>
                    <rect x="40" y="15" width="10" height="5" fill="#0f2a4a"/>
                    <rect x="40" y="75" width="15" height="5" fill="#0f2a4a"/>
                    <rect x="70" y="55" width="15" height="15" fill="#0f2a4a"/>
                </svg>
            </div>
            <div class="text-muted" style="font-size: 0.75rem;">NMID: ID1029384756201</div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="row text-center mt-5 pt-3">
        <div class="col-6">
            <div class="small text-muted mb-5">Diterima Oleh,</div>
            <div class="fw-bold border-top pt-2 d-inline-block" style="min-width: 160px;">
                <?= htmlspecialchars($invoice['customer_name']) ?>
            </div>
        </div>
        <div class="col-6">
            <div class="small text-muted mb-5">Hormat Kami,</div>
            <div class="fw-bold border-top pt-2 d-inline-block" style="min-width: 160px;">
                Kasir & Administrasi
            </div>
        </div>
    </div>

    <div class="text-center text-muted small mt-5 pt-3 border-top">
        Terima kasih atas kepercayaan Anda menggunakan layanan <strong>AC Service Pro</strong>.<br>
        Garansi pengerjaan servis berlaku selama 30 hari kalender sejak tanggal pengerjaan.
    </div>
</div>

</body>
</html>
