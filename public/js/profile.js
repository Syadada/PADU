/**
 * PADU v2.0 Enterprise - Profile & Password Management Controller
 * Lokasi: public/js/profile.js
 */

function profilePage() {
    return {
        newPassword: '',
        confirmPassword: '',
        showPass: false,
        get checks() {
            return {
                length: this.newPassword.length >= 15,
                upper: /[A-Z]/.test(this.newPassword),
                lower: /[a-z]/.test(this.newPassword),
                number: /[0-9]/.test(this.newPassword),
                symbol: /[\W_]/.test(this.newPassword)
            };
        },
        get allValid() {
            return this.checks.length && this.checks.upper && this.checks.lower && this.checks.number && this.checks.symbol;
        },
        get passwordsMatch() {
            return this.newPassword && this.newPassword === this.confirmPassword;
        }
    };
}
