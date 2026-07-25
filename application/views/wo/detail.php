<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 1000px;">
    <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <h4 class="m-0"><?= htmlspecialchars($title) ?></h4>
        <a href="<?= site_url('wo') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">Kembali</a>
    </div>
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th width="35%" class="text-muted">Nomor WO</th>
                        <td>: <strong><?= htmlspecialchars($wo['wo_number']) ?></strong></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nama Proyek</th>
                        <td>: <?= htmlspecialchars($wo['project_name']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Tanggal Permintaan</th>
                        <td>: <?= date('d M Y', strtotime($wo['request_date'])) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Dibuat Pada</th>
                        <td>: <?= date('d M Y H:i', strtotime($wo['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <h5 class="mb-3 border-bottom pb-2">Daftar Pengajuan Barang</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th width="5%">No</th>
                        <th width="15%">Waktu Request</th>
                        <th width="15%">Peminta</th>
                        <th width="25%">Nama Barang</th>
                        <th width="10%">Qty</th>
                        <th width="15%">Status</th>
                        <?php if (is_admin() || $this->session->userdata('role') === 'Staf Gudang'): ?>
                        <th width="15%">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($details as $d): ?>
                    <tr class="text-center">
                        <td><?= $no++ ?></td>
                        <td><?= date('d M Y H:i', strtotime($d['created_at'])) ?></td>
                        <td><?= htmlspecialchars($d['requester_name'] ?? $d['user_nik'] ?? '-') ?></td>
                        <td class="text-start">
                            <?= htmlspecialchars($d['electric_name']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($d['brand'] . ' - ' . $d['electric_type']) ?></small>
                        </td>
                        <td><strong><?= htmlspecialchars($d['qty']) ?></strong></td>
                        <td>
                            <?php 
                                if ($d['status'] === 'Approved') echo '<span class="badge bg-success">Disetujui</span>';
                                elseif ($d['status'] === 'Rejected') echo '<span class="badge bg-danger">Ditolak</span>';
                                else echo '<span class="badge bg-warning text-dark">Pending</span>';
                            ?>
                        </td>
                        <?php if (is_admin() || $this->session->userdata('role') === 'Staf Gudang'): ?>
                        <td>
                            <?php if ($d['status'] === 'Pending'): ?>
                                <a href="<?= site_url('wo/approve_item/' . $d['id']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve barang ini? Stok akan terpotong (FIFO).')"><i class="fas fa-check"></i></a>
                                <a href="<?= site_url('wo/reject_item/' . $d['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tolak barang ini?')"><i class="fas fa-times"></i></a>
                            <?php else: ?>
                                <small class="text-muted">Oleh: <?= htmlspecialchars($d['approved_by'] ?? '-') ?></small>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($details)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada barang yang diajukan untuk Work Order ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
