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

        openPreview(id) {
            if (typeof window.openPreview === 'function') {
                window.openPreview(id);
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
        }
    };
}
