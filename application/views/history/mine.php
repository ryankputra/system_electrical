<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container-fluid" style="margin-top:5rem;">
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
			<h4 class="mb-0">Riwayat Pengambilan Saya</h4>
			<div>
				<a href="<?= site_url('history') ?>" class="btn btn-sm btn-outline-secondary me-2">Laporan Global</a>
				<a href="<?= site_url('history/out') ?>" class="btn btn-sm btn-danger">Catat Keluar</a>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<div class="card shadow-sm rounded-4">
				<div class="card-body p-3">
					<div class="table-responsive">
						<table class="table table-hover table-sm align-middle">
							<thead class="table-light">
								<tr>
									<th>#</th>
									<th>ID</th>
									<th>Waktu</th>
									<th>Barang</th>
									<th>No. PO / Batch</th>
									<th>Qty</th>
									<th>Sisa di Batch</th>
									<th>Status Batch</th>
									<th>Keterangan</th>
									<th>Stok Sistem</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($history) && is_array($history)): ?>
									<?php $i = 1; foreach ($history as $row): ?>
										<?php
											$type = strtolower($row['type'] ?? '');
											// Determine displayed quantities per TA rules
											$rawQty = $row['qty'] ?? $row['quantity'] ?? $row['amount'] ?? 0;
											$qty_sisa = isset($row['qty_sisa']) ? (int)$row['qty_sisa'] : 0;
											$po = $row['po_number'] ?? ($row['po'] ?? null);
											$dist = htmlspecialchars($row['distributor'] ?? '');
											$userName = htmlspecialchars($row['user_name'] ?? ($row['user_nik'] ?? ''));
											$itemName = htmlspecialchars($row['nama_barang'] ?? ($row['electric_id'] ?? ''));
											$itemId = htmlspecialchars($row['electric_id'] ?? '');
											$dateRaw = $row['date'] ?? $row['created_at'] ?? null;
											$dateFmt = $dateRaw ? date('d M Y H:i', strtotime($dateRaw)) : '-';
											$systemStock = isset($row['system_stock']) ? (int)$row['system_stock'] : 0;

											// PO/Batch detection and display
											$poDisplay = '';
											$batchRefId = null;
											
											$k = $row['keterangan'] ?? '';
											if (preg_match('/Batch\s*#?(\d+)/i', $k, $m)) {
												$batchRefId = (int)$m[1];
											}
											if (!$batchRefId && (strpos(strtolower($row['type'] ?? ''), 'masuk') !== false || strtolower($row['type'] ?? '') === 'in')) {
												$batchRefId = (int)($row['id'] ?? 0);
											}
											
											if ($po) {
												$poDisplay = htmlspecialchars($po);
											} else {
												if ($batchRefId) {
													$CI = &get_instance();
													$histTable = $CI->db->table_exists('as_history') ? 'as_history' : 'history';
													$batchRow = $CI->db->get_where($histTable, ['id' => $batchRefId])->row_array();
													if ($batchRow) {
														$dateCols = ['tanggal_terima', 'created_at', 'date', 'tanggal_masuk', 'tgl_masuk'];
														$batchDateVal = null;
														foreach ($dateCols as $dc) { if (!empty($batchRow[$dc])) { $batchDateVal = $batchRow[$dc]; break; } }
														$poLabel = 'FIFO-B' . $batchRefId;
														if ($batchDateVal) $poLabel .= ' (Tgl: ' . date('d M Y', strtotime($batchDateVal)) . ')';
														$poDisplay = htmlspecialchars($poLabel);
													} else {
														$poDisplay = 'Batch ' . (int)($row['id'] ?? 0);
													}
												} else {
													$poDisplay = 'Batch ' . (int)($row['id'] ?? 0);
												}
											}

												// Display rules and Sisa/Status determination
												$displayQty = 0;
												$displaySisa = '-';
												$statusBatch = '';
												$referencedBatch = null;
												if (!empty($batchRefId)) {
													$CI = &get_instance();
													$histTable = $CI->db->table_exists('as_history') ? 'as_history' : 'history';
													$referencedBatch = $CI->db->get_where($histTable, ['id' => $batchRefId])->row_array();
												}

												// Determine authoritative remaining qty
												$sisaVal = null;
												if (strpos($type, 'masuk') !== false || $type === 'in') {
													if (isset($row['qty_sisa'])) {
														$sisaVal = (int)$row['qty_sisa'];
													}
												} else {
													if (!empty($referencedBatch) && isset($referencedBatch['qty_sisa'])) {
														$sisaVal = (int)$referencedBatch['qty_sisa'];
													}
												}

												if (strpos($type, 'masuk') !== false || $type === 'in') {
													if (isset($row['amount']) && $row['amount'] !== null) $displayQty = (int)$row['amount'];
													elseif (isset($row['jumlah']) && $row['jumlah'] !== null) $displayQty = (int)$row['jumlah'];
													else $displayQty = (int)$rawQty;
													// For Masuk rows prefer row's qty_sisa
													if ($sisaVal !== null) {
														$displaySisa = number_format($sisaVal);
														$statusBatch = ($sisaVal > 0) ? "<span class='badge bg-success'>TERSISA</span>" : "<span class='badge bg-danger'>HABIS</span>";
													} else {
														$displaySisa = '-';
														$statusBatch = "<span class='badge bg-secondary'>-</span>";
													}
												} else {
													$displayQty = (int)$rawQty;
													if ($sisaVal !== null) {
														$displaySisa = number_format($sisaVal);
														$statusBatch = ($sisaVal > 0) ? "<span class='badge bg-success'>TERSISA</span>" : "<span class='badge bg-danger'>HABIS</span>";
													} else {
														$displaySisa = '-';
														$statusBatch = "<span class='badge bg-secondary'>-</span>";
													}
												}
										?>
									<tr>
										<td><?= $i++; ?></td>
										<td><small class="font-monospace"><?= htmlspecialchars($row['id'] ?? ''); ?></small></td>
										<td><small><?= $dateFmt; ?></small></td>
										<td>
											<div class="fw-bold"><?= $itemName; ?></div>
											<small class="text-muted"><?= $itemId; ?></small>
										</td>
										<td><?= $poDisplay; ?></td>
										<td class="text-center align-middle"><?= number_format($displayQty); ?></td>
										<td class="text-center align-middle"><?= $displaySisa; ?></td>
										<td class="text-center align-middle"><?= $statusBatch; ?></td>
										<td><?= htmlspecialchars($row['keterangan'] ?? ''); ?></td>
										<td><span class="badge bg-light text-dark"><?= number_format($systemStock); ?></span></td>
									</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr><td colspan="10" class="text-center text-muted p-4">Belum ada riwayat pengambilan Anda.</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
