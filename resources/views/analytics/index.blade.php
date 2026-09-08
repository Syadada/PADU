@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="analyticsApp()">

    <!-- Banner Sambutan & Tombol Aksi Utama -->
    <div class="card-simple p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <span>📊</span> Selamat Datang di PADU v1.02 (Pengolah & Analisis Data Terpadu)
            </h2>
            <p class="text-sm text-slate-600">
                Hitung statistik (Jumlah, Max, Min, Rata-rata), umur detail (Tahun & Bulan), status kehidupan, serta kelola data hingga tingkat RT/RW.
            </p>
        </div>

        <!-- Tombol Aksi Utama -->
        <div class="flex flex-wrap items-center gap-2">
            <button @click="showAddModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                ✍️ Input Data Baru
            </button>

            <button @click="showExportModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition-all flex items-center gap-1.5">
                📥 Unduh CSV
            </button>

            <form action="{{ route('analytics.generate') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-medium text-xs shadow-sm transition-all flex items-center gap-1.5">
                    ⚡ Demo (+120 Data)
                </button>
            </form>
        </div>
    </div>

    <!-- 1. Kartu Ringkasan Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Kartu 1: Jumlah Total Orang -->
        <div class="card-simple p-5 border-l-4 border-l-blue-600">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase">
                <span>Jumlah Total Warga</span>
                <span class="text-lg">👥</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($totalRecord, 0, ',', '.') }}</span>
                <span class="text-sm font-medium text-slate-600">Orang</span>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                <span class="text-emerald-700">🟢 {{ $totalMasihHidup }} Masih Hidup</span>
                <span class="text-slate-600">⚫ {{ $totalMeninggal }} Meninggal</span>
            </div>
        </div>

        <!-- Kartu 2: Gaji Tertinggi (Max) --->
        <div class="card-simple p-5 border-l-4 border-l-emerald-500">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase">
                <span>Gaji Tertinggi (Max)</span>
                <span class="text-lg">💰</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-bold text-emerald-700">
                    <span x-show="isMasked">{{ 'Rp ' . (strlen((string)floor($nilaiTerbesar)) >= 8 ? substr((string)floor($nilaiTerbesar), 0, 2) : substr((string)floor($nilaiTerbesar), 0, 1)) . '.xxx.xxx' }}</span>
                    <span x-show="!isMasked">Rp {{ number_format($nilaiTerbesar, 0, ',', '.') }}</span>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 truncate">
                @if($recordTerbesar)
                    Pemilik: <span x-show="isMasked">{{ $recordTerbesar->masked_nama }}</span><span x-show="!isMasked">{{ $recordTerbesar->nama_lengkap }}</span>
                @else
                    -
                @endif
            </p>
        </div>

        <!-- Kartu 3: Gaji Terendah (Min) --->
        <div class="card-simple p-5 border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase">
                <span>Gaji Terendah (Min)</span>
                <span class="text-lg">📉</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-bold text-amber-700">
                    <span x-show="isMasked">{{ 'Rp ' . (strlen((string)floor($nilaiTerkecil)) >= 8 ? substr((string)floor($nilaiTerkecil), 0, 2) : substr((string)floor($nilaiTerkecil), 0, 1)) . '.xxx.xxx' }}</span>
                    <span x-show="!isMasked">Rp {{ number_format($nilaiTerkecil, 0, ',', '.') }}</span>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 truncate">
                @if($recordTerkecil)
                    Pemilik: <span x-show="isMasked">{{ $recordTerkecil->masked_nama }}</span><span x-show="!isMasked">{{ $recordTerkecil->nama_lengkap }}</span>
                @else
                    -
                @endif
            </p>
        </div>

        <!-- Kartu 4: Gaji Rata-Rata (Average) -->
        <div class="card-simple p-5 border-l-4 border-l-purple-600">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold uppercase">
                <span>Gaji Rata-Rata (Average)</span>
                <span class="text-lg">📊</span>
            </div>
            <div class="mt-2">
                <span class="text-2xl font-bold text-purple-700">Rp {{ number_format($nilaiRataRata, 0, ',', '.') }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Akumulasi Rp {{ number_format($totalJumlah/1000000, 1, ',', '.') }} Juta</p>
        </div>
    </div>

    <!-- 2. Ringkasan Demografi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Kelompok 1: Jenis Kelamin -->
        <div class="card-simple p-5 space-y-3">
            <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-2">
                <span>👨‍👩‍👧‍👦</span> Jenis Kelamin
            </h3>
            <div class="space-y-2">
                @foreach($breakdownJK as $jk)
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 text-sm block">{{ $jk->jenis_kelamin }}</span>
                            <span class="text-xs text-slate-500">Rata-rata Gaji: Rp {{ number_format($jk->avg_gaji, 0, ',', '.') }}</span>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-sm">
                            {{ $jk->total }} Orang
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kelompok 2: Pendidikan Terakhir -->
        <div class="card-simple p-5 space-y-3">
            <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-2">
                <span>🎓</span> Pendidikan Terakhir
            </h3>
            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                @foreach($breakdownPendidikan as $pend)
                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800">{{ $pend->pendidikan_terakhir }}</span>
                        <span class="text-slate-600">{{ $pend->total }} Orang (Avg Rp {{ number_format($pend->avg_gaji / 1000000, 1, ',', '.') }} Juta)</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kelompok 3: Kelompok Umur -->
        <div class="card-simple p-5 space-y-3">
            <h3 class="font-bold text-slate-900 border-b border-slate-200 pb-2 flex items-center gap-2">
                <span>🎂</span> Kelompok Umur / Usia
            </h3>
            <div class="space-y-2">
                @foreach($breakdownUsia as $rentang => $jumlahUsia)
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">{{ $rentang }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-slate-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ round(($jumlahUsia / max(1, $totalRecord)) * 100) }}%"></div>
                            </div>
                            <span class="font-bold text-slate-800 min-w-[50px] text-right">{{ $jumlahUsia }} Orang</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Section Tabel Data -->
    <div id="tabel-data" class="card-simple p-5 space-y-4 scroll-mt-24">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2 flex-wrap">
                    <span>📋 Tabel Data & Fitur CRUD Wilayah</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        📄 Halaman {{ $records->currentPage() }} dari {{ $records->lastPage() }}
                    </span>
                </h3>
                <p class="text-xs text-slate-500">
                    Menampilkan data ke-<strong>{{ $records->firstItem() ?? 0 }}</strong> s/d <strong>{{ $records->lastItem() ?? 0 }}</strong> dari total <strong>{{ $records->total() }}</strong> data warga
                </p>
            </div>

            <!-- Tab Switcher -->
            <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl border border-slate-200">
                <a href="{{ route('analytics.index', array_merge(request()->query(), ['status_trash' => 'aktif'])) }}#tabel-data"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $statusTrash === 'aktif' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>📋 Data Aktif</span>
                </a>
                <a href="{{ route('analytics.index', array_merge(request()->query(), ['status_trash' => 'sampah'])) }}#tabel-data"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $statusTrash === 'sampah' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>🗑️ Tempat Sampah</span>
                    @if($totalSampah > 0)
                        <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $statusTrash === 'sampah' ? 'bg-white text-rose-700' : 'bg-rose-200 text-rose-800' }}">{{ $totalSampah }}</span>
                    @endif
                </a>
            </div>
        </div>

        @if($statusTrash === 'sampah')
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>⚠️</span>
                    <span><strong>Area Tempat Sampah (Soft Delete):</strong> Data yang berusia lebih dari <strong>1 minggu (7 hari)</strong> akan otomatis dihapus permanen oleh sistem.</span>
                </div>
            </div>
        @endif

        <!-- Filter & Search Form -->
        <form method="GET" action="{{ route('analytics.index') }}#tabel-data" class="space-y-3">
            <input type="hidden" name="status_trash" value="{{ $statusTrash }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Smart Search:</label>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Ketik Nama, NIK, Kec, Kel..." 
                           class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin:</label>
                    <select name="jenis_kelamin" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm">
                        <option value="semua" {{ $jenisKelamin == 'semua' ? 'selected' : '' }}>Semua Kelamin</option>
                        <option value="Laki-laki" {{ $jenisKelamin == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ $jenisKelamin == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan:</label>
                    <select name="pendidikan" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm">
                        <option value="semua" {{ $pendidikan == 'semua' ? 'selected' : '' }}>Semua Pendidikan</option>
                        @foreach(['SD','SMP','SMA/K','D3','S1','S2','S3'] as $p)
                            <option value="{{ $p }}" {{ $pendidikan == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehidupan:</label>
                    <select name="status_kehidupan" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm font-semibold">
                        <option value="semua" {{ $statusKehidupan == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="Masih Hidup" {{ $statusKehidupan == 'Masih Hidup' ? 'selected' : '' }}>🟢 Masih Hidup</option>
                        <option value="Meninggal Dunia" {{ $statusKehidupan == 'Meninggal Dunia' ? 'selected' : '' }}>⚫ Meninggal Dunia</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Umur:</label>
                    <select name="rentang_usia" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm">
                        <option value="semua" {{ $rentangUsia == 'semua' ? 'selected' : '' }}>Semua Umur</option>
                        <option value="< 20" {{ $rentangUsia == '< 20' ? 'selected' : '' }}>&lt; 20 Tahun</option>
                        <option value="20-30" {{ $rentangUsia == '20-30' ? 'selected' : '' }}>20 - 30 Tahun</option>
                        <option value="31-40" {{ $rentangUsia == '31-40' ? 'selected' : '' }}>31 - 40 Tahun</option>
                        <option value="41-50" {{ $rentangUsia == '41-50' ? 'selected' : '' }}>41 - 50 Tahun</option>
                        <option value="> 50" {{ $rentangUsia == '> 50' ? 'selected' : '' }}>&gt; 50 Tahun</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-200">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Acuan Tanggal:</label>
                    <select name="tgl_jenis" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm">
                        <option value="created_at" {{ $tglJenis == 'created_at' ? 'selected' : '' }}>Tanggal Ditambahkan (Input)</option>
                        <option value="tanggal_lahir" {{ $tglJenis == 'tanggal_lahir' ? 'selected' : '' }}>Tanggal Lahir</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dari Tanggal:</label>
                    <input type="date" name="tgl_awal" value="{{ $tglAwal }}" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal:</label>
                    <input type="date" name="tgl_akhir" value="{{ $tglAkhir }}" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Urutkan Tampilan:</label>
                    <select name="urutkan" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-800 text-sm font-semibold text-blue-800">
                        <option value="terbaru" {{ $urutkan == 'terbaru' ? 'selected' : '' }}>🆕 Terbaru Ditambahkan</option>
                        <option value="terlama" {{ $urutkan == 'terlama' ? 'selected' : '' }}>📜 Terlama Ditambahkan</option>
                        <option value="gaji_max" {{ $urutkan == 'gaji_max' ? 'selected' : '' }}>💰 Gaji Tertinggi ➔ Terendah</option>
                        <option value="gaji_min" {{ $urutkan == 'gaji_min' ? 'selected' : '' }}>📉 Gaji Terendah ➔ Tertinggi</option>
                        <option value="usia_tua" {{ $urutkan == 'usia_tua' ? 'selected' : '' }}>👴 Usia Tertua ➔ Termuda</option>
                        <option value="usia_muda" {{ $urutkan == 'usia_muda' ? 'selected' : '' }}>👶 Usia Termuda ➔ Tertua</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center pt-1">
                @if($tglAwal || $tglAkhir || $search || $jenisKelamin !== 'semua' || $pendidikan !== 'semua' || $rentangUsia !== 'semua' || $statusKehidupan !== 'semua' || $urutkan !== 'terbaru')
                    <a href="{{ route('analytics.index', ['status_trash' => $statusTrash]) }}#tabel-data" class="px-3 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold text-xs">
                        ↺ Reset Filter
                    </a>
                @else
                    <div></div>
                @endif
                <button type="submit" class="px-5 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-sm">
                    🔍 Tetapkan Filter
                </button>
            </div>
        </form>

        <!-- Tabel Data Utama dengan Tata Letak Rapi Tanpa Patah Baris (whitespace-nowrap) -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 mt-4">
            <table class="w-full text-left text-sm text-slate-800 border-collapse">
                <thead class="bg-slate-100 text-xs font-bold uppercase text-slate-700 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 whitespace-nowrap">NIK</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Nama Lengkap</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Status Kehidupan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Kelamin</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Pendidikan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Tgl Lahir & Umur Detail</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Gaji Bulanan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Wilayah Alamat</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">{{ $statusTrash === 'sampah' ? 'Batas Hapus' : 'Ditambahkan' }}</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">Aksi CRUD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($records as $rec)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            <!-- NIK -->
                            <td class="px-4 py-3.5 font-mono text-xs whitespace-nowrap">
                                <span x-show="isMasked" class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded font-bold">{{ $rec->masked_nik }}</span>
                                <span x-show="!isMasked" class="text-slate-900 font-semibold">{{ $rec->nik }}</span>
                            </td>

                            <!-- Nama -->
                            <td class="px-4 py-3.5 font-semibold whitespace-nowrap">
                                <span x-show="isMasked" class="text-slate-600">{{ $rec->masked_nama }}</span>
                                <span x-show="!isMasked" class="text-slate-900">{{ $rec->nama_lengkap }}</span>
                            </td>

                            <!-- Status Kehidupan Badge Rapi -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($rec->status_kehidupan === 'Meninggal Dunia')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-800 text-white inline-flex items-center gap-1 shadow-sm">
                                        ⚫ Meninggal
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 inline-flex items-center gap-1">
                                        🟢 Masih Hidup
                                    </span>
                                @endif
                            </td>

                            <!-- Kelamin -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $rec->jenis_kelamin == 'Laki-laki' ? 'bg-sky-100 text-sky-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ $rec->jenis_kelamin }}
                                </span>
                            </td>

                            <!-- Pendidikan -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700">
                                    {{ $rec->pendidikan_terakhir }}
                                </span>
                            </td>

                            <!-- Tanggal Lahir & Umur Detail -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                <div class="flex items-center gap-2">
                                    <span x-show="isMasked" class="text-slate-600 font-mono">{{ $rec->masked_tanggal_lahir }}</span>
                                    <span x-show="!isMasked" class="text-slate-800 font-medium">{{ $rec->tanggal_lahir->format('d/m/Y') }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-800 font-bold text-[11px] inline-flex items-center gap-1">
                                        <span>⌛</span>
                                        <span>{{ $rec->usia_detail }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- Gaji -->
                            <td class="px-4 py-3.5 font-bold text-xs whitespace-nowrap">
                                <span x-show="isMasked" class="text-emerald-700">{{ $rec->masked_gaji }}</span>
                                <span x-show="!isMasked" class="text-emerald-700">{{ $rec->formatted_gaji }}</span>
                            </td>

                            <!-- Wilayah Lengkap -->
                            <td class="px-4 py-3.5 text-xs text-slate-700 font-medium max-w-[220px] truncate whitespace-nowrap" title="{{ $rec->wilayah_lengkap }}">
                                📍 {{ $rec->wilayah_lengkap }}
                            </td>

                            <!-- Tanggal Ditambahkan -->
                            <td class="px-4 py-3.5 text-xs font-medium whitespace-nowrap">
                                @if($statusTrash === 'sampah')
                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[11px]">
                                        ⏳ {{ $rec->sisa_hari_permanen }}
                                    </span>
                                @else
                                    <span class="text-slate-600 font-mono text-xs">
                                        {{ $rec->created_at ? $rec->created_at->format('d/m/Y H:i') : '-' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Tombol Aksi CRUD -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($statusTrash === 'sampah')
                                        <form action="{{ route('analytics.restore', $rec->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm" title="Pulihkan Data">
                                                ♻️ Pulihkan
                                            </button>
                                        </form>

                                        <form action="{{ route('analytics.forceDelete', $rec->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus permanen data ini selamanya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm" title="Hapus Selamanya">
                                                ❌ Permanen
                                            </button>
                                        </form>
                                    @else
                                        <button @click="openPreview({{ $rec->id }})" 
                                                class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm" title="Lihat Detail">
                                            🔍 Detail
                                        </button>

                                        <button @click="openEditModal({{ $rec->id }})" 
                                                class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm" title="Edit Data">
                                            ✏️ Edit
                                        </button>

                                        <form action="{{ route('analytics.destroy', $rec->id) }}" method="POST" class="inline" onsubmit="return confirm('Pindahkan data ke tempat sampah? (Bisa dipulihkan dalam 7 hari)')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-700 border border-rose-300 text-xs font-bold shadow-sm" title="Pindahkan ke Tempat Sampah">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-500 font-medium">
                                <div class="space-y-2">
                                    <span class="text-4xl block">📭</span>
                                    <p class="font-bold text-slate-700 text-base">Belum ada data di dalam database.</p>
                                    <p class="text-xs text-slate-500">Klik tombol <strong>"✍️ Input Data Baru"</strong> di atas atau tekan <strong>"⚡ Demo (+120 Data)"</strong> untuk mengisi sampel data otomatis.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200 text-xs text-slate-600 font-medium">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-bold border border-blue-200">
                    📄 Halaman {{ $records->currentPage() }} dari {{ $records->lastPage() }}
                </span>
                <span>
                    Menampilkan <strong>{{ $records->firstItem() ?? 0 }}</strong> – <strong>{{ $records->lastItem() ?? 0 }}</strong> dari <strong>{{ $records->total() }}</strong> Data
                </span>
            </div>
            <div>
                {{ $records->links('vendor.pagination.compact') }}
            </div>
        </div>
    </div>

    <!-- MODAL 0: Form Input Data Baru -->
    <div x-show="showAddModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        
        <form action="{{ route('analytics.store') }}" method="POST" 
              class="bg-white max-w-2xl w-full rounded-2xl p-6 border border-slate-200 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                    ✍️ Form Input Data Orang & Wilayah Lengkap
                </h3>
                <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-xl px-2">✕</button>
            </div>

            <div class="space-y-3">
                <h4 class="font-bold text-xs uppercase text-blue-700 border-b border-blue-100 pb-1">1. Data Identitas Diri</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap: *</label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Contoh: Budi Santoso" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">NIK (16 Digit - Opsional):</label>
                        <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" placeholder="Boleh dikosongkan (otomatis dibuatkan NIK)" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 font-mono text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehidupan: *</label>
                        <select name="status_kehidupan" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-bold text-slate-800">
                            <option value="Masih Hidup" selected>🟢 Masih Hidup</option>
                            <option value="Meninggal Dunia">⚫ Meninggal Dunia</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin: *</label>
                        <select name="jenis_kelamin" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan Terakhir: *</label>
                        <select name="pendidikan_terakhir" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA/K" selected>SMA/K</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir: *</label>
                        <input type="date" name="tanggal_lahir" value="1995-05-15" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Bulanan (Rp): *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-500">Rp</span>
                            <input type="text" 
                                   x-model="formattedGaji"
                                   @input="formatGajiInput($event)"
                                   placeholder="1.000.000" 
                                   required
                                   class="w-full pl-9 pr-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-bold text-emerald-800">
                            <input type="hidden" name="gaji_bulanan" :value="rawGaji">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="font-bold text-xs uppercase text-blue-700 border-b border-blue-100 pb-1">2. Data Alamat & Wilayah Regional</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi:</label>
                        <input type="text" name="provinsi" value="DKI Jakarta" placeholder="Contoh: DKI Jakarta" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten:</label>
                        <input type="text" name="kota_kabupaten" value="Jakarta Selatan" placeholder="Contoh: Jakarta Selatan" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan:</label>
                        <input type="text" name="kecamatan" value="Kebayoran Baru" placeholder="Contoh: Kebayoran Baru" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelurahan / Desa:</label>
                        <input type="text" name="kelurahan_desa" value="Senayan" placeholder="Contoh: Senayan" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">RT / RW:</label>
                        <input type="text" name="rt_rw" value="RT 003 / RW 005" placeholder="Contoh: RT 003 / RW 005" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Pernikahan:</label>
                        <select name="status_pernikahan" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                            <option value="Belum Menikah">Belum Menikah</option>
                            <option value="Menikah">Menikah</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Jalan & Nomor Rumah:</label>
                        <input type="text" name="alamat_lengkap" value="Jl. Sudirman No. 45" placeholder="Contoh: Jl. Sudirman No. 45" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email (Opsional):</label>
                        <input type="email" name="email" placeholder="Contoh: budi@gmail.com (Opsional)" 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-200">
                <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-slate-200 text-xs font-bold text-slate-700">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">Simpan Data Baru</button>
            </div>
        </form>
    </div>

    <!-- MODAL EDIT: Form Edit -->
    <div x-show="showEditModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        
        <form :action="'/update-data/' + editData.id" method="POST" 
              class="bg-white max-w-2xl w-full rounded-2xl p-6 border border-slate-200 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            @csrf
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                    ✏️ Edit Data Perorangan & Status Kehidupan (#<span x-text="editData.id"></span>)
                </h3>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-xl px-2">✕</button>
            </div>

            <div class="space-y-3">
                <h4 class="font-bold text-xs uppercase text-blue-700 border-b border-blue-100 pb-1">1. Data Diri & Status Kehidupan</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap: *</label>
                        <input type="text" name="nama_lengkap" x-model="editData.nama_lengkap" required 
                               class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehidupan: *</label>
                        <select name="status_kehidupan" x-model="editData.status_kehidupan" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-bold text-slate-800">
                            <option value="Masih Hidup">🟢 Masih Hidup</option>
                            <option value="Meninggal Dunia">⚫ Meninggal Dunia</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin: *</label>
                        <select name="jenis_kelamin" x-model="editData.jenis_kelamin" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan Terakhir: *</label>
                        <select name="pendidikan_terakhir" x-model="editData.pendidikan_terakhir" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA/K">SMA/K</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir: *</label>
                        <input type="date" name="tanggal_lahir" x-model="editData.tanggal_lahir" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Bulanan (Rp): *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-500">Rp</span>
                            <input type="text" 
                                   x-model="editFormattedGaji"
                                   @input="formatEditGajiInput($event)"
                                   required
                                   class="w-full pl-9 pr-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm font-bold text-emerald-800">
                            <input type="hidden" name="gaji_bulanan" :value="editRawGaji">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="font-bold text-xs uppercase text-blue-700 border-b border-blue-100 pb-1">2. Data Wilayah Regional</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi:</label>
                        <input type="text" name="provinsi" x-model="editData.provinsi" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten:</label>
                        <input type="text" name="kota_kabupaten" x-model="editData.kota_kabupaten" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan:</label>
                        <input type="text" name="kecamatan" x-model="editData.kecamatan" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelurahan / Desa:</label>
                        <input type="text" name="kelurahan_desa" x-model="editData.kelurahan_desa" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">RT / RW:</label>
                        <input type="text" name="rt_rw" x-model="editData.rt_rw" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Pernikahan:</label>
                        <select name="status_pernikahan" x-model="editData.status_pernikahan" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                            <option value="Belum Menikah">Belum Menikah</option>
                            <option value="Menikah">Menikah</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Jalan & Nomor Rumah:</label>
                        <input type="text" name="alamat_lengkap" x-model="editData.alamat_lengkap" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email:</label>
                        <input type="email" name="email" x-model="editData.email" class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-200">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-200 text-xs font-bold text-slate-700">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- MODAL 1: Modal Lihat Detail -->
    <div x-show="showPreviewModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        
        <div class="bg-white max-w-xl w-full rounded-2xl p-6 border border-slate-200 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="font-bold text-slate-900 text-lg">
                    👤 Detail Data Perorangan (#<span x-text="previewData.id"></span>)
                </h3>
                <div class="flex items-center gap-2">
                    <button @click="modalIsMasked = !modalIsMasked" 
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all border flex items-center gap-1 shadow-sm"
                            :class="modalIsMasked ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-emerald-100 text-emerald-900 border-emerald-300'">
                        <span x-show="modalIsMasked">🔓 Buka Sensor</span>
                        <span x-show="!modalIsMasked">🔒 Sensor</span>
                    </button>
                    <button @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold px-2">✕</button>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <span class="text-xs font-bold text-slate-500 uppercase">NIK (Nomor Induk Kependudukan):</span>
                    <p class="font-mono text-base font-bold text-blue-700" x-text="modalIsMasked ? previewData.masked_nik : previewData.nik"></p>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase">Nama Lengkap:</span>
                        <p class="text-base font-bold text-slate-900" x-text="modalIsMasked ? previewData.masked_nama : previewData.nama_lengkap"></p>
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold shadow-sm"
                              :class="previewData.status_kehidupan === 'Meninggal Dunia' ? 'bg-slate-800 text-white' : 'bg-emerald-100 text-emerald-800'">
                            <span x-text="previewData.status_kehidupan === 'Meninggal Dunia' ? '⚫ Meninggal Dunia' : '🟢 Masih Hidup'"></span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Jenis Kelamin</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="previewData.jenis_kelamin"></p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Pendidikan</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="previewData.pendidikan_terakhir"></p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Tgl Lahir & Usia Detail</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="(modalIsMasked ? previewData.masked_tanggal_lahir : previewData.formatted_tanggal_lahir)"></p>
                        <p class="text-xs font-bold text-blue-700 mt-0.5" x-text="'⏳ Usia: ' + previewData.usia_detail + ' (' + previewData.usia + ' Tahun)'"></p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase">Gaji Bulanan</span>
                        <p class="font-bold text-emerald-700 mt-0.5" x-text="modalIsMasked ? previewData.masked_gaji : previewData.gaji_bulanan"></p>
                    </div>
                </div>

                <div class="p-3.5 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                    <span class="text-xs font-bold text-blue-900 uppercase block border-b border-blue-200 pb-1">📍 Rincian Wilayah Alamat</span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-500 block">Provinsi:</span>
                            <span class="font-bold text-slate-800" x-text="previewData.provinsi"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Kota / Kabupaten:</span>
                            <span class="font-bold text-slate-800" x-text="previewData.kota_kabupaten"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Kecamatan:</span>
                            <span class="font-bold text-slate-800" x-text="previewData.kecamatan"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Kelurahan / Desa:</span>
                            <span class="font-bold text-slate-800" x-text="previewData.kelurahan_desa"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">RT / RW:</span>
                            <span class="font-bold text-slate-800" x-text="modalIsMasked ? previewData.masked_rt_rw : previewData.rt_rw"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Status Pernikahan:</span>
                            <span class="font-bold text-slate-800" x-text="previewData.status_pernikahan"></span>
                        </div>
                    </div>
                    <div class="pt-1 text-xs border-t border-blue-200/60">
                        <span class="text-slate-500 block">Alamat Jalan & No. Rumah:</span>
                        <span class="font-bold text-slate-900" x-text="modalIsMasked ? previewData.masked_alamat : previewData.alamat_lengkap"></span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                    <span class="text-xs font-bold text-slate-500 uppercase">Waktu Input:</span>
                    <p class="text-xs text-slate-800 font-bold" x-text="'📅 Tanggal Ditambahkan: ' + previewData.waktu_dibuat"></p>
                    <p class="text-xs text-slate-600" x-text="'Email: ' + (modalIsMasked ? previewData.masked_email : previewData.email)"></p>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button @click="showPreviewModal = false" class="px-5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Modal Unduh File CSV dengan Form Pilihan Kolom Kustom -->
    <div x-show="showExportModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        
        <form action="{{ route('analytics.export') }}" method="GET" 
              class="bg-white max-w-xl w-full rounded-2xl p-6 border border-slate-200 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
            
            <!-- Pass Current Active Search & Filters to CSV Export -->
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="jenis_kelamin" value="{{ $jenisKelamin }}">
            <input type="hidden" name="pendidikan" value="{{ $pendidikan }}">
            <input type="hidden" name="rentang_usia" value="{{ $rentangUsia }}">
            <input type="hidden" name="status_kehidupan" value="{{ $statusKehidupan }}">
            <input type="hidden" name="tgl_jenis" value="{{ $tglJenis }}">
            <input type="hidden" name="tgl_awal" value="{{ $tglAwal }}">
            <input type="hidden" name="tgl_akhir" value="{{ $tglAkhir }}">
            <input type="hidden" name="urutkan" value="{{ $urutkan }}">

            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                        📥 Pengaturan & Pilihan Kolom Ekspor CSV
                    </h3>
                    <p class="text-xs text-slate-500">Tentukan privasi sensor dan centang kolom data yang ingin di-ekspor</p>
                </div>
                <button type="button" @click="showExportModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-xl px-2">✕</button>
            </div>

            <!-- Langkah 1: Pilih Mode Sensor / Privasi -->
            <div class="space-y-2">
                <h4 class="font-bold text-xs uppercase text-blue-700 border-b border-blue-100 pb-1">1. Mode Sensor Privasi</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5"
                           :class="exportMode === 'masked' ? 'bg-emerald-50 border-emerald-500' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="mode" value="masked" x-model="exportMode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">🔒 Disensor (Masked)</span>
                            <span class="text-[11px] text-slate-600 block">Sembunyikan NIK, Nama & Alamat demi privasi (UU PDP).</span>
                        </div>
                    </label>

                    <label class="p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5"
                           :class="exportMode === 'unmasked' ? 'bg-amber-50 border-amber-500' : 'bg-slate-50 border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="mode" value="unmasked" x-model="exportMode" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="font-bold text-slate-900 text-xs block">🔓 Berkas Asli (Unmasked)</span>
                            <span class="text-[11px] text-slate-600 block">Ekspor data mentah utuh tanpa sensor.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Langkah 2: Form Checkbox Pilihan Kolom Data -->
            <div class="space-y-3 pt-1">
                <div class="flex items-center justify-between border-b border-blue-100 pb-1">
                    <h4 class="font-bold text-xs uppercase text-blue-700">2. Pilih Kolom Data Yang Ingin Di-Ekspor</h4>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectAllCols()" class="text-[11px] font-bold text-blue-700 hover:underline">
                            ☑️ Pilih Semua
                        </button>
                        <span class="text-slate-300 text-xs">•</span>
                        <button type="button" @click="deselectAllCols()" class="text-[11px] font-bold text-rose-700 hover:underline">
                            ☐ Hapus Semua
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-1 border border-slate-200 rounded-xl bg-slate-50 text-xs">
                    @foreach(\App\Services\CsvExportService::availableColumns() as $colKey => $colLabel)
                        <label class="flex items-center gap-2 p-2 rounded bg-white border border-slate-200 hover:bg-blue-50/50 cursor-pointer select-none">
                            <input type="checkbox" 
                                   name="columns[]" 
                                   value="{{ $colKey }}" 
                                   x-model="selectedColumns" 
                                   class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="font-medium text-slate-800 truncate" title="{{ $colLabel }}">{{ $colLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                <span class="text-xs text-slate-500 font-semibold" x-text="selectedColumns.length + ' dari {{ count(\App\Services\CsvExportService::availableColumns()) }} kolom dipilih'"></span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showExportModal = false" class="px-4 py-2 rounded-xl bg-slate-200 text-xs font-bold text-slate-700">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="selectedColumns.length === 0"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                        📥 Unduh Berkas CSV Sesuai Pilihan
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    function analyticsApp() {
        return {
            showAddModal: {{ (isset($errors) && $errors && method_exists($errors, 'any') && $errors->any()) ? 'true' : 'false' }},
            showEditModal: false,
            showExportModal: false,
            showPreviewModal: false,
            modalIsMasked: true,
            exportMode: 'masked',
            selectedColumns: [
                'nik', 'nama_lengkap', 'status_kehidupan', 'jenis_kelamin', 
                'pendidikan_terakhir', 'tanggal_lahir', 'usia_detail', 'gaji_bulanan', 
                'provinsi', 'kota_kabupaten', 'kecamatan', 'kelurahan_desa', 
                'rt_rw', 'alamat_lengkap', 'email', 'status_pernikahan', 'created_at'
            ],
            previewData: {},
            editData: {},
            formattedGaji: '5.000.000',
            rawGaji: 5000000,
            editFormattedGaji: '',
            editRawGaji: 0,
            
            selectAllCols() {
                this.selectedColumns = [
                    'id', 'nik', 'nama_lengkap', 'status_kehidupan', 'jenis_kelamin', 
                    'pendidikan_terakhir', 'tanggal_lahir', 'usia', 'usia_detail', 'gaji_bulanan', 
                    'provinsi', 'kota_kabupaten', 'kecamatan', 'kelurahan_desa', 
                    'rt_rw', 'alamat_lengkap', 'email', 'status_pernikahan', 'created_at'
                ];
            },

            deselectAllCols() {
                this.selectedColumns = [];
            },

            formatGajiInput(e) {
                let val = e.target.value.replace(/[^0-9]/g, '');
                if (val === '') {
                    this.rawGaji = 0;
                    this.formattedGaji = '';
                    return;
                }
                this.rawGaji = parseInt(val, 10);
                this.formattedGaji = new Intl.NumberFormat('id-ID').format(this.rawGaji);
            },

            formatEditGajiInput(e) {
                let val = e.target.value.replace(/[^0-9]/g, '');
                if (val === '') {
                    this.editRawGaji = 0;
                    this.editFormattedGaji = '';
                    return;
                }
                this.editRawGaji = parseInt(val, 10);
                this.editFormattedGaji = new Intl.NumberFormat('id-ID').format(this.editRawGaji);
            },

            async openPreview(id) {
                try {
                    const res = await fetch(`/preview/${id}`);
                    const json = await res.json();
                    if (json.success) {
                        this.previewData = json.data;
                        this.modalIsMasked = this.isMasked;
                        this.showPreviewModal = true;
                    }
                } catch (e) {
                    alert('Gagal memuat detail data.');
                }
            },

            async openEditModal(id) {
                try {
                    const res = await fetch(`/preview/${id}`);
                    const json = await res.json();
                    if (json.success) {
                        this.editData = json.data;
                        this.editRawGaji = json.data.raw_gaji;
                        this.editFormattedGaji = new Intl.NumberFormat('id-ID').format(json.data.raw_gaji);
                        this.showEditModal = true;
                    }
                } catch (e) {
                    alert('Gagal memuat data untuk di-edit.');
                }
            }
        }
    }
</script>
@endpush
@endsection
