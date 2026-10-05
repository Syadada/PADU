@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="backupPage()">

    <!-- Header Section (Glassmorphism) -->
    <div class="glass-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-blue-500/15 text-blue-800 border border-blue-400/30">
                    Per BSSN No. 11/2024
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-emerald-500/15 text-emerald-800 border border-emerald-400/30">
                    🔒 Enkripsi ZIP AES-256 & SHA-256
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                Pencadangan Sistem & Kunci Bencana Server (DRP)
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Pengelolaan arsip cadangan SQLite & DuckDB berenkripsi AES-256 militer, serta Kunci Pemulihan Bencana Hardware Server (DRP Re-Binding).
            </p>
        </div>

        <!-- Tombol Buat Backup Instan -->
        <form action="{{ route('admin.backup.create') }}" method="POST" onsubmit="return confirm('⚠️ Buat arsip cadangan sistem terenkripsi AES-256 sekarang?');">
            @csrf
            <button type="submit" class="btn-primary-blue px-5 py-3 rounded-xl text-xs font-bold shadow-md flex items-center gap-2 cursor-pointer transition-all hover:scale-105 active:scale-95">
                <span class="text-base">💾</span>
                <span>Buat Cadangan Baru (AES-256)</span>
            </button>
        </form>
    </div>

    <!-- Alert Notifikasi Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-xl border border-emerald-300 text-emerald-900 text-xs font-semibold flex items-center gap-2 shadow-sm"
             style="background: rgba(209, 250, 229, 0.9);">
            <span class="text-base">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl border border-rose-300 text-rose-900 text-xs font-semibold flex items-center gap-2 shadow-sm"
             style="background: rgba(254, 226, 226, 0.9);">
            <span class="text-base">✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- ================= 1. KUNCI PEMULIHAN BENCANA SERVER (DISASTER RECOVERY KEY - DRP) ================= -->
    <div class="recovery-vault-box space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-amber-300/80 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="step-badge bg-rose-600">DRP</span>
                <div>
                    <h2 class="font-black text-sm text-slate-900 flex items-center gap-2">
                        <span>Master Disaster Recovery Key (Pemulihan Bencana Hardware Server)</span>
                    </h2>
                    <p class="text-[11px] text-amber-900 font-medium">SOP Kontingensi Bab 3.3 Dokumen Proposal Teknis (Anti-Oper Folder & Re-Binding)</p>
                </div>
            </div>
            <button type="button" 
                    @click="printDisasterSheet()" 
                    class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-900 rounded-xl text-xs font-bold border border-amber-300 shadow-sm transition-all flex items-center gap-1.5 cursor-pointer shrink-0 hover:scale-105 active:scale-95">
                <span>🖨️</span>
                <span>Cetak Lembar Brankas DRP</span>
            </button>
        </div>

        <div class="p-3 rounded-xl bg-amber-50/90 border border-amber-200 text-xs leading-relaxed text-amber-950 font-medium space-y-1">
            <p class="font-extrabold text-amber-900 flex items-center gap-1.5">
                <span>⚠️</span> <span>PERBEDAAN FUNGSIONAL KRUSIAL:</span>
            </p>
            <p class="text-[11px] leading-relaxed">
                Kunci ini <strong>BUKAN untuk verifikasi login 2FA HP</strong>. Kunci ini digunakan secara eksklusif apabila <strong>komputer server kantor rusak fisik / motherboard terbakar</strong> sehingga seluruh folder aplikasi harus dipindahkan ke PC server pengganti yang baru via skrip konsol <code>restore_hardware_bind.bat</code>.
            </p>
        </div>

        <div class="space-y-2">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 block">KODE PEMULIHAN BENCANA HARDWARE (TIDAK DAPAT DISALIN):</span>
            
            <!-- Tampilan Anti-Copy: Sama sekali tidak bisa dicopy, diselect, atau disalin ke clipboard -->
            <div class="recovery-key-display select-none anti-copy-protection font-black tracking-widest text-center"
                 oncopy="return false;"
                 oncut="return false;"
                 oncontextmenu="return false;"
                 draggable="false"
                 x-text="disasterKey"></div>
        </div>

        <!-- Banner Anti-Copy Sesuai Instruksi User & Mitigasi BSSN -->
        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-rose-900 leading-relaxed font-semibold flex items-start gap-2 shadow-xs">
            <span class="text-sm shrink-0">🔒</span>
            <span><strong>Proteksi Anti-Copy Aktif:</strong> Demi standar mitigasi siber BSSN terhadap malware <em>clipboard sniffer</em>, Kunci Pemulihan Bencana ini <strong class="text-rose-950 font-black">TIDAK DAPAT DISALIN</strong> ke clipboard komputer. Kunci wajib dicatat secara manual dengan pulpen atau dicetak ke lembar fisik amplop brankas.</span>
        </div>
    </div>

    <!-- Informasi Standar Kepatuhan SOP 3-2-1 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <div class="glass-card-dark p-5 space-y-2">
            <div class="flex items-center gap-2 font-bold text-amber-400">
                <span>🔐</span>
                <span>Enkripsi AES-256 Otomatis</span>
            </div>
            <p class="text-slate-300 leading-relaxed text-[11px]">
                Setiap arsip ZIP dikunci dengan kata sandi 20 karakter acak berkekuatan tinggi. Begitu arsip dibuka/diekstrak, sistem otomatis me-reset kata sandi baru.
            </p>
        </div>
        <div class="glass-card-dark p-5 space-y-2">
            <div class="flex items-center gap-2 font-bold text-sky-400">
                <span>🛡️</span>
                <span>Integritas Hash SHA-256</span>
            </div>
            <p class="text-slate-300 leading-relaxed text-[11px]">
                Menjamin keaslian berkas cadangan dan mencegah penyisipan kode berbahaya (*tampering*) saat pemulihan bencana (*Disaster Recovery*).
            </p>
        </div>
        <div class="glass-card-dark p-5 space-y-2">
            <div class="flex items-center gap-2 font-bold text-emerald-400">
                <span>🏛️</span>
                <span>Prinsip Kedaulatan Mandiri</span>
            </div>
            <p class="text-slate-300 leading-relaxed text-[11px]">
                Cadangan tersimpan secara lokal offline di PC Server kantor. Dapat disalin ke media eksternal (Flashdisk / HDD Eksternal) sesuai SOP brankas fisik.
            </p>
        </div>
    </div>

    <!-- Tabel Daftar Cadangan yang Tersedia -->
    <div class="glass-card overflow-hidden">
        <div class="p-4 border-b border-slate-200/80 flex items-center justify-between">
            <h2 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                <span>📦</span>
                <span>Daftar Berkas Cadangan Tersimpan (Offline Local Storage)</span>
            </h2>
            <span class="text-xs text-slate-500 font-bold bg-slate-100 px-2.5 py-1 rounded-lg">Total: {{ count($backups) }} berkas</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/80 text-slate-700 font-extrabold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Nama Berkas Arsip (.ZIP)</th>
                        <th class="py-3 px-4">Waktu Pembuatan</th>
                        <th class="py-3 px-4">Ukuran</th>
                        <th class="py-3 px-4">Nilai Hash SHA-256</th>
                        <th class="py-3 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($backups as $b)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-amber-500">🗜️</span>
                                <span>{{ $b['filename'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 whitespace-nowrap">
                                {{ $b['created_at'] }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-700">
                                {{ $b['size'] }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 max-w-xs truncate" title="{{ $b['sha256'] }}">
                                {{ $b['sha256'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.backup.download', $b['filename']) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold border border-blue-200 transition-colors inline-flex items-center gap-1 cursor-pointer">
                                    <span>⬇️</span>
                                    <span>Unduh Arsip</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 font-medium">
                                Belum ada berkas cadangan sistem yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PENAMPIL KATA SANDI ENKRIPSI AES-256 SEKALI PAKAI -->
    <div x-show="showPassModal" x-cloak class="modal-overlay" @click.self="showPassModal = false">
        <div class="modal-content-glass space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                <div class="flex items-center gap-2 text-emerald-600 font-bold text-base">
                    <span class="text-xl">✅</span>
                    <span>Arsip Cadangan AES-256 Selesai Dibuat</span>
                </div>
                <button type="button" @click="showPassModal = false" class="text-slate-400 hover:text-slate-700 font-bold cursor-pointer">✕</button>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed space-y-1">
                <p class="font-bold text-amber-950">⚠️ PENTING: CATAT ATAU SIMPAN KATA SANDI INI SEKARANG!</p>
                <p>
                    Kata sandi enkripsi berkas ZIP ini digenerate secara acak 20 karakter berkekuatan tinggi dan <strong>hanya ditampilkan sekali ini saja</strong>.
                </p>
            </div>

            <!-- Detail Berkas & Password Box -->
            <div class="space-y-3">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">NAMA BERKAS ARSIP:</span>
                    <div class="p-2.5 rounded-xl font-mono text-xs text-slate-800 bg-slate-100 border border-slate-200 break-all" x-text="backupFilename"></div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">KATA SANDI ENKRIPSI AES-256 (20 KARAKTER):</span>
                    <div class="p-3 rounded-xl font-mono text-base font-extrabold text-amber-700 bg-amber-50 border border-amber-300 tracking-wider text-center select-all" x-text="backupPassword"></div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-500 block mb-1">HASH INTEGRITAS SHA-256:</span>
                    <div class="p-2.5 rounded-xl font-mono text-[10px] text-slate-600 bg-slate-100 border border-slate-200 break-all select-all" x-text="backupSha256"></div>
                </div>
            </div>

            <div class="pt-3 text-right border-t border-slate-200/80">
                <button type="button" @click="showPassModal = false" class="btn-primary-blue px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                    Saya Sudah Menyimpan Sandi Ini ✓
                </button>
            </div>
        </div>
    </div>

    <!-- ================= DOKUMEN CETAK AMPLOP BRANKAS BENCANA DRP (PRINTABLE ONLY) ================= -->
    <div id="printable-recovery-sheet" style="display: none;">
        <div style="border: 3px double #000; padding: 25px; font-family: 'Times New Roman', serif;">
            <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px;">
                <h2 style="font-size: 16pt; margin: 0; text-transform: uppercase; font-weight: bold;">LEMBAR KUNCI PEMULIHAN BENCANA SERVER (DISASTER RECOVERY KEY - DRP)</h2>
                <h3 style="font-size: 11pt; margin: 4px 0 0; font-weight: normal;">Sistem Informasi PADU Enterprise v2.0 &bull; Bab 3.3 Dokumen Proposal Teknis (Anti-Oper Folder & Re-Binding)</h3>
                <p style="font-size: 9pt; margin: 2px 0 0; font-style: italic; color: #555;">DOKUMEN SANGAT RAHASIA &bull; BERBEDA TOTAL DARI KUNCI 2FA HP &bull; WAJIB DISIMPAN DALAM AMPLOP TERSEGEL DI BRANKAS PIMPINAN</p>
            </div>

            <table style="width: 100%; font-size: 11pt; margin-bottom: 20px;">
                <tr>
                    <td style="width: 35%; padding: 4px 0; font-weight: bold;">Pemegang Otoritas DRP:</td>
                    <td style="padding: 4px 0;">Super Administrator (Pimpinan Satker)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold;">Peruntukan Kunci:</td>
                    <td style="padding: 4px 0;">Pemulihan Kerusakan Hardware Server PC Kantor (Motherboard Terbakar / Penggantian Server)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold;">Skrip Konsol Eksekusi:</td>
                    <td style="padding: 4px 0;"><code>restore_hardware_bind.bat</code></td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold;">Tanggal Diterbitkan:</td>
                    <td style="padding: 4px 0;">{{ now()->translatedFormat('d F Y, H:i:s') }} WIB</td>
                </tr>
            </table>

            <div style="background: #f4f4f4; border: 2px dashed #000; padding: 15px; text-align: center; margin: 20px 0;">
                <div style="font-size: 10pt; font-weight: bold; margin-bottom: 6px; text-transform: uppercase;">KUNCI PEMULIHAN BENCANA SERVER FISIK SEKALI PAKAI:</div>
                <div style="font-size: 18pt; font-family: monospace; font-weight: bold; letter-spacing: 2px;">{{ $disasterKey ?? 'PADU-DR-RECOVERY-KEY-2026' }}</div>
            </div>

            <div style="font-size: 10pt; line-height: 1.6; margin-top: 15px;">
                <p><strong>PETUNJUK OPERASIONAL STANDAR PEMULIHAN BENCANA (SOP DRP BSSN):</strong></p>
                <ol style="margin-left: 20px; padding-left: 0;">
                    <li>Kunci ini digunakan secara eksklusif apabila komputer PC Server kantor mengalami kerusakan fisik total (misal motherboard terbakar).</li>
                    <li>Pindahkan folder cadangan aplikasi PADU ke komputer PC Server baru.</li>
                    <li>Jalankan skrip <code>restore_hardware_bind.bat</code> dan masukkan kunci di atas saat diminta otorisasi master.</li>
                    <li>Sistem akan membaca UUID motherboard baru dan me-rebind hak akses sistem secara otomatis tanpa campur tangan tim developer.</li>
                </ol>
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 45%;">
                    <p style="font-size: 10pt; margin-bottom: 50px;">Petugas Administrator,</p>
                    <p style="font-size: 10pt; font-weight: bold; border-top: 1px solid #000; padding-top: 4px;">({{ auth()->user()->name ?? 'Super Administrator' }})</p>
                </div>
                <div style="text-align: center; width: 45%;">
                    <p style="font-size: 10pt; margin-bottom: 50px;">Saksi Pimpinan Satker / Auditor,</p>
                    <p style="font-size: 10pt; font-weight: bold; border-top: 1px solid #000; padding-top: 4px;">(....................................................)</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Clean JavaScript Component -->
<script>
function backupPage() {
    return {
        showPassModal: {{ session('new_backup_password') ? 'true' : 'false' }},
        backupPassword: @json(session('new_backup_password', '')),
        backupFilename: @json(session('new_backup_filename', '')),
        backupSha256: @json(session('new_backup_sha256', '')),
        disasterKey: @json($disasterKey ?? 'PADU-DR-RECOVERY-KEY-2026'),

        printDisasterSheet() {
            window.print();
        }
    };
}
</script>
@endsection
