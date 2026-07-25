<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-6">
                <h3 class="m-0"><?= htmlspecialchars($title) ?></h3>
            </div>
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search Form -->
                    <form action="" method="post" class="flex-grow-1 position-relative" id="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari nomor PO, supplier, atau status..." name="keyword" value="<?= htmlspecialchars($searchKeyword ?? '', ENT_QUOTES, 'UTF-8') ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>
                        <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button top-50 translate-middle-y" id="clear-button" onclick="clearKeyword()" style="right: 5.5rem;">
                    </form>

                    <?php if ($this->session->userdata('role') === 'Staf Gudang'): ?>
                    <a href="<?= site_url('po/create') ?>" class="btn btn-primary rounded-pill px-4 flex-shrink-0">
                        <i class="fas fa-plus"></i> Buat PO Baru
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-success m-3"><?= $this->session->flashdata('message') ?></div>
    <?php endif; ?>

    <?php if (empty($purchase_orders)): ?>
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p><?= !empty($searchKeyword) ? 'Purchase Order tidak ditemukan. Coba sesuaikan kata kunci pencarian Anda.' : 'Belum ada data Purchase Order.' ?></p>
            <?php if (!empty($searchKeyword)): ?>
                <form action="" method="post" class="mt-3">
                    <input type="hidden" name="reset" value="1">
                    <button type="submit" class="btn btn-outline-secondary rounded-pill px-4">Reset Pencarian</button>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nomor PO</th>
                        <th>Tanggal Order</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th class="pe-lg-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($purchase_orders as $po): ?>
                    <tr class="text-center">
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($po['po_number']) ?></strong></td>
                        <td><?= date('d M Y', strtotime($po['order_date'])) ?></td>
                        <td><?= htmlspecialchars($po['supplier_name']) ?></td>
                        <td>
                            <?php 
                                if ($po['status'] === 'Completed') echo '<span class="badge bg-success">Selesai (Completed)</span>';
                                elseif ($po['status'] === 'Approved') echo '<span class="badge bg-primary">Diproses (Approved)</span>';
                                elseif ($po['status'] === 'Rejected') echo '<span class="badge bg-danger">Ditolak (Rejected)</span>';
                                else echo '<span class="badge bg-warning text-dark">Menunggu Approval</span>';
                            ?>
                        </td>
                        <td class="pe-lg-4">
                            <a href="<?= site_url('po/detail/'.$po['id']) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i> Detail</a>
                            <?php if ($po['status'] == 'Pending'): ?>
                                <a href="<?= site_url('po/delete/'.$po['id']) ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Yakin ingin menghapus Purchase Order ini?');"><i class="fas fa-trash"></i> Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0 px-lg-5 px-4 py-3">
            <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
                <div class="text-muted">
                    Menampilkan <strong><?= count($purchase_orders) ?></strong> data
                </div>
                <?php if (!empty($searchKeyword)) : ?>
                    <div class="btn-group">
                        <form action="" method="post" class="d-inline">
                            <input type="hidden" name="reset" value="1">
                            <button type="submit" class="btn btn-outline-secondary rounded-pill px-4">Reset Pencarian</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
