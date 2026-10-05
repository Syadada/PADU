/**
 * PADU v2.0 Enterprise - Analytics Controller
 * Lokasi: public/js/analytics.js
 */

function analyticsApp(config = {}) {
    return {
        isMasked: localStorage.getItem('padu_is_masked') !== 'false',
        init() {
            window.addEventListener('padu-masking-changed', (e) => {
                this.isMasked = !!e.detail;
                this.modalIsMasked = !!e.detail;
            });
        },
        showAddModal: !!config.showAddModal,
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
    };
}
