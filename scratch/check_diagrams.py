import xml.etree.ElementTree as ET

def check_file(filename):
    print("=== " + filename + " ===")
    tree = ET.parse(filename)
    root = tree.getroot()
    for i, d in enumerate(root.findall("diagram")):
        name = d.get("name")
        print(f"{i+1}. {name}")
        # check edges connected to P6
        mxGraphModel = d.find("mxGraphModel")
        if mxGraphModel is not None:
            root_elem = mxGraphModel.find("root")
            if root_elem is not None:
                p6_cells = [cell.get("id") for cell in root_elem.findall("mxCell") if "6.0" in (cell.get("value") or "") or "Terminasi" in (cell.get("value") or "")]
                edges = [cell for cell in root_elem.findall("mxCell") if cell.get("edge") == "1"]
                for p6_id in p6_cells:
                    connected = [e.get("id") for e in edges if e.get("source") == p6_id or e.get("target") == p6_id]
                    print(f"   -> Node '{p6_id}' has {len(connected)} edges: {connected}")

if __name__ == "__main__":
    check_file("Padu_Diagram_SuperAdmin.drawio")
    print()
    check_file("Padu_Diagram.drawio")
