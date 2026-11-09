/**
 * PADU v2.0 Enterprise - Master Data Table Controller
 * Lokasi: public/js/data-page.js
 */

function dtsenDataPageApp(allColKeys = []) {
    let savedCols = null;
    try {
        savedCols = JSON.parse(localStorage.getItem('padu_visible_columns'));
    } catch (e) {}

    const validSavedCols = (savedCols && Array.isArray(savedCols)) 
        ? savedCols.filter(c => allColKeys.includes(c)) 
        : [];

    return {
        showColumnModal: false,
        visibleCols: validSavedCols.length > 0 ? validSavedCols : [...allColKeys],

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
            
            // Synonyms
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
