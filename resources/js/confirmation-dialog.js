export function confirmationDialog() {
    return {
        busy: false,
        error: '',
        open() {
            if (this.busy || this.$refs.dialog.open) return;
            this.error = '';
            this.$refs.dialog.showModal();
            this.$refs.cancel.focus();
        },
        cancel() {
            if (!this.busy) this.$refs.dialog.close();
        },
        async confirm(action) {
            if (this.busy || !this.$refs.dialog.open) return;
            this.busy = true;
            this.error = '';
            try {
                await action();
                this.$refs.dialog.close();
            } catch {
                this.error = 'The action could not be completed. Please try again.';
            } finally {
                this.busy = false;
            }
        },
    };
}
