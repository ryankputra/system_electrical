<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container-fluid" style="margin-top:5rem;">
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
			<h4 class="mb-0"><?= htmlspecialchars($title) ?></h4>
			<a href="<?= site_url('history') ?>" class="btn btn-sm btn-outline-secondary">Global Report</a>
		</div>
	</div>

	<!-- Date Filter Form -->
	<div class="card mb-4 shadow-sm border-0 rounded-4">
		<div class="card-body">
			<form method="get" action="">
				<div class="row g-3 align-items-end">
					<div class="col-md-4">
						<label class="form-label text-muted small fw-bold">Dari Tanggal</label>
						<input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date ?? '') ?>">
					</div>
					<div class="col-md-4">
						<label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
						<input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date ?? '') ?>">
					</div>
					<div class="col-md-4">
						<button type="submit" class="btn btn-primary px-4"><i class="fas fa-filter me-2"></i>Terapkan Filter</button>
						<?php if($start_date || $end_date): ?>
							<a href="<?= site_url('history/keluar') ?>" class="btn btn-outline-secondary ms-2">Reset</a>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="card shadow-sm border-0 rounded-4">
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover table-striped align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th class="ps-4">No</th>
							<th>Tanggal & Waktu</th>
							<th>ID Barang</th>
							<th>Nama Barang</th>
							<th>Kategori & Brand</th>
							<th>No. WO / Tujuan</th>
							<th>Qty Keluar</th>
							<th>Peminta / Teknisi</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($history)): ?>
							<?php $i=1; foreach($history as $r): ?>
								<tr>
									<td class="ps-4"><?= $i++ ?></td>
									<td><?= date('d M Y H:i', strtotime($r['created_at'] ?? $r['date'] ?? 'now')) ?></td>
									<td><?= htmlspecialchars($r['electric_id'] ?? '') ?></td>
									<td><strong><?= htmlspecialchars($r['nama_barang'] ?? '') ?></strong></td>
									<td><?= htmlspecialchars(($r['spec_type'] ?? '') . ' - ' . ($r['brand'] ?? '')) ?></td>
									<td><?= htmlspecialchars($r['po_number'] ?? $r['keterangan'] ?? '-') ?></td>
									<td><span class="badge bg-danger px-3 py-2 rounded-pill">- <?= $r['display_amount'] ?? $r['qty'] ?? 0 ?></span></td>
									<td><?= htmlspecialchars($r['user_name'] ?? $r['user_nik'] ?? '-') ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr><td colspan="8" class="text-center text-muted p-4">Tidak ada data barang keluar untuk periode ini.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
