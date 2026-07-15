import xml.etree.ElementTree as ET

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd-level-1.drawio'
tree = ET.parse(path)
root = tree.getroot()

cells = root.findall('.//mxCell')
nodes = {}
edges = []

# First, collect all nodes
for cell in cells:
    cid = cell.get('id')
    val = cell.get('value')
    if val:
        val_clean = val.replace('<br>', ' ').replace('<div>', ' ').replace('</div>', ' ').replace('<span>', '').replace('</span>', '').strip()
        nodes[cid] = val_clean
    elif cell.get('vertex') == '1':
        nodes[cid] = f"[Unnamed Node {cid}]"

# Collect all edges
for cell in cells:
    if cell.get('edge') == '1':
        src = cell.get('source')
        tgt = cell.get('target')
        val = cell.get('value') or ''
        val_clean = val.replace('<br>', ' ').replace('<div>', ' ').replace('</div>', ' ').strip()
        edges.append({'id': cell.get('id'), 'src': src, 'tgt': tgt, 'label': val_clean})

print("--- NODES IN DFD LEVEL 1 ---")
for cid, name in nodes.items():
    if any(x in name for x in ['Supplier', '1.0', '2.0', '3.0', '4.0', '5.0', 'PO', 'Purchase']):
        print(f"ID {cid}: {name}")

print("\n--- EDGES IN DFD LEVEL 1 (CONNECTED TO SUPPLIER OR PROCESS 4.0) ---")
for e in edges:
    src_name = nodes.get(e['src'], f"[ID:{e['src']}]")
    tgt_name = nodes.get(e['tgt'], f"[ID:{e['tgt']}]")
    
    # Filter for Supplier or Process 4.0 (which is P4)
    if 'Supplier' in src_name or 'Supplier' in tgt_name or '4.0' in src_name or '4.0' in tgt_name:
        print(f"Edge ID {e['id']}: {src_name} ---> {tgt_name} | Label: '{e['label']}'")
