<?php
view('layout.header', ['title' => $title]);
view('layout.sidebar');
?>

<div class="content-wrapper">
    <div class="page-header mb-4">
        <div class="text-muted small">
            <a href="<?= url('expenses') ?>" class="text-decoration-none">Pengeluaran</a> / Catat Baru
        </div>
        <h1 class="h3 font-weight-bold text-navy mb-0">Catat Pengeluaran Kas Baru</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= url('expenses') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori Pengeluaran <span class="text-danger">*</span></label>
                                <select name="expense_category" class="form-select" required>
                                    <option value="operasional">Operasional Rutin Kantor</option>
                                    <option value="bensin_transport">BBM & Transport Teknisi</option>
                                    <option value="gaji_komisi">Gaji & Insentif Teknisi</option>
                                    <option value="sewa_kantor">Sewa Tempat & Gudang</option>
                                    <option value="listrik_air">Listrik, Air & Internet</option>
                                    <option value="peralatan_alat">Beli Tool & Peralatan Kerja</option>
                                    <option value="lain_lain">Lain-lain</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sumber Rekening Kas/Bank <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select" required>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= $acc['id'] ?>"><?= htmlspecialchars($acc['name']) ?> (Saldo: <?= format_rupiah($acc['balance']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jumlah Pengeluaran (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" placeholder="Contoh: 250000" min="100" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Penerima Dana / Pihak Ketiga <span class="text-danger">*</span></label>
                            <input type="text" name="recipient" class="form-control" placeholder="Contoh: SPBU Pertamina / PLN / Pemilik Ruko" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Keterangan Pengeluaran <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Rincian peruntukan pengeluaran..." required></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= url('expenses') ?>" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="check" class="icon-sm me-1"></i> Simpan Pengeluaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php view('layout.footer'); ?>
