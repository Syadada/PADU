/**
 * PADU v2.0 Enterprise - Super Administrator Control Modules
 * Lokasi: public/js/admin.js
 */

function usersPage(config = {}) {
    return {
        showCreateModal: false,
        showEditModal: false,
        editUserId: '',
        editUserName: '',
        editUserEmail: '',
        editUserRole: 'operator',

        showDeleteModal: false,
        deleteUserId: '',
        deleteUserName: '',
        deleteUserEmail: '',
        inputDeleteEmail: '',

        showResetModal: !!config.tempPassword,
        tempUserName: config.tempUserName || '',
        tempUserEmail: config.tempUserEmail || '',
        tempPassword: config.tempPassword || '',
        copied: false,

        copyTempPassword() {
            if (!this.tempPassword) return;
            navigator.clipboard.writeText(this.tempPassword);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2500);
        },
        openEdit(id, name, email, role) {
            this.editUserId = id;
            this.editUserName = name;
            this.editUserEmail = email;
            this.editUserRole = role;
            this.showEditModal = true;
        },
        openDelete(id, name, email) {
            this.deleteUserId = id;
            this.deleteUserName = name;
            this.deleteUserEmail = email;
            this.inputDeleteEmail = '';
            this.showDeleteModal = true;
        },
        get isDeleteEmailMatched() {
            if (!this.inputDeleteEmail || !this.deleteUserEmail) return false;
            return this.inputDeleteEmail.trim().toLowerCase() === this.deleteUserEmail.trim().toLowerCase();
        }
    };
}

function twoFactorSetup(otpUri = '') {
    return {
        otpUri: otpUri,
        code: '',
        isScreenShielded: false,
        submitting: false,

        init() {
            this.$nextTick(() => {
                this.renderQRCode();
            });

            // 1. Deteksi Jendela Kehilangan Fokus (Snipping Tool, Win+Shift+S, Alt+Tab, Perekam Layar)
            window.addEventListener('blur', () => {
                this.isScreenShielded = true;
            });
            window.addEventListener('focus', () => {
                this.isScreenShielded = false;
            });

            // 2. Deteksi Tombol PrintScreen & Pintasan Screenshot
            window.addEventListener('keyup', (e) => {
                if (e.key === 'PrintScreen' || e.keyCode === 44) {
                    this.triggerAntiScreenshot();
                }
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'PrintScreen' || e.keyCode === 44) {
                    this.triggerAntiScreenshot();
                }
                if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                    e.preventDefault();
                    this.printSheet();
                }
                if ((e.metaKey || e.ctrlKey) && e.shiftKey && (e.key === 's' || e.key === 'S' || e.key === '3' || e.key === '4')) {
                    this.triggerAntiScreenshot();
                }
            });
        },

        triggerAntiScreenshot() {
            this.isScreenShielded = true;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    navigator.clipboard.writeText('[DILINDUNGI] Pengambilan tangkapan layar kredensial 2FA diblokir oleh sistem.');
                } catch (err) {}
            }
            setTimeout(() => {
                this.isScreenShielded = false;
            }, 3500);
        },

        renderQRCode() {
            const container = document.getElementById('qrcode-container');
            if (container && typeof QRCode !== 'undefined') {
                container.innerHTML = '';
                new QRCode(container, {
                    text: this.otpUri,
                    width: 170,
                    height: 170,
                    colorDark : '#0f172a',
                    colorLight : '#ffffff',
                    correctLevel : QRCode.CorrectLevel.M
                });
            }
        },

        formatCode() {
            this.code = this.code.replace(/[^0-9]/g, '').slice(0, 6);
        },

        printSheet() {
            window.print();
        }
    };
}

function backupPage(config = {}) {
    return {
        showPassModal: !!config.backupPassword,
        backupPassword: config.backupPassword || '',
        backupFilename: config.backupFilename || '',
        backupSha256: config.backupSha256 || '',
        isScreenShielded: false,

        init() {
            window.addEventListener('blur', () => {
                this.isScreenShielded = true;
            });
            window.addEventListener('focus', () => {
                this.isScreenShielded = false;
            });

            window.addEventListener('keyup', (e) => {
                if (e.key === 'PrintScreen' || e.keyCode === 44) {
                    this.triggerAntiScreenshot();
                }
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'PrintScreen' || e.keyCode === 44) {
                    this.triggerAntiScreenshot();
                }
                if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                    e.preventDefault();
                    this.printDisasterSheet();
                }
                if ((e.metaKey || e.ctrlKey) && e.shiftKey && (e.key === 's' || e.key === 'S' || e.key === '3' || e.key === '4')) {
                    this.triggerAntiScreenshot();
                }
            });
        },

        triggerAntiScreenshot() {
            this.isScreenShielded = true;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                try {
                    navigator.clipboard.writeText('[DILINDUNGI] Tangkapan layar Kunci Pemulihan Bencana DRP diblokir oleh sistem.');
                } catch (err) {}
            }
            setTimeout(() => {
                this.isScreenShielded = false;
            }, 3500);
        },

        printDisasterSheet() {
            window.print();
        }
    };
}
