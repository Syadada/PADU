import xml.sax.saxutils as saxutils
import xml.etree.ElementTree as ET
import os

def esc(s):
    return saxutils.escape(s, {'"': '&quot;'})

def make_vertex(cid, val, style, x, y, w, h, parent="1"):
    if "html=1" not in style:
        style = "html=1;" + style
    return f'''        <mxCell id="{cid}" value="{esc(val)}" style="{style}" vertex="1" parent="{parent}">
          <mxGeometry x="{x}" y="{y}" width="{w}" height="{h}" as="geometry" />
        </mxCell>'''

def make_edge(cid, val, src, tgt, style, points=None, parent="1"):
    if "html=1" not in style:
        style = "html=1;" + style
    pts_xml = ""
    if points:
        pts_items = "".join([f'<mxPoint x="{px}" y="{py}" />' for px, py in points])
        pts_xml = f'''\n            <Array as="points">{pts_items}</Array>'''

    return f'''        <mxCell id="{cid}" value="{esc(val)}" style="{style}" edge="1" parent="{parent}" source="{src}" target="{tgt}">
          <mxGeometry relative="1" as="geometry">{pts_xml}
          </mxGeometry>
        </mxCell>'''

PROC_FILL = "#66B2FF"
PROC_BORDER = "#007ACC"
ENT_FILL = "#66B2FF"
ENT_BORDER = "#007ACC"
TEXT_COLOR = "#000000"

def proc_style(border_col=PROC_BORDER, fill_col=PROC_FILL):
    return f"rounded=1;arcSize=20;whiteSpace=wrap;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;"

def ent_style(border_col=ENT_BORDER, fill_col=ENT_FILL):
    return f"rounded=1;arcSize=10;whiteSpace=wrap;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor={TEXT_COLOR};verticalAlign=middle;align=center;shadow=1;"

def store_style(border_col, fill_col):
    return f"shape=partialRectangle;right=0;left=0;html=1;fillColor={fill_col};strokeColor={border_col};strokeWidth=2;fontColor=#ffffff;align=center;verticalAlign=middle;"

blue_in = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#0284c7;strokeWidth=2;fontColor=#0369a1;fontSize=10;fontStyle=1;labelBackgroundColor=#f0f9ff;labelBorderColor=#bae6fd;"
green_out = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#059669;strokeWidth=2;fontColor=#047857;fontSize=10;fontStyle=1;labelBackgroundColor=#ecfdf5;labelBorderColor=#a7f3d0;"
purple_flow = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#7c3aed;strokeWidth=2;fontColor=#6d28d9;fontSize=10;fontStyle=1;labelBackgroundColor=#f5f3ff;labelBorderColor=#ddd6fe;"
amber_cam = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#d97706;strokeWidth=2;fontColor=#b45309;fontSize=10;fontStyle=1;labelBackgroundColor=#fffbeb;labelBorderColor=#fde68a;"
red_alert = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#dc2626;strokeWidth=2;fontColor=#991b1b;fontSize=10;fontStyle=1;labelBackgroundColor=#fef2f2;labelBorderColor=#fecaca;"
indigo_sys = "edgeStyle=orthogonalEdgeStyle;rounded=0;strokeColor=#4f46e5;strokeWidth=2;fontColor=#3730a3;fontSize=10;fontStyle=1;labelBackgroundColor=#eef2ff;labelBorderColor=#c7d2fe;"

def build_diagram(diag_id, diag_name, cells, w=2400, h=1400):
    body = "\n".join(cells)
    return f'''  <diagram id="{diag_id}" name="{esc(diag_name)}">
    <mxGraphModel dx="{w}" dy="{h}" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="{w}" pageHeight="{h}" math="0" shadow="0">
      <root>
        <mxCell id="0" />
        <mxCell id="1" parent="0" />
{body}
      </root>
    </mxGraphModel>
  </diagram>'''

# Texts
e1_asis_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Menyalin file CSV/XLSX ke src-dtsen/<br>
• Memilih file &amp; trigger impor lokal<br>
• Memantau progress bar impor<br>
• Mengatur filter NIK, Wilayah, Desil<br>
• Melihat tabel data mentah (as-is)<br>
• Mengunduh file CSV hasil filter/error
</div>'''

e1_tobe_standard = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">OPERATOR DATA / ANALIS</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Mengunggah berkas mentah DTSEN<br>
• Memilih Modul: Data Mikro vs Statistik Wilayah<br>
• Menjalankan filter dinamis &amp; pencarian NIK/KK<br>
• Meninjau data mikro terpadu (Transliterasi Label FK)<br>
• Menganalisis agregasi wilayah (7 Metrik Statistik)<br>
• Mengunduh paket ZIP arsip olahan &amp; log audit<br>
• Menerima notifikasi peringatan layar
</div>'''

e2_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#000000;">SENSOR KAMERA / WEBCAM</div>
<hr style="border:1px solid #007ACC;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#000000;padding:0 6px;">
• Headless OpenCV Capture (DSHOW)<br>
• Frame visual berkala (loop 0.2s)<br>
• Snapshot verifikasi biometrik
</div>'''

e3_tobe_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR</div>
<div style="font-size:15px;font-weight:bold;margin-top:2px;color:#000000;">SISTEM OPERASI WINDOWS</div>
<hr style="border:1px solid #007ACC;margin:8px 0;">
<div style="text-align:left;font-size:11px;line-height:1.5;color:#000000;padding:0 6px;">
• Windows API (user32.dll)<br>
• Layar LockWorkStation (Win+L)<br>
• Sinyal OS (SIGTERM / atexit)
</div>'''

e4_superadmin_val = '''<div style="font-size:12px;color:#000000;font-weight:bold;">ENTITAS LUAR KHUSUS</div>
<div style="font-size:16px;font-weight:bold;margin-top:3px;color:#000000;">👑 SUPER ADMIN (BAPPENAS / BPS)</div>
<hr style="border:1px solid #007ACC;margin:10px 0;">
<div style="text-align:left;font-size:11px;line-height:1.6;color:#000000;padding:0 8px;">
• Mengakses Dedicated Library Management Page<br>
• Memperbarui Kamus Kode Referensi (Bappenas)<br>
• Mengatur Aturan Kualitas &amp; Variabel Baru<br>
• Mengontrol Hak Akses &amp; Manajemen Pengguna<br>
• Mengaudit Log Perubahan Master Metadata
</div>'''

# Helper to read existing Padu_Diagram.drawio diagram by id
def extract_diagram_from_file(filepath, diag_id):
    tree = ET.parse(filepath)
    root = tree.getroot()
    for d in root.findall("diagram"):
        if d.get("id") == diag_id:
            return ET.tostring(d, encoding="unicode")
    return None

def extract_all_diagrams(filepath):
    tree = ET.parse(filepath)
    root = tree.getroot()
    diags = {}
    for d in root.findall("diagram"):
        diags[d.get("id")] = ET.tostring(d, encoding="unicode")
    return diags

print("Base setup ready.")
