<?php $this->load->view('templates/header'); ?>
<div class="container" style="margin-top:6rem">
    <h3><?= isset($title) ? $title : 'Buat Transaksi Stok'; ?></h3>
    <?= form_open('transaksi_stok/store'); ?>
    <div class="mb-3">
        <label for="electric_id" class="form-label">Barang</label>
        <select name="electric_id" id="electric_id" class="form-select" required>
            <option value="">-- Pilih Barang --</option>
            <?php foreach ($electric_items as $it): ?>
                <option value="<?= htmlspecialchars($it['electric_id']); ?>"><?= htmlspecialchars($it['nama']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="type" class="form-label">Aksi</label>
        <select name="type" id="type" class="form-select" required>
            <option value="Masuk">Masuk</option>
            <option value="Keluar">Keluar</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="qty" class="form-label">Jumlah</label>
        <input type="number" name="qty" id="qty" class="form-control" min="1" required>
    </div>
    <div class="mb-3">
        <label for="keterangan" class="form-label">Keterangan</label>
        <textarea name="keterangan" id="keterangan" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary">Simpan</button>
    <?= form_close(); ?>
</div>
<?php $this->load->view('templates/footer'); ?>
