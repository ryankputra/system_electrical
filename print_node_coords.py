import xml.etree.ElementTree as ET

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd-level-1.drawio'
tree = ET.parse(path)
root = tree.getroot()

cells = root.findall('.//mxCell')
print("--- ALL VERTICES IN DFD LEVEL 1 ---")
for cell in cells:
    if cell.get('vertex') == '1':
        cid = cell.get('id')
        val = cell.get('value') or ''
        val_clean = val.replace('<br>', ' ').replace('<div>', ' ').replace('</div>', ' ').replace('<span>', '').replace('</span>', '').strip()
        geom = cell.find('mxGeometry')
        if geom is not None:
            x = geom.get('x') or '0'
            y = geom.get('y') or '0'
            w = geom.get('width') or '0'
            h = geom.get('height') or '0'
            print(f"Node ID {cid}: name='{val_clean}', x={x}, y={y}, w={w}, h={h}")
