<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="container-fluid" style="margin-top:5rem;">
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
				<h4 class="mb-0">Laporan Mutasi Barang</h4>
				<div>
					<a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary me-2">Kembali</a>
					<?php if (is_admin() || is_manajer_oe()): ?>
						<a href="<?= site_url('dashboard/download_monthly') ?>" class="btn btn-sm" style="background:#004274;color:#fff;border:0;padding:8px 16px;border-radius:6px;" target="_blank"><i class="fas fa-file-pdf me-1"></i> Cetak Laporan (PDF)</a>
					<?php endif; ?>
				</div>
			</div>
	</div>

	<div class="row">
		<div class="col-12">
			<?php
				// Group history by electric_id for batch summary
				$byElectric = [];
				$history = $history ?? [];
				foreach ($history as $row) {
					$eid = $row['electric_id'] ?? 'unknown';
					if (!isset($byElectric[$eid])) {
						$byElectric[$eid] = [];
					}
					$byElectric[$eid][] = $row;
				}
			?>

			<!-- ==================== BATCH SUMMARY CARDS ==================== -->
			
			<!-- Search & Filter Section -->
			<div class="mb-4 p-3 bg-light rounded-3">
				<div class="row g-3 mb-3">
					<div class="col-md-8">
						<div class="input-group">
							<span class="input-group-text" style="background: #fff;">
								<i class="fas fa-search"></i>
							</span>
							<input type="text" class="form-control" id="search-batches" placeholder="Cari barang, brand, spesifikasi, atau ID..." style="border-left: 0;">
						</div>
						<small class="text-muted d-block mt-2">💡 Mulai ketik untuk filter barang secara real-time</small>
					</div>
					<div class="col-md-4">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="toggle-batch-habis">
							<label class="form-check-label" for="toggle-batch-habis">
								<strong>Tampilkan Batch Habis</strong>
							</label>
						</div>
					</div>
				</div>
			</div>

			<!-- Batch Cards Container - Initial Load (10 items) -->
			<div id="batch-container">
				<?php
					$displayItems = array_slice($byElectric, 0, 10, true);
					$totalItems = count($byElectric);
					$displayCount = count($displayItems);
				?>
				<?php foreach ($displayItems as $electricId => $rows): ?>
				<?php
					$itemName = htmlspecialchars($rows[0]['nama_barang'] ?? $electricId);
					$itemId = htmlspecialchars($electricId);
					$itemBrand = htmlspecialchars($rows[0]['brand'] ?? 'N/A');

					// Build full spec string from all available fields
					$specParts = [];
					$sampleRow = $rows[0];
					if (!empty($sampleRow['spec_type'])) {
						$specParts[] = 'Tipe: ' . $sampleRow['spec_type'];
					}
					$voltVal = trim(($sampleRow['voltage'] ?? '') . ($sampleRow['voltage_unit'] ?? ''));
					if ($voltVal !== '') $specParts[] = $voltVal;
					if (!empty($sampleRow['ampere'])) $specParts[] = $sampleRow['ampere'] . 'A';
					$dayaVal = trim(($sampleRow['daya'] ?? '') . ($sampleRow['daya_unit'] ?? ''));
					if ($dayaVal !== '') $specParts[] = $dayaVal;
					$itemSpec = htmlspecialchars(!empty($specParts) ? implode(' | ', $specParts) : 'N/A');
					
					// Group by batch using FIFO logic: match Keluar to the Masuk that came before it
					$byBatch = [];
					$totalMasuk = 0;
					$totalKeluar = 0;
					$batchStack = []; // Stack of batch IDs with remaining qty
					$nextBatchSeq = 1;
					$batchIdToSeq = []; // Map batch ID to sequence number
					
					foreach ($rows as $row) {
						$type = strtolower($row['type'] ?? '');
						$qty = (int)($row['display_amount'] ?? 0);
						$batchId = (int)($row['id'] ?? 0);
						
						if (strpos($type, 'masuk') !== false) {
							// Masuk: start a new batch
							$batchSeq = $nextBatchSeq++;
							$batchIdToSeq[$batchId] = $batchSeq;
							
							if (!isset($byBatch[$batchSeq])) {
								$byBatch[$batchSeq] = ['masuk' => 0, 'keluar' => 0, 'sisa' => 0, 'supplier' => '', 'distributor' => '', 'po' => '', 'batch_id' => $batchId, 'harga_satuan' => 0];
							}
							
							$byBatch[$batchSeq]['masuk'] += $qty;
							$byBatch[$batchSeq]['sisa'] += $qty;
							$totalMasuk += $qty;
							$byBatch[$batchSeq]['supplier'] = $row['supplier_name'] ?? '';
							// Prioritas distributor: field distributor -> keterangan (jika dari PO) -> kosong
							$distName = trim($row['distributor'] ?? '');
							if ($distName === '' && !empty($row['keterangan'])) {
								// Coba ambil dari keterangan "Penerimaan otomatis dari PO xxx"
								if (!empty($row['supplier_name'])) {
									$distName = $row['supplier_name'];
								}
							}
							$byBatch[$batchSeq]['distributor'] = $distName;
							$byBatch[$batchSeq]['po'] = $row['batch_number'] ?? ($row['po_number'] ?? '');
							$byBatch[$batchSeq]['harga_satuan'] = (float)($row['harga_satuan'] ?? 0);
							
							// Push to batch stack for FIFO
							array_push($batchStack, ['seq' => $batchSeq, 'remaining' => $qty]);
						} 
						elseif (strpos($type, 'keluar') !== false) {
							// Keluar: assign to oldest batch (FIFO)
							$remaining = $qty;
							
							// Process from oldest batch to newest
							while ($remaining > 0 && !empty($batchStack)) {
								$batch = &$batchStack[0];
								$take = min($batch['remaining'], $remaining);
								$batchSeq = $batch['seq'];
								
								$byBatch[$batchSeq]['keluar'] += $take;
								$byBatch[$batchSeq]['sisa'] -= $take;
								$totalKeluar += $take;
								$remaining -= $take;
								$batch['remaining'] -= $take;
								
								// Remove from stack if exhausted
								if ($batch['remaining'] <= 0) {
									array_shift($batchStack);
								}
							}
						}
					}
					
					// Calculate final sisa
					$totalSisa = $totalMasuk - $totalKeluar;
				?>

				<!-- Item Header with Summary -->
				<div class="card border-0 shadow-sm rounded-4 mb-4">
					<div class="card-header bg-light border-bottom px-4 py-3">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<h6 class="mb-1 fw-bold"><?= $itemName; ?></h6>
								<small class="text-muted">ID: <?= $itemId; ?> | Brand: <strong><?= $itemBrand; ?></strong> | Spesifikasi: <strong><?= $itemSpec; ?></strong></small>
							</div>
							<div class="text-end">
								<div style="font-size: 0.9rem;">
									<span class="me-3"><strong>Masuk:</strong> <span class="badge bg-success"><?= number_format($totalMasuk); ?></span></span>
									<span class="me-3"><strong>Keluar:</strong> <span class="badge bg-danger"><?= number_format($totalKeluar); ?></span></span>
									<span><strong>Sisa:</strong> <span class="badge bg-info"><?= number_format($totalSisa); ?></span></span>
								</div>
							</div>
						</div>
					</div>

					<!-- Batch Groups (Collapsible) -->
					<div class="card-body p-0">
						<?php foreach ($byBatch as $batchSeq => $batchData): ?>
							<?php
								$isHabis = $batchData['sisa'] <= 0;
								$hiddenClass = $isHabis ? 'd-none' : '';
								$batchId = htmlspecialchars($electricId) . '-' . $batchSeq;
							?>
							<div class="batch-group batch-card <?= $hiddenClass; ?>" data-batch-id="<?= $batchId; ?>" data-is-habis="<?= $isHabis ? '1' : '0'; ?>" style="border-bottom: 1px solid #dee2e6; padding: 0.75rem 1rem;">
								<button class="btn btn-sm btn-link text-start w-100 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#batch-detail-<?= $batchId; ?>" aria-expanded="false">
									<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
										<div>
											<i class="fas fa-chevron-right me-2" style="transition: transform 0.2s;"></i>
											<strong>Batch #<?= htmlspecialchars($batchSeq); ?></strong>
											<small class="text-muted ms-2"><?= htmlspecialchars($batchData['po']); ?></small>
											<?php if ($isHabis): ?>
												<span class="badge bg-dark ms-2">HABIS</span>
											<?php endif; ?>
											<?php if (!empty($batchData['distributor'])): ?>
												<span class="badge bg-warning text-dark ms-2"><i class="fas fa-truck me-1"></i><?= htmlspecialchars($batchData['distributor']); ?></span>
											<?php endif; ?>
										</div>
										<div style="font-size: 0.85rem;">
											<span class="me-2">Masuk: <strong><?= number_format($batchData['masuk']); ?></strong></span>
											<span class="me-2">Keluar: <strong><?= number_format($batchData['keluar']); ?></strong></span>
											<span class="me-2">Sisa: <span class="badge <?= $batchData['sisa'] > 0 ? 'bg-success' : 'bg-danger'; ?>"><?= number_format($batchData['sisa']); ?></span></span>
											<?php if ($batchData['harga_satuan'] > 0): ?>
												<span class="badge bg-primary ms-2">Rp <?= number_format($batchData['harga_satuan'], 0, ',', '.'); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</button>

								<!-- Collapse Content -->
								<div class="collapse" id="batch-detail-<?= $batchId; ?>">
									<div style="background: #f9f9f9; padding: 1rem; margin-top: 0.5rem; border-radius: 4px; font-size: 0.85rem;">
										<!-- Info Pembelian -->
										<div class="d-flex flex-wrap gap-3 mb-3 p-2 rounded" style="background:#fff; border:1px solid #e0e0e0;">
											<div>
												<small class="text-muted d-block">Distributor / Supplier</small>
												<strong><?= !empty($batchData['distributor']) ? htmlspecialchars($batchData['distributor']) : '<span class="text-muted">-</span>'; ?></strong>
											</div>
											<div>
												<small class="text-muted d-block">Nomor PO / Referensi</small>
												<strong><?= !empty($batchData['po']) ? htmlspecialchars($batchData['po']) : '<span class="text-muted">-</span>'; ?></strong>
											</div>
											<?php if ($batchData['harga_satuan'] > 0): ?>
											<div>
												<small class="text-muted d-block">Harga Satuan</small>
												<strong class="text-primary">Rp <?= number_format($batchData['harga_satuan'], 0, ',', '.'); ?></strong>
											</div>
											<?php endif; ?>
										</div>
										<?php
											// For detail rows, rebuild batch assignment using FIFO to match summary
											$detailBatchStack = [];
											$detailNextSeq = 1;
											$detailBatchMap = []; // row_id => batch_seq for matching
											
											foreach ($rows as $detailRow) {
												$dType = strtolower($detailRow['type'] ?? '');
												$dQty = (int)($detailRow['display_amount'] ?? 0);
												$dId = (int)($detailRow['id'] ?? 0);
												
												if (strpos($dType, 'masuk') !== false) {
													$dSeq = $detailNextSeq++;
													$detailBatchMap[$dId] = $dSeq;
													array_push($detailBatchStack, ['seq' => $dSeq, 'remaining' => $dQty]);
												} elseif (strpos($dType, 'keluar') !== false) {
													$dRemaining = $dQty;
													while ($dRemaining > 0 && !empty($detailBatchStack)) {
														$dBatch = &$detailBatchStack[0];
														$dTake = min($dBatch['remaining'], $dRemaining);
														if (!isset($detailBatchMap[$dId])) {
															$detailBatchMap[$dId] = $dBatch['seq'];
														}
														$dRemaining -= $dTake;
														$dBatch['remaining'] -= $dTake;
														if ($dBatch['remaining'] <= 0) {
															array_shift($detailBatchStack);
														}
														break; // Assign to first matched batch only
													}
												}
											}
											
											$batchRows = array_filter($rows, function($r) use ($batchSeq, $detailBatchMap) {
												$rId = (int)($r['id'] ?? 0);
												return isset($detailBatchMap[$rId]) && $detailBatchMap[$rId] == $batchSeq;
											});
											$cumulativeSisa = $batchData['masuk'];
										?>
										<?php foreach ($batchRows as $row): ?>
											<?php
												$type = strtolower($row['type'] ?? '');
												$qty = (int)($row['display_amount'] ?? 0);
												$dateRaw = $row['date'] ?? $row['created_at'] ?? null;
												$dateFmt = $dateRaw ? date('d M H:i', strtotime($dateRaw)) : '-';
												$keterangan = htmlspecialchars($row['keterangan_dinamis'] ?? $row['keterangan'] ?? '');
												
												if (strpos($type, 'masuk') !== false) {
													$cumulativeSisa = $qty;
													$display = '<span class="badge bg-success me-2">MASUK</span> +' . $qty . ' → Sisa: ' . $cumulativeSisa;
												} else {
													$oldSisa = $cumulativeSisa;
													$cumulativeSisa -= $qty;
													$display = '<span class="badge bg-danger me-2">KELUAR</span> -' . $qty . ' (dari ' . $oldSisa . ') → Sisa: ' . $cumulativeSisa;
												}
											?>
											<div class="mb-2">
												<small class="text-muted"><?= $dateFmt; ?> | <?= htmlspecialchars($row['user_name'] ?? $row['user_nik'] ?? ''); ?></small><br>
												<small><?= $display; ?></small>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>

			<!-- Load More Button (shown only if there are more items) -->
			<?php if ($totalItems > 10): ?>
			<div class="text-center mb-4">
				<button id="load-more-btn" class="btn btn-outline-primary" style="padding: 10px 30px; border-radius: 6px;" data-offset="10" data-total="<?= $totalItems; ?>">
					<i class="fas fa-chevron-down me-2"></i>Muat Lebih Banyak (<?= $displayCount; ?> dari <?= $totalItems; ?>)
				</button>
				<small class="d-block text-muted mt-2" id="load-more-status"></small>
			</div>
			<?php endif; ?>

			<!-- ==================== DETAIL LOG TABLE (WITH DATATABLES) ==================== -->
			<div class="card border-0 shadow-sm rounded-4 mt-5">
				<div class="card-header bg-light border-bottom px-4 py-3">
					<h6 class="mb-0 fw-bold">📋 Detail Log Lengkap (Pagination)</h6>
				</div>
				<div class="card-body p-3">
					<div class="table-responsive">
						<table id="dataTable-history" class="table table-hover table-sm align-middle">
							<thead class="table-light">
								<tr>
									<th>#</th>
									<th>ID Log</th>
									<th>Waktu</th>
									<th>Tipe</th>
									<th>Barang &amp; Spesifikasi</th>
									<th>Batch #</th>
									<th>Distributor</th>
									<th>Qty</th>
									<th>Harga Satuan</th>
									<th>Ref. Batch</th>
									<th>Sisa di Batch</th>
									<th>Status Batch</th>
									<th>User</th>
									<th>Keterangan</th>
									<th>Stok Sistem</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php if (!empty($history) && is_array($history)): ?>
									<?php 
										$i = 1; 
										$prevBatchSeq = null;
										$batchColors = ['#E8F4F8', '#F0F8E8', '#F8F4E8', '#F8E8E8', '#F0E8F8'];
										$colorIdx = 0;
										$currentColorIdx = 0;
									?>
									<?php foreach ($history as $row): ?>
										<?php
											$type = strtolower($row['type'] ?? '');
											$typeBadge = 'secondary';
											if (strpos($type, 'masuk') !== false || $type === 'in') $typeBadge = 'success';
											elseif (strpos($type, 'keluar') !== false || $type === 'out') $typeBadge = 'danger';

											$userName = htmlspecialchars($row['user_name'] ?? ($row['user_nik'] ?? ''));
											$itemName = htmlspecialchars($row['nama_barang'] ?? ($row['electric_id'] ?? ''));
											$itemId = htmlspecialchars($row['electric_id'] ?? '');
											$dateRaw = $row['date'] ?? $row['created_at'] ?? null;
											$dateFmt = $dateRaw ? date('d M Y H:i', strtotime($dateRaw)) : '-';
											$systemStock = isset($row['system_stock']) ? (int)$row['system_stock'] : 0;

											// Build full spec string per row
											$rowSpecParts = [];
											if (!empty($row['spec_type'])) $rowSpecParts[] = 'Tipe: ' . $row['spec_type'];
											$rv = trim(($row['voltage'] ?? '') . ($row['voltage_unit'] ?? ''));
											if ($rv !== '') $rowSpecParts[] = $rv;
											if (!empty($row['ampere'])) $rowSpecParts[] = $row['ampere'] . 'A';
											$rd = trim(($row['daya'] ?? '') . ($row['daya_unit'] ?? ''));
											if ($rd !== '') $rowSpecParts[] = $rd;
											$rowSpec = htmlspecialchars(implode(' | ', $rowSpecParts));

											// Harga satuan (hanya tampil untuk Masuk)
											$isMasuk = strpos($type, 'masuk') !== false || $type === 'in';
											$hargaSatuan = (float)($row['harga_satuan'] ?? 0);
											$hargaDisplay = ($isMasuk && $hargaSatuan > 0)
												? '<span class="badge bg-primary">Rp ' . number_format($hargaSatuan, 0, ',', '.') . '</span>'
												: '<span class="text-muted">-</span>';

											// Use batch_seq field for clearer FIFO display
											$batchSeq = $row['batch_seq'] ?? '-';
											$batchSeqDisplay = ($batchSeq !== '-') ? '<strong>#' . htmlspecialchars($batchSeq) . '</strong>' : '-';
											
											// Detect batch change for coloring
											if ($batchSeq !== '-' && $batchSeq !== $prevBatchSeq) {
												$prevBatchSeq = $batchSeq;
												$currentColorIdx = ($colorIdx + 1) % count($batchColors);
												$colorIdx = $currentColorIdx;
											}
											$rowBgColor = ($batchSeq !== '-') ? $batchColors[$currentColorIdx] : 'transparent';
											
											$poDisplay = htmlspecialchars($row['batch_number'] ?? $row['po_number'] ?? ('Batch ' . (int)($row['id'] ?? 0)));
											$dist = htmlspecialchars($row['supplier_name'] ?? $row['distributor'] ?? '');
											$displayQty = number_format((int)($row['display_amount'] ?? $row['amount'] ?? $row['qty'] ?? 0));
											$displaySisa = isset($row['sisa_batch']) && $row['sisa_batch'] !== '-' ? number_format((int)$row['sisa_batch']) : '-';
											$sisaVal = is_numeric($row['sisa_batch']) ? (int)$row['sisa_batch'] : null;
											if ($sisaVal === null) {
												$statusBatch = "<span class='badge bg-secondary'>-</span>";
											} else {
												$statusBatch = $sisaVal > 0 ? "<span class='badge bg-success'>TERSISA</span>" : "<span class='badge bg-danger'>HABIS</span>";
											}
											$keterangan = htmlspecialchars($row['keterangan_dinamis'] ?? $row['keterangan'] ?? '');
											
											// Extract reference batch for Keluar transactions - show which batch it came from
											$refBatch = '-';
											if (strpos($type, 'keluar') !== false || $type === 'out') {
												// Extract batch seq from keterangan: "Keluar dari Batch #X"
												$k = $row['keterangan_dinamis'] ?? $row['keterangan'] ?? '';
												if (preg_match('/Batch\s*#?(\d+)/i', $k, $m)) {
													$refBatchSeq = intval($m[1]);
													$refBatch = '<span class="badge bg-info"><strong>Batch #' . $refBatchSeq . '</strong></span>';
												}
											}
										?>
										<tr style="background-color: <?= $rowBgColor ?>;">
											<td><?= $i++; ?></td>
											<td><small class="font-monospace"><?= htmlspecialchars($row['id'] ?? ''); ?></small></td>
											<td><small><?= $dateFmt; ?></small></td>
											<td><span class="badge bg-<?= $typeBadge ?> text-white"><?= htmlspecialchars($row['type'] ?? ''); ?></span></td>
											<td>
												<div class="fw-bold"><?= $itemName; ?></div>
												<small class="text-muted"><?= $itemId; ?></small>
												<?php if ($rowSpec !== ''): ?>
												<div class="mt-1"><?php foreach ($rowSpecParts as $sp): ?><span class="badge bg-secondary me-1" style="font-weight:400;font-size:0.72rem;"><?= htmlspecialchars($sp); ?></span><?php endforeach; ?></div>
												<?php endif; ?>
											</td>
											<td><?= $batchSeqDisplay; ?></td>
											<td><?= $dist; ?></td>
											<td><?= $displayQty; ?></td>
											<td><?= $hargaDisplay; ?></td>
											<td><?= $refBatch; ?></td>
											<td><?= $displaySisa; ?></td>
											<td><?= $statusBatch; ?></td>
											<td><?= $userName; ?></td>
											<td>
												<small><?= $keterangan; ?></small>
											</td>
											<td><span class="badge bg-light text-dark"><?= number_format($systemStock); ?></span></td>
											<td>
												<?php if (strpos(strtolower($row['type'] ?? ''), 'masuk') !== false || strtolower($row['type'] ?? '') === 'in'): ?>
													<a href="<?= site_url('history/print_sticker/' . $row['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-print"></i> Cetak Stiker</a>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr><td colspan="16" class="text-center text-muted p-4">Belum ada riwayat transaksi.</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
	.batch-group button:hover {
		background-color: #f9f9f9 !important;
	}
	.batch-group button i {
		display: inline-block;
		transition: transform 0.2s;
	}
	.batch-group button[aria-expanded="true"] i {
		transform: rotate(90deg);
	}
	.batch-card {
		transition: all 0.3s ease;
	}
	#dataTable-history {
		font-size: 0.875rem;
	}
</style>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
	// Initialize DataTables untuk Detail Log
	document.addEventListener('DOMContentLoaded', function() {
		// Check if table has actual data rows (not just empty message)
		const tableBody = document.querySelector('#dataTable-history tbody');
		const hasData = tableBody.querySelectorAll('tr').length > 1 || 
		                (tableBody.querySelectorAll('tr').length === 1 && !tableBody.querySelector('tr td[colspan]'));
		
		// Only initialize DataTables if there's actual data
		if (hasData) {
			if ($.fn.DataTable.isDataTable('#dataTable-history')) {
				$('#dataTable-history').DataTable().destroy();
			}
			
			$('#dataTable-history').DataTable({
				"pageLength": 10,
				"lengthChange": false,
				"searching": true,
				"ordering": true,
				"info": true,
				"autoWidth": false,
				"language": {
					"url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
				},
				"columnDefs": [{
					"targets": '_all',
					"defaultContent": '-'
				}]
			});
		}
	});

	// Toggle Checkbox untuk Tampilkan/Sembunyikan Batch Habis
	document.getElementById('toggle-batch-habis').addEventListener('change', function() {
		const isChecked = this.checked;
		const batchCards = document.querySelectorAll('.batch-card');
		
		batchCards.forEach(card => {
			const isHabis = card.getAttribute('data-is-habis') === '1';
			
			if (isHabis) {
				if (isChecked) {
					card.classList.remove('d-none');
				} else {
					card.classList.add('d-none');
				}
			}
		});
	});

	// ============ SEARCH & LOAD MORE FUNCTIONALITY ============
	const searchInput = document.getElementById('search-batches');
	const loadMoreBtn = document.getElementById('load-more-btn');
	const batchContainer = document.getElementById('batch-container');
	const loadMoreStatus = document.getElementById('load-more-status');
	let currentOffset = 0;
	let isLoading = false;

	// Real-time search with debounce
	let searchTimeout;
	searchInput.addEventListener('input', function() {
		clearTimeout(searchTimeout);
		const searchQuery = this.value.trim();
		
		searchTimeout = setTimeout(() => {
			if (searchQuery.length > 0) {
				// Reset offset when searching
				currentOffset = 0;
				searchBatches(searchQuery);
			} else {
				// Reset to initial load (first 10)
				location.reload();
			}
		}, 300); // 300ms debounce
	});

	// Load More button handler
	if (loadMoreBtn) {
		loadMoreBtn.addEventListener('click', function() {
			if (isLoading) return;
			
			isLoading = true;
			loadMoreBtn.disabled = true;
			loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sedang memuat...';
			
			const offset = parseInt(loadMoreBtn.getAttribute('data-offset')) || 0;
			const searchQuery = searchInput.value.trim();
			
			loadMoreBatches(offset, searchQuery);
		});
	}

	// AJAX: Search batches
	function searchBatches(query) {
		if (isLoading) return;
		isLoading = true;
		
		const url = '<?= site_url('history/get_batches_ajax'); ?>?offset=0&search=' + encodeURIComponent(query);
		
		fetch(url)
			.then(response => response.json())
			.then(data => {
				batchContainer.innerHTML = data.html;
				currentOffset = data.offset;
				
				// Update Load More button
				if (loadMoreBtn) {
					if (data.hasMore) {
						loadMoreBtn.style.display = 'block';
						loadMoreBtn.setAttribute('data-offset', data.offset);
						loadMoreBtn.innerHTML = '<i class="fas fa-chevron-down me-2"></i>Muat Lebih Banyak (' + data.itemsLoaded + ' dari ' + data.total + ')';
						loadMoreBtn.disabled = false;
					} else {
						loadMoreBtn.style.display = 'none';
						loadMoreStatus.textContent = 'Semua ' + data.total + ' barang sudah ditampilkan';
					}
				}
				
				// Re-initialize Bootstrap collapse
				initializeCollapses();
				isLoading = false;
			})
			.catch(error => {
				console.error('Error:', error);
				loadMoreStatus.textContent = 'Terjadi kesalahan saat mencari data';
				isLoading = false;
			});
	}

	// AJAX: Load more batches
	function loadMoreBatches(offset, searchQuery) {
		const url = '<?= site_url('history/get_batches_ajax'); ?>?offset=' + offset + '&search=' + encodeURIComponent(searchQuery);
		
		fetch(url)
			.then(response => response.json())
			.then(data => {
				// Append new HTML
				batchContainer.innerHTML += data.html;
				currentOffset = data.offset;
				
				// Update Load More button
				if (loadMoreBtn) {
					if (data.hasMore) {
						loadMoreBtn.setAttribute('data-offset', data.offset);
						loadMoreBtn.innerHTML = '<i class="fas fa-chevron-down me-2"></i>Muat Lebih Banyak (' + (offset + data.itemsLoaded) + ' dari ' + data.total + ')';
						loadMoreBtn.disabled = false;
					} else {
						loadMoreBtn.style.display = 'none';
						loadMoreStatus.textContent = 'Semua ' + data.total + ' barang sudah ditampilkan';
					}
				}
				
				// Re-initialize Bootstrap collapse for new elements
				initializeCollapses();
				isLoading = false;
			})
			.catch(error => {
				console.error('Error:', error);
				loadMoreStatus.textContent = 'Terjadi kesalahan saat memuat lebih banyak data';
				loadMoreBtn.disabled = false;
				isLoading = false;
			});
	}

	// Initialize Bootstrap collapse for dynamically added elements
	function initializeCollapses() {
		const collapseElements = document.querySelectorAll('[data-bs-toggle="collapse"]');
		collapseElements.forEach(element => {
			// Remove old listener to avoid duplicates
			element.removeEventListener('click', handleCollapseClick);
			element.addEventListener('click', handleCollapseClick);
			
			// Update chevron rotation on collapse show/hide
			const targetId = element.getAttribute('data-bs-target');
			const collapseTarget = document.querySelector(targetId);
			if (collapseTarget) {
				collapseTarget.addEventListener('show.bs.collapse', function() {
					element.querySelector('i').style.transform = 'rotate(90deg)';
				});
				collapseTarget.addEventListener('hide.bs.collapse', function() {
					element.querySelector('i').style.transform = 'rotate(0deg)';
				});
			}
		});
	}

	function handleCollapseClick(e) {
		const target = e.currentTarget;
		const icon = target.querySelector('i');
		const isExpanded = target.getAttribute('aria-expanded') === 'true';
		
		if (icon && !isExpanded) {
			icon.style.transform = 'rotate(90deg)';
		} else if (icon) {
			icon.style.transform = 'rotate(0deg)';
		}
	}

	// Initialize on page load
	document.addEventListener('DOMContentLoaded', function() {
		initializeCollapses();
	});
</script>


