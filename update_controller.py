import re

path = r'c:\xampp\htdocs\electrical-system\application\controllers\History.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add print_sticker method before the closing brace of the class
method = r'''
    public function print_sticker() {
        ['history'] = ->History_model->getById();
        if (!['history']) {
            show_404();
        }
        ['electric'] = ->Electric_model->getById(['history']['electric_id']);
        
        // Month names and color coding
         = [
            '01' => ['name' => 'JANUARI', 'color' => '#E74C3C'],   // Merah
            '02' => ['name' => 'FEBRUARI', 'color' => '#3498DB'],  // Biru
            '03' => ['name' => 'MARET', 'color' => '#F1C40F'],     // Kuning
            '04' => ['name' => 'APRIL', 'color' => '#2ECC71'],     // Hijau
            '05' => ['name' => 'MEI', 'color' => '#E67E22'],       // Oranye
            '06' => ['name' => 'JUNI', 'color' => '#9B59B6'],      // Ungu
            '07' => ['name' => 'JULI', 'color' => '#8E44AD'],      // Ungu Tua
            '08' => ['name' => 'AGUSTUS', 'color' => '#34495E'],   // Biru Dongker
            '09' => ['name' => 'SEPTEMBER', 'color' => '#1ABC9C'], // Tosca
            '10' => ['name' => 'OKTOBER', 'color' => '#D35400'],   // Coklat
            '11' => ['name' => 'NOVEMBER', 'color' => '#7F8C8D'],  // Abu-abu
            '12' => ['name' => 'DESEMBER', 'color' => '#2C3E50']   // Hitam
        ];
        
         = strtotime(['history']['created_at']);
         = date('m', );
        
        ['month_name'] = []['name'];
        ['month_color'] = []['color'];
        
        ->load->view('history/print_sticker', );
    }
}
'''
content = re.sub(r'\}\s*$', method, content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated History.php")
