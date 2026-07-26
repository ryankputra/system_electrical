<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container-fluid" style="margin-top:5rem;">
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
			<h4 class="mb-0"><?= htmlspecialchars($title) ?></h4>
			<div class="d-flex gap-2">
				<?php
					$pdf_params = http_build_query([
						'start_date'  => $start_date  ?? '',
						'end_date'    => $end_date    ?? '',
						'electric_id' => $electric_id ?? '',
					]);
				?>
				<a href="<?= site_url('history/print_keluar?' . $pdf_params) ?>" target="_blank" class="btn btn-sm btn-danger">
					<i class="fas fa-file-pdf me-1"></i>Download PDF
				</a>
				<a href="<?= site_url('history') ?>" class="btn btn-sm btn-outline-secondary">Global Report</a>
			</div>
		</div>
	</div>

	<!-- Date Filter Form -->
	<div class="card mb-4 shadow-sm border-0 rounded-4">
		<div class="card-body">
			<form method="get" action="" id="form-filter-keluar">
				<div class="row g-3 align-items-end">
					<div class="col-md-3">
						<label class="form-label text-muted small fw-bold">Dari Tanggal</label>
						<input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date ?? '') ?>">
					</div>
					<div class="col-md-3">
						<label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
						<input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date ?? '') ?>">
					</div>
					<div class="col-md-3">
						<label class="form-label text-muted small fw-bold">Filter Barang</label>
						<select name="electric_id" class="form-select" id="select-barang-keluar">
							<option value="">— Semua Barang —</option>
							<?php foreach (($electrics ?? []) as $el): 
								$eid   = htmlspecialchars($el['electric_id'] ?? '');
								$label = htmlspecialchars(trim(($el['type'] ?? '') . ' ' . ($el['nama'] ?? '') . ' ' . ($el['brand'] ?? '')));
								$sel   = (isset($electric_id) && $electric_id === ($el['electric_id'] ?? '')) ? 'selected' : '';
							?>
								<option value="<?= $eid ?>" <?= $sel ?>><?= $eid ?> — <?= $label ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<button type="submit" class="btn btn-primary px-3"><i class="fas fa-filter me-2"></i>Terapkan</button>
						<?php if($start_date || $end_date || !empty($electric_id)): ?>
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
							<th>No. WO</th>
							<th>Keterangan / Tujuan</th>
							<th>Qty Keluar</th>
							<th>Peminta / Teknisi</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($history)): ?>
							<?php $i=1; $total_qty = 0; foreach($history as $r): ?>
								<?php $total_qty += (int)($r['display_amount'] ?? $r['qty'] ?? 0); ?>
								<tr>
									<td class="ps-4"><?= $i++ ?></td>
									<td><?= date('d M Y H:i', strtotime($r['created_at'] ?? $r['date'] ?? 'now')) ?></td>
									<td><?= htmlspecialchars($r['electric_id'] ?? '') ?></td>
									<td><strong><?= htmlspecialchars($r['spec_type'] ?? '') ?></strong></td>
									<td><?= htmlspecialchars(($r['nama_barang'] ?? '') . ' - ' . ($r['brand'] ?? '')) ?></td>
									<td><?= htmlspecialchars($r['po_number'] ?? $r['wo_number'] ?? '-') ?></td>
									<td><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
									<td><span class="badge bg-danger px-3 py-2 rounded-pill">- <?= $r['display_amount'] ?? $r['qty'] ?? 0 ?></span></td>
									<td><?= htmlspecialchars($r['user_name'] ?? $r['user_nik'] ?? '-') ?></td>
								</tr>
							<?php endforeach; ?>
							<tr class="table-light fw-bold">
								<td colspan="7" class="text-end pe-3">TOTAL QTY KELUAR:</td>
								<td><span class="badge bg-secondary px-3 py-2 rounded-pill"><?= $total_qty ?></span></td>
								<td></td>
							</tr>
						<?php else: ?>
							<tr><td colspan="9" class="text-center text-muted p-4">Tidak ada data barang keluar untuk periode ini.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
