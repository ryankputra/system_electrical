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
				<a href="<?= site_url('history/print_masuk?' . $pdf_params) ?>" target="_blank" class="btn btn-sm btn-danger">
					<i class="fas fa-file-pdf me-1"></i>Download PDF
				</a>
				<a href="<?= site_url('history') ?>" class="btn btn-sm btn-outline-secondary">Global Report</a>
			</div>
		</div>
	</div>

	<!-- Select2 Assets -->
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
	<style>
		.select2-container--bootstrap-5 .select2-selection {
			border-radius: 8px !important;
			min-height: calc(2.4rem + 2px) !important;
			padding: 0.3rem 0.75rem !important;
			font-size: 0.95rem !important;
		}
	</style>

	<!-- Date Filter Form -->
	<div class="card mb-4 shadow-sm border-0 rounded-4">
		<div class="card-body">
			<form method="get" action="" id="form-filter-masuk">
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
						<label class="form-label text-muted small fw-bold"><i class="fas fa-search me-1"></i>Filter Barang</label>
						<select name="electric_id" class="form-select select2-barang-masuk" id="select-barang-masuk">
							<option value="">— Semua Barang —</option>
							<?php foreach (($electrics ?? []) as $el):
								$eid  = htmlspecialchars($el['electric_id'] ?? '');
								$type = $el['type'] ?? '';
								$nama = $el['nama'] ?? '';
								$brand = $el['brand'] ?? '';
								$spec = [];
								if (!empty($brand) && $brand !== '-') $spec[] = $brand;
								$voltage = trim(($el['voltage'] ?? '') . ($el['voltage_unit'] ?? ''));
								if ($voltage !== '') $spec[] = $voltage;
								if (!empty($el['ampere'])) $spec[] = $el['ampere'] . 'A';
								$spec_str = !empty($spec) ? ' (' . implode(' | ', $spec) . ')' : '';
								$label = htmlspecialchars(trim($type . $spec_str) . ($nama ? ' — ' . $nama : ''));
								$sel  = (isset($electric_id) && $electric_id === ($el['electric_id'] ?? '')) ? 'selected' : '';
							?>
								<option value="<?= $eid ?>" <?= $sel ?>><?= $label ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<button type="submit" class="btn btn-primary px-3"><i class="fas fa-filter me-2"></i>Terapkan</button>
						<?php if($start_date || $end_date || !empty($electric_id)): ?>
							<a href="<?= site_url('history/masuk') ?>" class="btn btn-outline-secondary ms-2">Reset</a>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script>
		$(document).ready(function() {
			$('.select2-barang-masuk').select2({
				theme: 'bootstrap-5',
				placeholder: '— Cari nama / spesifikasi barang —',
				allowClear: true,
				width: '100%',
			});
		});
	</script>

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
							<th>No. PO</th>
							<th>Vendor / Supplier</th>
							<th>Harga Satuan</th>
							<th>Qty Masuk</th>
							<th>Total Harga</th>
							<th>Penerima</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($history)): ?>
							<?php $i=1; $total_semua = 0; foreach($history as $r): ?>
								<?php 
									$harga = isset($r['harga_satuan']) ? (float)$r['harga_satuan'] : 0;
									$qty = isset($r['display_amount']) ? $r['display_amount'] : ($r['qty'] ?? 0);
									$subtotal = $harga * $qty;
									$total_semua += $subtotal;
									$vendor = !empty($r['supplier_name']) ? $r['supplier_name'] : (!empty($r['distributor']) ? $r['distributor'] : '-');
								?>
								<tr>
									<td class="ps-4"><?= $i++ ?></td>
									<td><?= date('d M Y H:i', strtotime($r['created_at'] ?? $r['date'] ?? 'now')) ?></td>
									<td><?= htmlspecialchars($r['electric_id'] ?? '') ?></td>
									<td><strong><?= htmlspecialchars($r['spec_type'] ?? '') ?></strong></td>
									<td><?= htmlspecialchars(($r['nama_barang'] ?? '') . ' - ' . ($r['brand'] ?? '')) ?></td>
									<td><?= htmlspecialchars($r['po_number'] ?? '-') ?></td>
									<td><?= htmlspecialchars($vendor) ?></td>
									<td class="text-end">Rp <?= number_format($harga, 0, ',', '.') ?></td>
									<td><span class="badge bg-success px-3 py-2 rounded-pill">+ <?= $qty ?></span></td>
									<td class="text-end fw-bold">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
									<td><?= htmlspecialchars($r['user_name'] ?? $r['user_nik'] ?? '-') ?></td>
								</tr>
							<?php endforeach; ?>
							<tr class="table-light fw-bold">
								<td colspan="9" class="text-end pe-3">TOTAL KESELURUHAN:</td>
								<td class="text-end">Rp <?= number_format($total_semua, 0, ',', '.') ?></td>
								<td></td>
							</tr>
						<?php else: ?>
							<tr><td colspan="11" class="text-center text-muted p-4">Tidak ada data barang masuk untuk periode ini.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
