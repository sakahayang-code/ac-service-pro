<?php require dirname(__DIR__, 2) . '/layout/header.php'; ?>

<div class="card" style="max-width:760px; margin:0 auto;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="user-plus"></i>
            <span>Tambah Data Teknisi Baru</span>
        </div>
        <a href="/technicians" class="btn btn-outline btn-sm">Kembali</a>
    </div>

    <form method="POST" action="/technicians">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap Teknisi *</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Contoh: Rian Pratama">
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Nomor WhatsApp / HP *</label>
                <input type="tel" id="phone" name="phone" class="form-control" required placeholder="08123456789">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email (Untuk Login Aplikasi Teknisi) *</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="teknisi@acservicepro.id">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi Awal</label>
                <input type="password" id="password" name="password" class="form-control" value="Password123!">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="work_area">Wilayah Operasional Kerja</label>
                <input type="text" id="work_area" name="work_area" class="form-control" value="Jakarta Selatan, Depok">
            </div>
            <div class="form-group">
                <label class="form-label" for="commission_rate_percent">Persentase Komisi (%)</label>
                <input type="number" step="0.5" id="commission_rate_percent" name="commission_rate_percent" class="form-control" value="20.0">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="skills">Keahlian Teknis Khusus</label>
            <textarea id="skills" name="skills" class="form-control" rows="2" placeholder="Instalasi Inverter, Perbaikan Kompresor, Flushing Pipa, Troubleshooting Kelistrikan..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="certifications">Sertifikasi & Pelatihan</label>
            <input type="text" id="certifications" name="certifications" class="form-control" placeholder="Contoh: BNSP Teknisi Refrigerasi Level 2, ASISI">
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <a href="/technicians" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width:18px; height:18px;"></i>
                <span>Simpan Teknisi</span>
            </button>
        </div>
    </form>
</div>

<?php require dirname(__DIR__, 2) . '/layout/footer.php'; ?>
