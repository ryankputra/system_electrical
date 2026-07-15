import os

def fix_electric():
    path = r'c:\xampp\htdocs\electrical-system\application\controllers\Electric.php'
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # find the comment
    idx = content.find('// ==========================================')
    if idx != -1:
        content = content[:idx]
    
    new_method = '''
    // ==========================================
    // REVISI DOSEN: AJAX DROPDOWN CASCADE
    // ==========================================
    public function get_items_by_type() {
         = ->input->post('type_id', TRUE);
        
        // Mencegah SQL Injection via query binding CodeIgniter
         = ->db->get_where('as_electric', ['type_id' => ])->result_array();
        
        // Kembalikan data dalam format JSON
        echo json_encode();
    }
}'''
    new_method = new_method.replace('', '$' + 'type_id').replace('', '$' + 'this').replace('', '$' + 'items')
    
    content += new_method
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

def fix_history():
    path = r'c:\xampp\htdocs\electrical-system\application\models\History_model.php'
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    idx = content.find('// ==========================================')
    if idx != -1:
        content = content[:idx]
        
    new_method = '''
    // ==========================================
    // REVISI DOSEN: LOGIKA KONTROL STOK FIFO 
    // ==========================================
    public function reduce_stock_fifo(, ) {
         = ;

        // 1. Ambil batch yang masih ada sisa stok (qty_available > 0)
        // 2. Wajib urutkan dari yang paling tua (created_at ASC) -> INI INTI FIFO
        ->db->where('electric_id', );
        ->db->where('qty_available >', 0);
        ->db->order_by('created_at', 'ASC');
         = ->db->get('as_stock_batches')->result_array();

        ->db->trans_start(); // Mulai transaksi database (ACID)

        foreach ( as ) {
            if ( <= 0) break; // Kebutuhan terpenuhi

             = ['id'];
             = ['qty_available'];

            if ( >= ) {
                // Skenario A: Sisa stok cukup
                 =  - ;
                ->db->where('id', )->update('as_stock_batches', ['qty_available' => ]);
                ->record_history_fifo(, );
                 = 0;
            } else {
                // Skenario B: Sisa stok kurang (habiskan batch ini)
                ->db->where('id', )->update('as_stock_batches', ['qty_available' => 0]);
                ->record_history_fifo(, );
                 -= ; 
            }
        }

        if ( > 0) {
            ->db->trans_rollback(); // Batalkan pemotongan jika stok global ternyata tidak cukup
            return FALSE; 
        }

        ->db->trans_complete(); 
        return ->db->trans_status();
    }

    private function record_history_fifo(, ) {
         = [
            'batch_id' => ,
            'qty_deducted' => ,
            'transaction_type' => 'Keluar - Work Order (FIFO)',
            'created_at' => date('Y-m-d H:i:s')
        ];
        ->db->insert('as_history_fifo', );
    }
}'''
    content += new_method
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

fix_electric()
fix_history()
print("Fixed files!")
