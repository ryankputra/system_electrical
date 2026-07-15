<?php if (empty($types)) : ?> <div class="container" style="margin-top: 6rem;">
        <div class="card p-4">
            <div class="alert alert-info mb-0">
                <h5 class="alert-heading">Tidak ada type electrical</h5>
                <p>Belum ada data type electrical. Anda dapat menambah type melalui halaman pengelolaan type.</p>
                <a href="<?= site_url('electric_type'); ?>" class="btn btn-primary">Kelola Type Electrical</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card mx-auto rounded-5 shadow border-0 mb-5 cust-rounded-card" style="margin-top: 5rem; max-width: 95%; border-radius: 2.5rem; overflow: hidden; background-clip: padding-box;">
        <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="m-0 mb-1">Pilih Type Electrical</h3>
                    <p class="text-muted mb-0">Pilih sebuah type untuk melihat daftar atau menambah electrical baru pada type tersebut.</p>
                </div>
                <div>
                    <a href="<?= site_url('electric') . '?clear_type=1'; ?>" class="btn btn-outline-secondary rounded-pill px-4">Lihat Semua</a>
                </div>
            </div>
            
            <!-- Search Section -->
            <div class="row mt-4">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <svg width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                            </svg>
                        </span>
                        <input type="text" id="searchTypes" class="form-control border-start-0" placeholder="Cari type electrical..." style="border-left: none; text-transform: uppercase;">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch" style="display: none;">
                            <svg width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block" id="searchResults"></small>
                </div>
            </div>
        </div>

        <div class="card-body px-lg-5 px-4 py-4">
            <div class="row electric-type-grid row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach (($types ?? []) as $type) : ?> <?php $img = !empty($type['image_url']) ? $type['image_url'] : base_url('assets/img/electric-default.png'); ?>
                    <?php $typeLabel = $type['type'] ?? ''; ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="thumb" style="height:160px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f8fafc;">
                                <?php
                                $imgSrc = $img;
                                $localPrefix = base_url('assets/img/electric_types/');
                                if (strpos($img, $localPrefix) === 0) {
                                    $filename = substr($img, strlen($localPrefix));
                                    $filePath = FCPATH . 'assets/img/electric_types/' . $filename;
                                    if (is_file($filePath)) {
                                        $imgSrc = $img . '?v=' . filemtime($filePath);
                                    }
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid" style="max-height:150px; max-width:100%" onerror="this.onerror=null;this.src='<?= base_url('assets/img/electric-default.png') ?>';">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title electric-type-label mb-2"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></h5>
                                <div class="d-grid gap-2 mt-3">
                                    <a href="<?= site_url('electric') . '?type_id=' . (int)$type['id']; ?>" class="btn btn-outline-primary btn-sm">Lihat Daftar</a>
                                    <?php if (is_admin()): ?>
                                        <a href="<?= site_url('electric/add/' . $type['id']); ?>" class="btn btn-primary btn-sm">Tambah Baru</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus handling for cards
            document.querySelectorAll('.electric-type-grid a').forEach(function(a) {
                a.addEventListener('focus', function() {
                    var c = a.closest('.card');
                    if (c) c.classList.add('focus');
                });
                a.addEventListener('blur', function() {
                    var c = a.closest('.card');
                    if (c) c.classList.remove('focus');
                });
            });

            // Search functionality
            const searchInput = document.getElementById('searchTypes');
            const clearSearchBtn = document.getElementById('clearSearch');
            const searchResults = document.getElementById('searchResults');
            const typeCards = document.querySelectorAll('.electric-type-grid .col');
            const totalTypes = typeCards.length;

            if (searchInput) {
                // Auto-uppercase input
                searchInput.addEventListener('input', function() {
                    const cursorPosition = this.selectionStart;
                    const originalValue = this.value;
                    this.value = this.value.toUpperCase();
                    
                    // Restore cursor position after converting to uppercase
                    this.setSelectionRange(cursorPosition, cursorPosition);
                    
                    performSearch();
                });

                function performSearch() {
                    const query = searchInput.value.trim();
                    let visibleCount = 0;

                    typeCards.forEach(card => {
                        const titleElement = card.querySelector('.electric-type-label');
                        if (titleElement) {
                            const title = titleElement.textContent.trim();
                            
                            if (query === '' || title.includes(query)) {
                                card.style.display = '';
                                visibleCount++;
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    });

                    // Update search results and clear button visibility
                    if (query.length > 0) {
                        clearSearchBtn.style.display = 'block';
                        searchResults.textContent = `Menampilkan ${visibleCount} dari ${totalTypes} type electrical`;
                        
                        // Show no results message if needed
                        showNoResultsMessage(visibleCount === 0);
                    } else {
                        clearSearchBtn.style.display = 'none';
                        searchResults.textContent = `${totalTypes} type electrical tersedia`;
                        showNoResultsMessage(false);
                    }
                }

                function showNoResultsMessage(show) {
                    let noResultsDiv = document.getElementById('noResultsMessage');
                    
                    if (show && !noResultsDiv) {
                        noResultsDiv = document.createElement('div');
                        noResultsDiv.id = 'noResultsMessage';
                        noResultsDiv.className = 'col-12 text-center py-5';
                        noResultsDiv.innerHTML = `
                            <div class="text-muted">
                                <svg width="48" height="48" fill="currentColor" class="bi bi-search mb-3" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                                <p class="mb-1 fw-medium">Tidak ada type electrical ditemukan</p>
                                <small>Coba gunakan kata kunci yang berbeda</small>
                            </div>
                        `;
                        document.querySelector('.electric-type-grid').appendChild(noResultsDiv);
                    } else if (!show && noResultsDiv) {
                        noResultsDiv.remove();
                    }
                }

                // Clear button functionality
                if (clearSearchBtn) {
                    clearSearchBtn.addEventListener('click', function() {
                        searchInput.value = '';
                        performSearch();
                        searchInput.focus();
                    });
                }

                // Initialize search results display
                performSearch();
            }
        });
    </script>
<?php endif; ?>