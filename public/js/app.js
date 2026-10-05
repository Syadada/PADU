/**
 * PADU v2.0 Enterprise - Global Layout & Core Application Script
 * Lokasi: public/js/app.js
 */

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function paduLayoutApp() {
    return {
        sidebarOpen: window.innerWidth >= 1024 
            ? (localStorage.getItem('padu_sidebar_open') !== 'false') 
            : false,
        isMasked: localStorage.getItem('padu_is_masked') !== 'false',
        showClearModal: false,
        showExportModal: false,
        showPreviewModal: false,
        previewData: null,
        showImportProgressModal: false,
        importProgressPercent: 0,
        importProgressMessage: 'Menyiapkan proses...',
        importEtaSeconds: 0,
        importProgressInterval: null,
        showLogModal: false,
        logList: [],
        logFilter: 'ALL',
        logPollingInterval: null,

        init() {
            // Sinkronisasi status masking NIK secara global
            window.addEventListener('padu-masking-changed', (e) => {
                this.isMasked = e.detail;
            });

            // Event listener untuk preview baris
            window.addEventListener('padu-open-preview', (e) => {
                this.previewData = e.detail;
                this.showPreviewModal = true;
            });
        },

        openPreview(id) {
            if (typeof window.openPreview === 'function') {
                window.openPreview(id);
            }
        },

        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            if (window.innerWidth >= 1024) {
                localStorage.setItem('padu_sidebar_open', this.sidebarOpen ? 'true' : 'false');
            }
        },

        closeSidebar() {
            this.sidebarOpen = false;
            if (window.innerWidth >= 1024) {
                localStorage.setItem('padu_sidebar_open', 'false');
            }
        },

        setMasked(val) {
            this.isMasked = val;
            localStorage.setItem('padu_is_masked', val ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent('padu-masking-changed', { detail: val }));
        },

        openLogConsole() {
            this.showLogModal = true;
            this.fetchLogs();
            if (!this.logPollingInterval) {
                this.logPollingInterval = setInterval(() => {
                    if (this.showLogModal) {
                        this.fetchLogs();
                    }
                }, 2500);
            }
        },

        fetchLogs() {
            fetch('/dtsen/logs', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success && Array.isArray(data.logs)) {
                    this.logList = data.logs;
                }
            })
            .catch(() => {});
        },

        get filteredLogs() {
            if (this.logFilter === 'ALL') return this.logList;
            return this.logList.filter(l => l.level === this.logFilter);
        },

        downloadSystemLogsTxt() {
            window.location.href = '/dtsen/logs/download';
        },

        clearSystemLogs() {
            if (!confirm('Bersihkan seluruh catatan log aktivitas saat ini?')) return;
            fetch('/dtsen/logs/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res && res.success) {
                    this.logList = [];
                    this.fetchLogs();
                }
            });
        },

        cancelCurrentImport() {
            if (!confirm('Hentikan dan batalkan injeksi dataset yang sedang berjalan sekarang?')) return;
            fetch('/dtsen/cancel-import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(res => {
                alert((res && res.message) ? res.message : 'Perintah pembatalan terkirim.');
                if (this.importProgressInterval) clearInterval(this.importProgressInterval);
                this.showImportProgressModal = false;
                this.fetchLogs();
            });
        },

        formatEta(sec) {
            if (sec < 60) return sec + ' detik';
            const min = Math.floor(sec / 60);
            const rem = sec % 60;
            return min + ' m ' + rem + ' s';
        }
    };
}

// Handler Modal Inspeksi Rincian Baris Data DTSEN (Cross-component Event Dispatcher)
window.openPreview = function(id) {
    if (!id && id !== 0) return;
    fetch('/dtsen/preview/' + id, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Data tidak dapat dimuat (HTTP ' + res.status + ')');
        return res.json();
    })
    .then(data => {
        if (data && data.success && data.data) {
            window.dispatchEvent(new CustomEvent('padu-open-preview', { detail: data.data }));
        } else if (data && data.data) {
            window.dispatchEvent(new CustomEvent('padu-open-preview', { detail: data.data }));
        } else {
            alert('Rincian data tidak ditemukan.');
        }
    })
    .catch(err => {
        console.error('[PADU Preview Error]', err);
        alert('Gagal memuat rincian data: ' + err.message);
    });
};

// Handler Modal Inspeksi Rincian Baris Data DTSEN (Cross-component Event Dispatcher)
window.openPreview = function(id) {
    if (!id && id !== 0) return;
    fetch('/dtsen/preview/' + id, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Data tidak dapat dimuat (HTTP ' + res.status + ')');
        return res.json();
    })
    .then(data => {
        if (data && data.success && data.data) {
            window.dispatchEvent(new CustomEvent('padu-open-preview', { detail: data.data }));
        } else if (data && data.data) {
            window.dispatchEvent(new CustomEvent('padu-open-preview', { detail: data.data }));
        } else {
            alert('Rincian data tidak ditemukan.');
        }
    })
    .catch(err => {
        console.error('[PADU Preview Error]', err);
        alert('Gagal memuat rincian data: ' + err.message);
    });
};

// Skrip Pengunci Posisi Scroll Presisi
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
