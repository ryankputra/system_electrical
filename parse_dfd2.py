import xml.etree.ElementTree as ET
import re

path = r'C:\Users\Ryan\Documents\TA\diagrams\dfd_level_2_ganesarson.drawio'

try:
    tree = ET.parse(path)
    root = tree.getroot()
    
    nodes = []
    edges = []
    
    for cell in root.iter('mxCell'):
        val = cell.get('value')
        if val:
            # Clean HTML tags from value
            val = re.sub('<[^<]+>', ' ', val).strip()
            
            if cell.get('edge') == '1':
                source = cell.get('source', '?')
                target = cell.get('target', '?')
                edges.append(f"EDGE: {val} (Source: {source} -> Target: {target})")
            elif cell.get('vertex') == '1':
                nodes.append(f"NODE [{cell.get('id')}]: {val}")

    print("Nodes:")
    for n in nodes: print(n)
    print("\nEdges:")
    for e in edges: print(e)
except Exception as e:
    print("Error:", e)
