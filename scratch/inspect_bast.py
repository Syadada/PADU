import openpyxl
import json

wb = openpyxl.load_workbook(r'C:\Users\rasyaad\Downloads\Versi 3_2026 Lampiran BAST Metadata DTSEN.xlsx', data_only=True)

for name in wb.sheetnames:
    ws = wb[name]
    print(f"\n==================== SHEET: {name} ({ws.max_row} rows, {ws.max_column} cols) ====================")
    rows = list(ws.iter_rows(values_only=True))
    for i, r in enumerate(rows[:10]):
        # Filter out trailing Nones
        clean_r = [str(x) if x is not None else "" for x in r[:12]]
        print(f"Row {i+1}: {clean_r}")
