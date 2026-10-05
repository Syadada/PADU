path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Dokumentasi_DFD_PADU.md"
with open(path, "r", encoding="utf-8") as f:
    text = f.read()

# Fix multi-arrows and tab count
text = text.replace(r"$\rightarrow$\rightarrow$\rightarrow$", r"$\rightarrow$")
text = text.replace(r"$\rightarrow$\rightarrow$", r"$\rightarrow$")
text = text.replace("mencakup **10 tab interaktif**", "mencakup **14 tab interaktif**")

with open(path, "w", encoding="utf-8") as f:
    f.write(text)

print("Fixed text!")
