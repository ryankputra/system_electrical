<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 1000px;">
    <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0">Detail Purchase Order: <?= htmlspecialchars($po['po_number']) ?></h4>
        <div>
            <a href="<?= site_url('po') ?>" class="btn btn-outline-secondary rounded-pill btn-sm me-2">Kembali</a>
            <?php if ($po['status'] == 'Pending'): ?>
                <a href="<?= site_url('po/receive/'.$po['id']) ?>" class="btn btn-success rounded-pill btn-sm" onclick="return confirm('Proses terima semua barang ke gudang? Tindakan ini akan menambah antrian stok (Queue) di riwayat.');">
                    <i class="fas fa-box-open"></i> Terima Semua Barang
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-4">
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('message') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-sm-4">
                <h6 class="text-muted mb-1">Nomor PO</h6>
                <p class="fw-bold mb-0"><?= htmlspecialchars($po['po_number']) ?></p>
            </div>
            <div class="col-sm-4">
                <h6 class="text-muted mb-1">Supplier</h6>
                <p class="fw-bold mb-0"><?= htmlspecialchars($po['supplier_name']) ?></p>
            </div>
            <div class="col-sm-4">
                <h6 class="text-muted mb-1">Tanggal & Status</h6>
                <p class="mb-0">
                    <?= date('d M Y', strtotime($po['order_date'])) ?> |
                    <?php if ($po['status'] == 'Pending'): ?>
                        <span class="badge bg-warning text-dark">Menunggu</span>
                    <?php else: ?>
                        <span class="badge bg-success">Selesai</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <h5 class="mb-3 border-bottom pb-2">Rincian Barang</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode/ID</th>
                        <th>Nama Barang & Spesifikasi</th>
                        <th>Jumlah Pesan</th>
                        <th>Harga Satuan (Rp)</th>
                        <th>Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; $total = 0; foreach ($details as $d): ?>
                    <?php $sub = $d['qty_ordered'] * $d['price']; $total += $sub; ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= htmlspecialchars($d['electric_id']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($d['nama']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($d['brand'] . ' | ' . $d['spesifikasi']) ?></small>
                        </td>
                        <td class="text-center"><?= $d['qty_ordered'] ?></td>
                        <td class="text-end"><?= number_format($d['price'], 2, ',', '.') ?></td>
                        <td class="text-end"><?= number_format($sub, 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL KESELURUHAN</td>
                        <td class="text-end fw-bold"><?= number_format($total, 2, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
