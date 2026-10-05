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

print("Helper functions defined.")
