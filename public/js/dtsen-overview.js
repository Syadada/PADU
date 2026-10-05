/**
 * PADU v2.0 Enterprise - DTSEN 2026 Overview Dashboard Controller
 * Lokasi: public/js/dtsen-overview.js
 */

function dtsenApp(allColKeys = []) {
    let savedCols = null;
    try {
        savedCols = JSON.parse(localStorage.getItem('padu_visible_columns'));
    } catch(e) {}

    const validSavedCols = (savedCols && Array.isArray(savedCols)) 
        ? savedCols.filter(c => allColKeys.includes(c)) 
        : [];

    return {
        isMasked: localStorage.getItem('padu_is_masked') !== 'false',
        mobileSidebarOpen: false,
        showExportModal: false,
        showClearModal: false,
        showPreviewModal: false,
        showColumnModal: false,
        previewData: null,
        visibleCols: validSavedCols.length > 0 ? validSavedCols : [...allColKeys],

        init() {
            window.addEventListener('padu-masking-changed', (e) => {
                this.isMasked = !!e.detail;
            });
        },

        toggleSidebarMasking() {
            this.isMasked = !this.isMasked;
            localStorage.setItem('padu_is_masked', this.isMasked ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent('padu-masking-changed', { detail: this.isMasked }));
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

        isColVisible(colKey) {
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

        isUploading: false,
        uploadProgress: 0,
        uploadMessage: '',
        localPathInput: 'sample_1_juta_data.csv',

        showImportProgressModal: false,
        importProgressPercent: 0,
        importProgressMessage: 'Menyiapkan pemrosesan berkas data...',
        importEtaSeconds: 0,
        progressPollTimer: null,

        async startLocalImportSubmit(e) {
            if (e) e.preventDefault();
            this.isProcessing = true;
            this.showImportProgressModal = true;
            this.importProgressPercent = 5;
            this.importProgressMessage = 'Membaca data dari berkas CSV...';
            this.importEtaSeconds = 25;

            if (this.progressPollTimer) clearInterval(this.progressPollTimer);

            const startTime = Date.now();
            const totalEstSec = 25;
            let isPollingActive = false;
            let hasServerProgress = false;

            this.progressPollTimer = setInterval(() => {
                const elapsedSec = (Date.now() - startTime) / 1000;

                if (!hasServerProgress) {
                    const ratio = Math.min(elapsedSec / totalEstSec, 0.95);
                    const calcPct = Math.min(95, Math.round(5 + (ratio * 90)));
                    if (calcPct > this.importProgressPercent) {
                        this.importProgressPercent = calcPct;
                    }
                    const calcEta = Math.max(1, Math.round(totalEstSec - elapsedSec));
                    this.importEtaSeconds = calcEta;

                    if (this.importProgressPercent < 25) {
                        this.importProgressMessage = 'Membaca data dari berkas CSV...';
                    } else if (this.importProgressPercent < 50) {
                        this.importProgressMessage = 'Memvalidasi NIK, KK, & kualitas data...';
                    } else if (this.importProgressPercent < 75) {
                        this.importProgressMessage = 'Menyiapkan & mengurutkan data...';
                    } else if (this.importProgressPercent < 92) {
                        this.importProgressMessage = 'Memasukkan data ke dalam database...';
                    } else {
                        this.importProgressMessage = 'Membangun indeks B-Tree pencarian database...';
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
            }, 300);

            try {
                const formData = new FormData(e.target);
                const response = await fetch('/dtsen/import-local', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const resText = await response.text();
                let resData;
                try {
                    resData = JSON.parse(resText);
                } catch (err) {
                    const cleanErrText = resText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
                    throw new Error('Respon server (Status ' + response.status + '): ' + cleanErrText.substring(0, 200));
                }
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);

                if (response.ok && resData.success) {
                    this.importProgressPercent = 100;
                    this.importProgressMessage = resData.message || 'Pemrosesan data selesai!';
                    this.importEtaSeconds = 0;
                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
                } else {
                    alert('Gagal impor: ' + (resData.message || 'Error tidak diketahui'));
                    this.showImportProgressModal = false;
                    this.isProcessing = false;
                }
            } catch (err) {
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                alert('Gagal memproses impor: ' + err.message);
                this.showImportProgressModal = false;
                this.isProcessing = false;
            }
        },

        async handleChunkedUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.isUploading = true;
            this.uploadProgress = 0;
            this.uploadMessage = 'Memulai persiapan upload berkas data (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)...';

            const chunkSize = 5 * 1024 * 1024; // 5MB Chunks
            const totalChunks = Math.ceil(file.size / chunkSize);
            const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            const csrfToken = (typeof getCsrfToken === 'function') ? getCsrfToken() : '';

            try {
                for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                    const start = chunkIndex * chunkSize;
                    const end = Math.min(start + chunkSize, file.size);
                    const chunkBlob = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('file_id', fileId);
                    formData.append('chunk_index', chunkIndex);
                    formData.append('total_chunks', totalChunks);
                    formData.append('file_name', file.name);
                    formData.append('file_size', file.size);
                    formData.append('chunk', chunkBlob, file.name);
                    formData.append('_token', csrfToken);

                    const response = await fetch('/dtsen/import-chunk', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const resText = await response.text();
                    let resData;
                    try {
                        resData = JSON.parse(resText);
                    } catch (err) {
                        const cleanErrText = resText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
                        throw new Error('Respon server (Status ' + response.status + '): ' + cleanErrText.substring(0, 200));
                    }

                    if (!response.ok || !resData.success) {
                        throw new Error(resData.message || 'Gagal mengunggah chunk ' + (chunkIndex + 1));
                    }

                    this.uploadProgress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                    if (resData.is_complete) {
                        this.uploadMessage = resData.message;
                        setTimeout(() => window.location.reload(), 1000);
                        return;
                    } else {
                        this.uploadMessage = 'Mengirim berkas skala besar (' + this.uploadProgress + '% | Chunk ' + (chunkIndex + 1) + '/' + totalChunks + ')...';
                    }
                }
            } catch (err) {
                alert('Gagal mengunggah berkas: ' + err.message);
                this.isUploading = false;
            }
        },

        showLogModal: false,
        logList: [],
        logFilter: 'ALL',
        logPollTimer: null,
        logCount: 0,

        get filteredLogs() {
            if (this.logFilter === 'ALL') return this.logList;
            return this.logList.filter(l => l.level === this.logFilter);
        },

        openLogConsole() {
            this.showLogModal = true;
            this.fetchLogs();
            if (this.logPollTimer) clearInterval(this.logPollTimer);
            this.logPollTimer = setInterval(() => {
                if (this.showLogModal || this.showImportProgressModal) {
                    this.fetchLogs();
                }
            }, 3000);
        },

        async fetchLogs() {
            try {
                const res = await fetch('/dtsen/logs', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.logs) {
                        this.logList = data.logs;
                        this.logCount = data.total || 0;
                    }
                }
            } catch (e) {}
        },

        async clearSystemLogs() {
            if (!confirm('Apakah Anda yakin ingin mengosongkan berkas log aktivitas sistem?')) return;
            try {
                const res = await fetch('/dtsen/logs/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': (typeof getCsrfToken === 'function') ? getCsrfToken() : '',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    this.fetchLogs();
                }
            } catch (e) {}
        },

        copySystemLogs() {
            if (!this.filteredLogs || this.filteredLogs.length === 0) {
                alert('Belum ada log aktivitas untuk disalin.');
                return;
            }
            const text = this.filteredLogs.map(l => `[${l.timestamp}] [${l.level}] ${l.message}`).join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('Berhasil menyalin ' + this.filteredLogs.length + ' log aktivitas ke clipboard!');
            });
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
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            const secs = String(d.getSeconds()).padStart(2, '0');
            
            link.href = url;
            link.download = `dtsen_activity_log_${year}-${month}-${day}_${hours}-${mins}-${secs}.txt`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            alert('Berhasil mengekstrak ' + this.filteredLogs.length + ' log aktivitas ke berkas TXT!');
        },

        async cancelCurrentImport() {
            if (!confirm('⚠️ Batalkan proses injeksi dataset ke database?\n\nSemua proses impor yang sedang berjalan akan dihentikan dan memori temporary akan dibersihkan.')) {
                return;
            }

            try {
                const res = await fetch('/dtsen/cancel-import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': (typeof getCsrfToken === 'function') ? getCsrfToken() : '',
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
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } catch (err) {
                alert('Gagal membatalkan impor: ' + err.message);
            }
        },

        openPreview(id) {
            if (typeof window.openPreview === 'function') {
                window.openPreview(id);
            }
        }
    };
}

let isFilterNavigating = false;

window.applyAjaxFilter = function(targetUrl, targetHash = 'tabel-data', shouldScroll = false) {
    if (isFilterNavigating) return;
    
    if (targetHash && !targetUrl.includes('#')) {
        targetUrl += '#' + targetHash;
    }

    const currentClean = window.location.href;
    if (currentClean === targetUrl) {
        return;
    }

    isFilterNavigating = true;
    window.location.href = targetUrl;
};

let filterDebounceTimer = null;

function submitFilterFormAjax(form, shouldScroll = false) {
    if (!form) return;
    const formData = new FormData(form);
    
    const currentUrl = new URL(window.location.href);
    const urlParams = currentUrl.searchParams;
    
    for (const [key, value] of formData.entries()) {
        if (value === 'semua' || value === '') {
            urlParams.delete(key);
        } else {
            urlParams.set(key, value);
        }
    }
    
    urlParams.set('page', '1');
    const actionUrl = new URL(form.action || window.location.href);
    actionUrl.search = urlParams.toString();
    
    let targetHash = 'tabel-data';
    if (form.id === 'salaryFilterForm') targetHash = 'salary-analytics-section';
    if (form.id === 'kpiVarForm') targetHash = 'kpi-analytics-section';
    
    window.applyAjaxFilter(actionUrl.toString(), targetHash, shouldScroll);
}

document.addEventListener('change', function(e) {
    const filterForm = e.target.closest('#salaryFilterForm, #dataTableSearchForm, #kpiVarForm');
    if (filterForm && (e.target.tagName === 'SELECT' || e.target.type === 'checkbox' || e.target.type === 'radio')) {
        submitFilterFormAjax(filterForm, false);
    }
});

document.addEventListener('input', function(e) {
    const filterForm = e.target.closest('#salaryFilterForm, #dataTableSearchForm');
    if (filterForm && (e.target.tagName === 'INPUT' && (e.target.type === 'text' || e.target.type === 'search'))) {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(() => {
            submitFilterFormAjax(filterForm, false);
        }, 300);
    }
});

document.addEventListener('click', function(e) {
    const link = e.target.closest('#tabel-data a, #salary-analytics-section a, #kpi-analytics-section a, #summary-stats-section a, .pagination a');
    if (link && link.href && !link.href.includes('javascript:') && !link.dataset.noAjax) {
        try {
            const url = new URL(link.href);
            if (url.origin === window.location.origin) {
                e.preventDefault();
                window.applyAjaxFilter(link.href, url.hash.replace('#', '') || 'tabel-data', true);
            }
        } catch (err) {}
    }
});

document.addEventListener('submit', function(e) {
    const form = e.target.closest('#dataTableSearchForm, #salaryFilterForm, #kpiVarForm');
    if (form) {
        e.preventDefault();
        submitFilterFormAjax(form, true);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#salary-analytics-section') {
        const el = document.getElementById('salary-analytics-section');
        if (el) {
            setTimeout(function() {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }
});
