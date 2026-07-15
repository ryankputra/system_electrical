-- ==========================================
-- REVISI DOSEN: STRUKTUR DATABASE BARU
-- ==========================================

-- Tabel Antrean Stok (FIFO BATCH)
CREATE TABLE IF NOT EXISTS s_stock_batches (
  id int(11) NOT NULL AUTO_INCREMENT,
  electric_id varchar(50) NOT NULL, 
  atch_number varchar(50) NOT NULL, 
  qty_initial int(11) NOT NULL, 
  qty_available int(11) NOT NULL, 
  created_at datetime NOT NULL, 
  PRIMARY KEY (id),
  FOREIGN KEY (electric_id) REFERENCES s_electric(electric_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabel Riwayat (Audit Trail) khusus untuk mutasi FIFO
CREATE TABLE IF NOT EXISTS s_history_fifo (
  id int(11) NOT NULL AUTO_INCREMENT,
  atch_id int(11) NOT NULL, 
  qty_deducted int(11) NOT NULL, 
  	ransaction_type varchar(50) NOT NULL, 
  created_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
