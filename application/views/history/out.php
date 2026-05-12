<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 60%;">
    <div class="card-header bg-white border-bottom px-4 py-3">
        <h4 class="m-0">Catat Barang Keluar</h4>
    </div>
    <div class="card-body p-4">
        <form action="" method="post">
            <div class="mb-3">
                <label for="electric_id" class="form-label">Nama Barang</label>
                <select id="electric_id" name="electric_id" class="form-select rounded-pill" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php foreach ($electrics as $e) : ?>
                        <option value="<?= htmlspecialchars($e['electric_id']) ?>"><?= htmlspecialchars($e['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="qty" class="form-label">Jumlah</label>
                <input id="qty" name="qty" type="number" min="1" class="form-control rounded-pill" required>
            </div>

            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"></textarea>
            </div>

            <div class="text-center">
                <button class="btn btn-danger rounded-pill" type="submit">Simpan Keluar</button>
            </div>
        </form>
    </div>
</div>
