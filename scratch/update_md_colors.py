import re

path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Dokumentasi_DFD_PADU.md"
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# Replace entity and process classDefs in Mermaid
# from whatever fill/color to fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000000;
content = re.sub(
    r'classDef entity fill:[^,]+,stroke:[^,]+,stroke-width:[^,]+,color:[^;]+;',
    'classDef entity fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;',
    content
)

content = re.sub(
    r'classDef process fill:[^,]+,stroke:[^,]+,stroke-width:[^,]+,color:[^;]+;',
    'classDef process fill:#66B2FF,stroke:#007ACC,stroke-width:2px,color:#000;',
    content
)

# Also add a styling note in the documentation
if "Warna Tema Entitas & Proses" not in content:
    note = """
> [!NOTE]
> **Pembaruan Palet Visual**: Sesuai preferensi, seluruh elemen **Entitas Luar** dan **Sub-Proses** pada seluruh tingkatan DFD (Level 0, Level 1, dan Level 2) kini menggunakan warna latar **`#66B2FF`** (Pastel Sky Blue) dengan **tipografi teks berwarna hitam (`#000000`)** untuk keterbacaan kontras optimal, sembari tetap mempertahankan kode warna fungsional pada masing-masing garis penghubung (*orthogonal edges*).
"""
    content = content.replace("---", "---\n" + note, 1)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Updated Dokumentasi_DFD_PADU.md with #66B2FF and black text styling!")
