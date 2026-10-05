path = r"c:\Users\rasyaad\.gemini\antigravity-ide\scratch\aplikasi-cepat-analytics\Dokumentasi_DFD_PADU.md"
with open(path, "r", encoding="utf-8") as f:
    text = f.read()

bad_str = "$\\rightarrow$\nightarrow$\\rightarrow$"
text = text.replace(bad_str, "$\\rightarrow$")

# Also check for single newline variants
text = text.replace("$\\rightarrow$\r\nightarrow$\\rightarrow$", "$\\rightarrow$")
text = text.replace("$ightarrow$", "$\\rightarrow$")

with open(path, "w", encoding="utf-8") as f:
    f.write(text)

print("Replacement complete.")
