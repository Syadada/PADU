/**
 * PADU v2.5 Enterprise - Unified Core Application & Layout Script
 * Lokasi: public/js/app.js
 * Berisi seluruh state manager Alpine.js: paduLayoutApp, dtsenDataPageApp, dtsenApp,
 * serta preview handler, bulk search, column filters, dan security interceptors.
 */

// Helper CSRF Token dari Meta Tag
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// =========================================================================
// 1. GLOBAL LAYOUT APPLICATION STATE (paduLayoutApp)
// =========================================================================
function paduLayoutApp() {
    try {
        localStorage.removeItem('padu_show_translated_labels');
        localStorage.removeItem('padu_show_translated_labels_v2');
    } catch (e) {}

    let initialShowTranslated = true;
    try {
        const stored = localStorage.getItem('padu_show_translated_labels_v3');
        if (stored !== null) {
            initialShowTranslated = (stored === 'true');
        }
    } catch (e) {}

    return {
        sidebarOpen: window.innerWidth >= 768 
            ? (localStorage.getItem('padu_sidebar_open') !== 'false') 
            : false,
        isMobile: window.innerWidth < 768,
        isMasked: localStorage.getItem('padu_is_masked') !== 'false',
        showTranslatedLabels: initialShowTranslated,
        
        toggleTranslatorMode() {
            this.showTranslatedLabels = !this.showTranslatedLabels;
            try {
                localStorage.setItem('padu_show_translated_labels_v3', this.showTranslatedLabels ? 'true' : 'false');
            } catch (e) {}
            window.dispatchEvent(new CustomEvent('padu-translator-changed', { detail: this.showTranslatedLabels }));
        },

        showExportModal: false,
        showClearModal: false,
        showPreviewModal: false,
        showLogModal: false,
        showImportProgressModal: false,
        previewData: null,
        
        // Console Logs State
        logList: [],
        logFilter: 'ALL',
        logPollingInterval: null,

        // Import Progress State
        importProgressPercent: 0,
        importProgressMessage: 'Menyiapkan pemrosesan data...',
        importEtaSeconds: 0,
        progressPollTimer: null,
        isProcessing: false,

        get filteredLogs() {
            if (this.logFilter === 'ALL') return this.logList;
            return this.logList.filter(l => l.level === this.logFilter);
        },

        init() {
            window.paduApp = this;
            window.openPreview = (id) => this.openPreview(id);
            window.closeLogConsole = () => this.closeLogConsole();

            window.addEventListener('open-preview', (e) => {
                const targetId = (e && e.detail && (e.detail.id || e.detail)) ? (e.detail.id || e.detail) : e;
                this.openPreview(targetId);
            });

            window.addEventListener('resize', () => {
                const wasMobile = this.isMobile;
                this.isMobile = window.innerWidth < 768;
                if (wasMobile !== this.isMobile && !this.isMobile) {
                    this.sidebarOpen = localStorage.getItem('padu_sidebar_open') !== 'false';
                }
            });

            window.addEventListener('padu-masking-changed', (e) => {
                this.isMasked = !!e.detail;
            });
            window.addEventListener('padu-translator-changed', (e) => {
                this.showTranslatedLabels = !!e.detail;
            });
            window.addEventListener('open-clear-modal', () => {
                this.showClearModal = true;
            });

            this.fetchLogs();
        },

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            if (!this.isMobile) {
                localStorage.setItem('padu_sidebar_open', this.sidebarOpen ? 'true' : 'false');
            }
        },

        closeSidebar() {
            this.sidebarOpen = false;
            if (!this.isMobile) {
                localStorage.setItem('padu_sidebar_open', 'false');
            }
        },

        openSidebar() {
            this.sidebarOpen = true;
            if (!this.isMobile) {
                localStorage.setItem('padu_sidebar_open', 'true');
            }
        },

        setMasked(val) {
            this.isMasked = val;
            localStorage.setItem('padu_is_masked', val ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent('padu-masking-changed', { detail: val }));
        },

        toggleMasking() {
            this.setMasked(!this.isMasked);
        },

        formatEta(sec) {
            if (!sec || sec <= 0) return '5 detik';
            if (sec >= 60) {
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return m + ' m ' + (s < 10 ? '0' : '') + s + ' s';
            }
            return sec + ' detik';
        },

        openLogConsole() {
            window.location.href = "/dtsen/activity-logs";
        },

        closeLogConsole() {
            this.showLogModal = false;
        },

        async fetchLogs() {
            try {
                const res = await fetch('/dtsen/logs', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.success && Array.isArray(data.logs)) {
                        this.logList = data.logs;
                    }
                }
            } catch (e) {}
        },

        async clearSystemLogs() {
            if (!confirm('Bersihkan seluruh catatan log aktivitas sistem?')) return;
            try {
                const res = await fetch('/dtsen/logs/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    this.fetchLogs();
                }
            } catch (e) {}
        },

        downloadSystemLogsTxt() {
            if (!this.filteredLogs || this.filteredLogs.length === 0) {
                alert('Belum ada log aktivitas untuk diekstrak.');
                return;
            }
            const text = this.filteredLogs.map(l => `[${l.timestamp}] [${l.level}] ${l.message}`).join('\r\n');
            const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            const d = new Date();
            link.href = url;
            link.download = `padu_activity_log_${d.toISOString().slice(0, 10)}.txt`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

        copySystemLogs() {
            if (!this.filteredLogs || this.filteredLogs.length === 0) {
                alert('Belum ada log aktivitas untuk disalin.');
                return;
            }
            const text = this.filteredLogs.map(l => `[${l.timestamp}] [${l.level}] ${l.message}`).join('\n');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Berhasil menyalin log aktivitas ke clipboard!');
                }).catch(() => {
                    this.fallbackCopyText(text);
                });
            } else {
                this.fallbackCopyText(text);
            }
        },

        fallbackCopyText(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Berhasil menyalin log aktivitas ke clipboard!');
            } catch (err) {
                alert('Gagal menyalin log aktivitas.');
            }
            document.body.removeChild(textArea);
        },

        async cancelCurrentImport() {
            if (!confirm('Batalkan proses injeksi dataset ke database?\n\nSemua proses impor yang sedang berjalan akan dihentikan.')) {
                return;
            }
            try {
                const res = await fetch('/dtsen/cancel-import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                this.showImportProgressModal = false;
                this.isProcessing = false;
                alert(data.message || 'Proses injeksi data berhasil dibatalkan.');
                this.fetchLogs();
                setTimeout(() => { window.location.reload(); }, 500);
            } catch (err) {
                alert('Gagal membatalkan impor: ' + err.message);
            }
        },

        async openPreview(id) {
            if (!id && id !== 0) return;
            try {
                const res = await fetch('/dtsen/preview/' + id, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                const data = await res.json();
                if (data && data.success && data.data) {
                    this.previewData = data.data;
                    this.showPreviewModal = true;
                } else {
                    alert(data.message || 'Data tidak ditemukan.');
                }
            } catch (err) {
                console.error('Gagal memuat rincian baris:', err);
                alert('Gagal memuat rincian data: ' + err.message);
            }
        },

        async startLocalImportSubmit(e) {
            if (e) e.preventDefault();
            this.isProcessing = true;
            this.showImportProgressModal = true;
            this.importProgressPercent = 5;
            this.importProgressMessage = 'Membaca data dari berkas...';
            this.importEtaSeconds = 25;

            if (this.progressPollTimer) clearInterval(this.progressPollTimer);

            let startTime = Date.now();
            let totalEstSec = 25;
            let isPollingActive = false;
            let hasServerProgress = false;

            this.progressPollTimer = setInterval(() => {
                let elapsedSec = (Date.now() - startTime) / 1000;

                if (!hasServerProgress) {
                    let ratio = Math.min(elapsedSec / totalEstSec, 0.95);
                    let calcPct = Math.min(95, Math.round(5 + (ratio * 90)));
                    if (calcPct > this.importProgressPercent) {
                        this.importProgressPercent = calcPct;
                    }
                    let calcEta = Math.max(1, Math.round(totalEstSec - elapsedSec));
                    this.importEtaSeconds = calcEta;

                    if (this.importProgressPercent < 25) {
                        this.importProgressMessage = 'Membaca data berkas...';
                    } else if (this.importProgressPercent < 50) {
                        this.importProgressMessage = 'Memvalidasi kualitas data & kolom...';
                    } else if (this.importProgressPercent < 75) {
                        this.importProgressMessage = 'Menyiapkan tabel & struktur dataset...';
                    } else if (this.importProgressPercent < 92) {
                        this.importProgressMessage = 'Memasukkan data ke database analitik...';
                    } else {
                        this.importProgressMessage = 'Membangun indeks pencarian cepat...';
                    }
                }

                if (!isPollingActive) {
                    isPollingActive = true;
                    fetch('/dtsen/import-progress', { headers: { 'Accept': 'application/json' } })
                        .then(res => res.ok ? res.json() : null)
                        .then(data => {
                            if (data && data.percent > 0) {
                                hasServerProgress = true;
                                if (data.percent >= this.importProgressPercent || data.percent === 100) {
                                    this.importProgressPercent = data.percent;
                                }
                                if (data.message) {
                                    this.importProgressMessage = data.message;
                                }
                                if (typeof data.eta_seconds !== 'undefined') {
                                    this.importEtaSeconds = data.eta_seconds;
                                }
                            }
                        })
                        .catch(() => {})
                        .finally(() => { isPollingActive = false; });
                }
            }, 400);

            const form = e.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const resText = await response.text();
                let result;
                try {
                    result = JSON.parse(resText);
                } catch (e) {
                    const cleanErrText = resText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
                    throw new Error('Respon server (Status ' + response.status + '): ' + cleanErrText.substring(0, 200));
                }
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);

                if (result.success) {
                    this.importProgressPercent = 100;
                    this.importProgressMessage = 'Proses Berhasil!';
                    setTimeout(() => {
                        window.location.href = "/dtsen/data";
                    }, 600);
                } else {
                    this.showImportProgressModal = false;
                    this.isProcessing = false;
                    alert('Gagal memproses data: ' + (result.message || 'Terjadi kesalahan sistem.'));
                }
            } catch (err) {
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                this.showImportProgressModal = false;
                this.isProcessing = false;
                alert('Terjadi kesalahan koneksi saat mengunggah berkas: ' + err.message);
            }
        }
    };
}

// =========================================================================
// 2. MASTER DATA PAGE APPLICATION STATE (dtsenDataPageApp)
// =========================================================================
function dtsenDataPageApp(allColKeys = []) {
    if (!allColKeys || allColKeys.length === 0) {
        allColKeys = window.PADU_ALL_COL_KEYS || [];
    }

    let savedCols = null;
    try {
        savedCols = JSON.parse(localStorage.getItem('padu_visible_columns'));
    } catch (e) {}

    const validSavedCols = (savedCols && Array.isArray(savedCols)) 
        ? savedCols.filter(c => allColKeys.includes(c)) 
        : [];

    return {
        showColumnModal: false,
        showRawExtraCols: false,
        visibleCols: validSavedCols.length > 0 ? validSavedCols : [...allColKeys],
        showTranslatedLabels: true,

        toggleTranslatorMode() {
            this.showTranslatedLabels = !this.showTranslatedLabels;
            window.dispatchEvent(new CustomEvent('padu-translator-changed', { detail: this.showTranslatedLabels }));
        },

        openClearModal() {
            this.showClearModal = true;
            window.dispatchEvent(new CustomEvent('open-clear-modal'));
        },

        init() {
            window.addEventListener('padu-translator-changed', (e) => {
                this.showTranslatedLabels = !!e.detail;
            });
        },

        // State Bulk Search by NIK / KK Modal
        showBulkSearchModal: false,
        bulkSearchField: 'nik',
        bulkSearchInput: '',
        get bulkSearchCount() {
            if (!this.bulkSearchInput) return 0;
            const items = this.bulkSearchInput.split(/[\r\n,;\s]+/).map(s => s.trim().replace(/[^0-9A-Za-z]/g, '')).filter(Boolean);
            return (new Set(items)).size;
        },
        clearBulkSearch() {
            const url = new URL(window.location.href);
            url.searchParams.delete('bulk_search');
            url.searchParams.delete('bulk_field');
            url.searchParams.set('page', '1');
            url.hash = 'tabel-data';
            window.location.href = url.toString();
        },

        // State Filter Kolom Pivot Modal (Terpusat, Anti-Offscreen, & Pinned Footer)
        showFilterModal: false,
        activeFilterCol: null,
        activeFilterTitle: '',
        activeFilterType: 'general',
        filterSearchVal: '',
        filterMinVal: '',
        filterMaxVal: '',
        filterSelectedVals: [],
        filterDistinctValues: [],

        openPreview(id) {
            if (typeof window.openPreview === 'function') {
                window.openPreview(id);
            } else if (window.paduApp && typeof window.paduApp.openPreview === 'function') {
                window.paduApp.openPreview(id);
            } else {
                window.dispatchEvent(new CustomEvent('open-preview', { detail: { id } }));
            }
        },

        isColVisible(colKey) {
            if (!this.visibleCols || !Array.isArray(this.visibleCols) || this.visibleCols.length === 0) {
                return true;
            }
            return this.visibleCols.includes(colKey);
        },

        toggleCol(colKey) {
            if (this.visibleCols.includes(colKey)) {
                if (this.visibleCols.length > 1) {
                    this.visibleCols = this.visibleCols.filter(c => c !== colKey);
                }
            } else {
                this.visibleCols.push(colKey);
            }
            localStorage.setItem('padu_visible_columns', JSON.stringify(this.visibleCols));
        },

        resetCols() {
            this.visibleCols = [...allColKeys];
            localStorage.setItem('padu_visible_columns', JSON.stringify(this.visibleCols));
        },

        deselectAllExtraCols(extraKeys = []) {
            if (!extraKeys || extraKeys.length === 0) {
                this.visibleCols = this.visibleCols.filter(k => !k.match(/^r[0-9]/i) && !k.startsWith('col_'));
            } else {
                this.visibleCols = this.visibleCols.filter(k => !extraKeys.includes(k));
            }
            localStorage.setItem('padu_visible_columns', JSON.stringify(this.visibleCols));
        },

        // --- Controller Filter Kolom Modal (Terpusat & Anti-Offscreen) ---
        openColumnFilter(colKey, colTitle, filterType = 'general', distinctVals = [], currentVals = []) {
            this.activeFilterCol = colKey;
            this.activeFilterTitle = colTitle;
            this.activeFilterType = filterType;
            this.filterDistinctValues = Array.isArray(distinctVals) ? distinctVals : [];
            this.filterSelectedVals = Array.isArray(currentVals) ? [...currentVals] : [];
            this.filterSearchVal = (this.filterSelectedVals.length === 1) ? this.filterSelectedVals[0] : '';
            this.filterMinVal = '';
            this.filterMaxVal = '';
            this.showFilterModal = true;
        },

        closeColumnFilter() {
            this.showFilterModal = false;
            this.activeFilterCol = null;
        },

        hasFilterVal(val) {
            if (val === null || val === undefined) return false;
            const target = String(val).trim().toLowerCase();
            return this.filterSelectedVals.some(v => String(v).trim().toLowerCase() === target);
        },

        toggleFilterVal(val) {
            if (this.hasFilterVal(val)) {
                const target = String(val).trim().toLowerCase();
                this.filterSelectedVals = this.filterSelectedVals.filter(v => String(v).trim().toLowerCase() !== target);
            } else {
                this.filterSelectedVals.push(String(val).trim());
            }
        },

        selectAllFilterVals() {
            if (this.filterSelectedVals.length >= this.filterDistinctValues.length) {
                this.filterSelectedVals = [];
            } else {
                this.filterSelectedVals = this.filterDistinctValues.map(v => String(v).trim());
            }
        },

        setSingleFilterVal(val) {
            this.filterSelectedVals = [String(val).trim()];
            this.applyActiveFilter();
        },

        applyFilterRange() {
            let min = parseInt(this.filterMinVal);
            let max = parseInt(this.filterMaxVal);
            let rangeStr = '';
            if (!isNaN(min) && !isNaN(max)) {
                rangeStr = min + ' - ' + max;
            } else if (!isNaN(min)) {
                rangeStr = '> ' + (min - 1);
            } else if (!isNaN(max)) {
                rangeStr = '< ' + (max + 1);
            }
            if (rangeStr) {
                this.filterSelectedVals = [rangeStr];
                this.applyActiveFilter();
            }
        },

        applyActiveFilter() {
            if (!this.activeFilterCol) return;
            const textQuery = this.filterSearchVal.trim();
            if (textQuery !== '') {
                if (this.filterSelectedVals.length === 0) {
                    this.filterSelectedVals = [textQuery];
                } else if (!this.filterSelectedVals.some(v => String(v).toLowerCase() === textQuery.toLowerCase())) {
                    this.filterSelectedVals.push(textQuery);
                }
            }
            const url = new URL(window.location.href);
            if (this.filterSelectedVals.length > 0) {
                url.searchParams.set(this.activeFilterCol, this.filterSelectedVals.join(','));
            } else {
                url.searchParams.delete(this.activeFilterCol);
            }
            url.searchParams.set('page', '1');
            url.hash = 'tabel-data';
            this.showFilterModal = false;
            window.location.href = url.toString();
        },

        clearActiveFilter() {
            if (!this.activeFilterCol) return;
            const url = new URL(window.location.href);
            url.searchParams.delete(this.activeFilterCol);
            
            const synonyms = {
                'jenis_kelamin': ['gender', 'jk', 'sex'],
                'usia': ['umur', 'age'],
                'gaji_bulanan': ['gaji', 'pendapatan', 'income', 'salary', 'penghasilan'],
                'desil_nasional': ['desil', 'desil_kesejahteraan'],
                'status_bekerja': ['pekerjaan', 'status_kerja'],
                'nomor_induk_kependudukan': ['nik', 'no_nik'],
                'nomor_kartu_keluarga': ['kk', 'no_kk'],
                'pendidikan': ['pendidikan_terakhir', 'ijazah_tertinggi_yang_dimiliki'],
                'status_kawin': ['status_pernikahan']
            };
            if (synonyms[this.activeFilterCol]) {
                synonyms[this.activeFilterCol].forEach(s => url.searchParams.delete(s));
            }

            url.searchParams.set('page', '1');
            url.hash = 'tabel-data';
            this.showFilterModal = false;
            window.location.href = url.toString();
        }
    };
}

// =========================================================================
// 3. OVERVIEW / HOME APPLICATION STATE (dtsenApp alias)
// =========================================================================
function dtsenApp(allColKeys = []) {
    return dtsenDataPageApp(allColKeys);
}

// =========================================================================
// 4. SCROLL TO TABLE TOP PRESERVATION
// =========================================================================
function scrollToTableTop() {
    const el = document.getElementById('tabel-data');
    if (el) {
        const rect = el.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const targetPos = scrollTop + rect.top - 75;
        window.scrollTo({ top: targetPos, behavior: 'instant' });
    }
}

document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*="#tabel-data"]');
    if (link && document.getElementById('tabel-data')) {
        const el = document.getElementById('tabel-data');
        const rect = el.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        sessionStorage.setItem('padu_table_top_pos', (scrollTop + rect.top - 75).toString());
    }
});

document.addEventListener('submit', function(e) {
    const form = e.target.closest('form[action*="#tabel-data"]');
    if (form && document.getElementById('tabel-data')) {
        const el = document.getElementById('tabel-data');
        const rect = el.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        sessionStorage.setItem('padu_table_top_pos', (scrollTop + rect.top - 75).toString());
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash.includes('tabel-data')) {
        const savedPos = sessionStorage.getItem('padu_table_top_pos');
        if (savedPos !== null) {
            window.scrollTo({ top: parseInt(savedPos, 10), behavior: 'instant' });
        } else {
            scrollToTableTop();
        }
    }
});

// =========================================================================
// 5. GLOBAL SESSION TIMEOUT & REQUEST INTERCEPTORS
// =========================================================================
(function() {
    const loginUrl = "/login?timeout=1";

    // 1. Intersep Fetch API Global (Tangani HTTP 401 Unauthorized / HTTP 419 Page Expired)
    if (window.fetch) {
        const rawFetch = window.fetch;
        window.fetch = async function(...args) {
            try {
                const response = await rawFetch.apply(this, args);
                if (response.status === 401 || response.status === 419) {
                    window.location.href = loginUrl;
                    return response;
                }
                return response;
            } catch (err) {
                throw err;
            }
        };
    }

    // 2. Intersep XMLHttpRequest Global
    if (window.XMLHttpRequest) {
        const rawOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function() {
            this.addEventListener('load', function() {
                if (this.status === 401 || this.status === 419) {
                    window.location.href = loginUrl;
                }
            });
            return rawOpen.apply(this, arguments);
        };
    }

    // 3. Monitor Inaktivitas Otomatis 15 Menit
    let inactivityTimer;
    const TIMEOUT_MS = 15 * 60 * 1000;

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            window.location.href = loginUrl;
        }, TIMEOUT_MS);
    }

    ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click'].forEach(evt => {
        window.addEventListener(evt, resetInactivityTimer, { passive: true });
    });

    resetInactivityTimer();
})();
