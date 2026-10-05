/**
 * PADU v2.0 Enterprise - Authentication Controller Scripts
 * Lokasi: public/js/auth.js
 */

if (typeof document !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('loginForm', (config) => loginForm(config));
        }
    });
}

function loginForm(config) {
    config = config || (typeof window !== 'undefined' && window.LOGIN_CONFIG ? window.LOGIN_CONFIG : {}) || {};
    return {
        step: config.step || 1,
        email: config.email || '',
        password: '',
        showPassword: false,
        loading: false,
        submitting: false,
        userInfo: config.userInfo || null,
        errorMessage: '',
        isLocked: false,
        lockCountdown: 0,
        timerInterval: null,
        checkEmailUrl: config.checkEmailUrl || '/auth/check-email',
        csrfToken: config.csrfToken || (typeof document !== 'undefined' ? (document.querySelector('input[name="_token"]')?.value || '') : '') || '',

        init() {
            if (this.step === 2) {
                if (!this.userInfo && this.email) {
                    this.fetchUserInfoSilently(this.email);
                }
                this.$nextTick(() => {
                    if (this.$refs.passwordInput) {
                        this.$refs.passwordInput.focus();
                    }
                });
            }
        },

        async fetchUserInfoSilently(emailToFetch) {
            try {
                const res = await fetch(this.checkEmailUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: emailToFetch })
                });
                if (res.ok) {
                    const data = await res.json();
                    this.userInfo = data;
                }
            } catch (e) {
                // silent fallback
            }
        },

        fillAccount(targetEmail, targetPassword) {
            this.email = targetEmail;
            this.password = targetPassword || '';
            this.submitEmail();
        },

        async submitEmail() {
            this.errorMessage = '';
            if (!this.email || !this.email.includes('@')) {
                this.errorMessage = 'Silakan masukkan alamat surel dinas yang valid.';
                return;
            }

            this.loading = true;
            try {
                const res = await fetch(this.checkEmailUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: this.email })
                });

                const data = await res.json();
                this.loading = false;

                if (!res.ok) {
                    this.errorMessage = data.message || 'Alamat surel tidak terdaftar dalam direktori sistem.';
                    if (data.locked) {
                        this.isLocked = true;
                        this.startLockCountdown(data.remaining);
                    }
                    return;
                }

                this.userInfo = data;
                this.step = 2;
                this.$nextTick(() => {
                    if (this.$refs.passwordInput) {
                        this.$refs.passwordInput.focus();
                    }
                });
            } catch (err) {
                this.loading = false;
                this.errorMessage = 'Gagal menghubungi server lokal. Pastikan server PADU telah berjalan.';
            }
        },

        backToEmail() {
            this.step = 1;
            this.password = '';
            this.errorMessage = '';
        },

        startLockCountdown(seconds) {
            this.lockCountdown = seconds;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.lockCountdown > 0) {
                    this.lockCountdown--;
                } else {
                    clearInterval(this.timerInterval);
                    this.isLocked = false;
                    this.errorMessage = 'Masa penguncian telah berakhir. Silakan coba masuk kembali.';
                }
            }, 1000);
        },

        formatSeconds(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return (m > 0 ? m + ' Menit ' : '') + s + ' Detik';
        }
    };
}

function twoFactorVerify() {
    return {
        mode: 'totp',
        code: '',
        recoveryKey: '',
        submitting: false,

        init() {
            this.$nextTick(() => {
                if (this.$refs.codeInput) {
                    this.$refs.codeInput.focus();
                }
            });
        },

        formatCode() {
            this.code = this.code.replace(/[^0-9]/g, '').slice(0, 6);
        },

        formatRecoveryKey() {
            this.recoveryKey = this.recoveryKey.toUpperCase().replace(/\s+/g, '');
        },

        switchToRecovery() {
            this.mode = 'recovery';
            this.$nextTick(() => {
                if (this.$refs.recoveryInput) {
                    this.$refs.recoveryInput.focus();
                }
            });
        },

        switchToTotp() {
            this.mode = 'totp';
            this.$nextTick(() => {
                if (this.$refs.codeInput) {
                    this.$refs.codeInput.focus();
                }
            });
        }
    };
}

function forceResetForm() {
    return {
        password: '',
        password_confirmation: '',
        showPass: false,
        get hasMinLen() { return this.password.length >= 15; },
        get hasUpper() { return /[A-Z]/.test(this.password); },
        get hasLower() { return /[a-z]/.test(this.password); },
        get hasNumber() { return /[0-9]/.test(this.password); },
        get hasSymbol() { return /[@$!%*#?&\-_+=]/.test(this.password); },
        get isMatch() { return this.password && this.password === this.password_confirmation; },
        get isValidAll() {
            return this.hasMinLen && this.hasUpper && this.hasLower && this.hasNumber && this.hasSymbol && this.isMatch;
        }
    };
}
