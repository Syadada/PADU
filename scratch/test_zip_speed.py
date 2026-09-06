import time
import numpy as np
import pandas as pd

total_rows = 1000000
nik_s = pd.Series(['3201011234567890'] * total_rows)
kk_s = pd.Series(['3201011234567890'] * total_rows)
nama_s = pd.Series(['Masyarakat'] * total_rows)
gaji_s = pd.Series([5000000.0] * total_rows)
usia_s = pd.Series([30] * total_rows)
status_arr = np.array(['Valid'] * total_rows)
issues_arr = np.array(['[]'] * total_rows)

# Method 1: list comprehension with .iat[i]
t0 = time.time()
# batch1 = [(nik_s.iat[i], kk_s.iat[i], nama_s.iat[i], float(gaji_s.iat[i]), int(usia_s.iat[i]), status_arr[i], issues_arr[i]) for i in range(100000)]
# t1 = time.time()
# print(f"100k rows with .iat[i]: {t1 - t0:.3f} sec")

# Method 2: numpy to_numpy() + zip
t0 = time.time()
nik_a = nik_s.to_numpy()
kk_a = kk_s.to_numpy()
nama_a = nama_s.to_numpy()
gaji_a = gaji_s.to_numpy()
usia_a = usia_s.to_numpy()

batch2 = list(zip(nik_a, kk_a, nama_a, gaji_a, gaji_a, usia_a, [None]*total_rows, [None]*total_rows, ['Kepala keluarga']*total_rows, [None]*total_rows, [None]*total_rows, ['Perdagangan']*total_rows, ['001']*total_rows, ['001']*total_rows, ['Jl. Mawar']*total_rows, ['{}']*total_rows, status_arr, issues_arr, ['2026-09-06']*total_rows, ['2026-09-06']*total_rows))
t1 = time.time()
print(f"1,000,000 rows with numpy arrays + list(zip(...)): {t1 - t0:.3f} sec")
