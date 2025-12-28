document.addEventListener("DOMContentLoaded", function () {
    const toastEl = document.getElementById('toastMensagem');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl, {
            delay: 5000
        });
        toast.show();
    }
});