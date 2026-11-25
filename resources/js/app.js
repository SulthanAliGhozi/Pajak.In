function initMobileMenu() {
    const toggle = document.querySelector('[data-mobile-menu-toggle]');
    const panel = document.querySelector('[data-mobile-menu-panel]');
    if (!toggle || !panel) return;

    const closeMenu = () => {
        panel.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isOpen = !panel.classList.contains('hidden');
        if (isOpen) {
            closeMenu();
        } else {
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
            closeMenu();
            toggle.focus();
        }
    });
}

function initLoginPortalTabs() {
    const tabList = document.querySelector('[data-portal-tablist]');
    const portalInput = document.getElementById('portal-input');
    if (!tabList || !portalInput) return;

    const tabs = Array.from(tabList.querySelectorAll('[data-portal-tab]'));
    const portalHint = document.getElementById('portal-helper-text');
    const emailInput = document.getElementById('email');

    const descriptions = {
        wajib_pajak: 'Masuk sebagai Wajib Pajak Pribadi atau Badan Usaha untuk mengelola kendaraan, unggah berkas, dan bayar tagihan PKB.',
        petugas: 'Masuk sebagai Petugas Pajak Samsat untuk memeriksa berkas KTP/STNK, memverifikasi NJKB, dan memantau tagihan.',
    };

    const activateTab = (targetTab, shouldFocus = false) => {
        const portal = targetTab.getAttribute('data-portal-tab');
        if (!portal) return;

        portalInput.value = portal;

        tabs.forEach((tab) => {
            const isSelected = tab === targetTab;
            tab.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            tab.setAttribute('tabindex', isSelected ? '0' : '-1');
            tab.classList.toggle('bg-[#0F2942]', isSelected);
            tab.classList.toggle('text-white', isSelected);
            tab.classList.toggle('bg-white', !isSelected);
            tab.classList.toggle('text-[#0F172A]', !isSelected);
            tab.classList.toggle('hover:bg-[#F4F6F9]', !isSelected);
        });

        if (portalHint && descriptions[portal]) {
            portalHint.textContent = descriptions[portal];
        }

        if (emailInput) {
            emailInput.placeholder = portal === 'petugas' ? 'petugas@pajakin.local' : 'nama@pajakin.local';
        }

        if (shouldFocus) {
            targetTab.focus();
        }
    };

    const initialPortal = portalInput.value || 'wajib_pajak';
    const initialTab = tabs.find((t) => t.getAttribute('data-portal-tab') === initialPortal) || tabs[0];
    if (initialTab) {
        activateTab(initialTab, false);
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab, false));
        tab.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
                event.preventDefault();
                const nextIndex = event.key === 'ArrowRight'
                    ? (index + 1) % tabs.length
                    : (index - 1 + tabs.length) % tabs.length;
                activateTab(tabs[nextIndex], true);
            }
        });
    });

    document.querySelectorAll('[data-demo-fill]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const email = btn.getAttribute('data-demo-email') || '';
            const password = btn.getAttribute('data-demo-password') || '';
            const portal = btn.getAttribute('data-demo-portal') || 'wajib_pajak';

            const matchingTab = tabs.find((t) => t.getAttribute('data-portal-tab') === portal);
            if (matchingTab) activateTab(matchingTab, false);

            const passwordInput = document.getElementById('password');
            if (emailInput) emailInput.value = email;
            if (passwordInput) passwordInput.value = password;
            if (emailInput) emailInput.focus();
        });
    });
}

function initLoadingForms() {
    document.querySelectorAll('form[data-loading-submit]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const submitter = event.submitter || form.querySelector('button[type="submit"]');
            if (!submitter || submitter.disabled) return;

            const loadingLabel = submitter.getAttribute('data-loading-text') || 'Memproses...';
            submitter.disabled = true;
            submitter.setAttribute('aria-busy', 'true');
            submitter.innerHTML = `<span class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" aria-hidden="true"></span><span>${loadingLabel}</span>`;
        });
    });
}

function initFilePreviews() {
    document.querySelectorAll('input[type="file"][data-file-preview]').forEach((input) => {
        const targetId = input.getAttribute('data-file-preview');
        const target = targetId ? document.getElementById(targetId) : null;
        if (!target) return;

        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) {
                target.textContent = 'Belum ada berkas baru dipilih.';
                target.className = 'mt-1.5 text-xs text-[#475569]';
                return;
            }
            const sizeKb = (file.size / 1024).toFixed(1);
            target.textContent = `Berkas dipilih: ${file.name} (${sizeKb} KB)`;
            target.className = 'mt-1.5 text-xs font-medium text-[#065F46]';
        });
    });
}

function initNjkbLookup() {
    const button = document.querySelector('[data-njkb-lookup]');
    if (!button) return;

    const statusBox = document.getElementById('njkb-lookup-status');
    const njkbInput = document.getElementById('njkb');

    const setStatus = (state, message) => {
        if (!statusBox) return;
        statusBox.classList.remove('hidden', 'bg-[#F4F6F9]', 'text-[#0F172A]', 'border-[#E2E8F0]', 'bg-[#D1FAE5]', 'text-[#065F46]', 'border-[#10B981]/40', 'bg-[#FEE2E2]', 'text-[#991B1B]', 'border-[#EF4444]/40');
        if (state === 'loading') {
            statusBox.classList.add('bg-[#F4F6F9]', 'text-[#0F172A]', 'border-[#E2E8F0]');
        } else if (state === 'success') {
            statusBox.classList.add('bg-[#D1FAE5]', 'text-[#065F46]', 'border-[#10B981]/40');
        } else if (state === 'error') {
            statusBox.classList.add('bg-[#FEE2E2]', 'text-[#991B1B]', 'border-[#EF4444]/40');
        }
        statusBox.textContent = message;
    };

    button.addEventListener('click', async () => {
        const jenis = document.getElementById('jenis_kendaraan')?.value?.trim() || '';
        const merek = document.getElementById('merek')?.value?.trim() || '';
        const tahun = document.getElementById('tahun_pembuatan')?.value?.trim() || '';
        const cc = document.getElementById('kapasitas_cc')?.value?.trim() || '';

        if (!jenis || !merek || !tahun || !cc) {
            setStatus('error', 'Lengkapi jenis kendaraan, merek, tahun pembuatan, dan kapasitas CC sebelum mengambil estimasi NJKB.');
            return;
        }

        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = 'Mengambil estimasi...';
        setStatus('loading', 'Menghubungi API referensi NJKB...');

        try {
            const params = new URLSearchParams({ jenis, merek, tahun, cc });
            const endpoint = button.getAttribute('data-endpoint') || '/api/referensi/njkb';
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Gagal mengambil estimasi NJKB dari server.');
            }

            const data = payload.data || payload;
            const estimated = Number(data.estimated_njkb);
            if (!Number.isFinite(estimated) || estimated <= 0) {
                throw new Error('Nilai estimasi NJKB tidak tersedia untuk parameter tersebut.');
            }

            if (njkbInput) {
                njkbInput.value = String(Math.round(estimated));
            }

            const formatted = new Intl.NumberFormat('id-ID').format(Math.round(estimated));
            const sourceLabel = payload.source === 'api' ? 'API Referensi Eksternal' : 'Referensi Tarif Lokal';
            setStatus('success', `Estimasi NJKB berhasil diisi: Rp ${formatted} (Sumber: ${sourceLabel}). Anda tetap dapat menyesuaikan nominal sesuai faktur kendaraan.`);
        } catch (error) {
            const msg = error instanceof Error ? error.message : 'Terjadi gangguan jaringan saat mengambil estimasi NJKB.';
            setStatus('error', `${msg} Silakan isi nominal NJKB secara manual.`);
        } finally {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.innerHTML = originalHtml;
        }
    });
}

function initApplyReferenceNjkb() {
    document.querySelectorAll('[data-apply-njkb]').forEach((button) => {
        button.addEventListener('click', () => {
            const value = button.getAttribute('data-apply-njkb');
            const target = document.getElementById('njkb-approve-input');
            if (value && target) {
                target.value = value;
                target.focus();
            }
        });
    });
}

function initMidtransPayment() {
    const payButton = document.querySelector('[data-midtrans-pay]');
    if (!payButton) return;

    const statusBox = document.getElementById('midtrans-status-box');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setStatus = (state, message) => {
        if (!statusBox) return;
        statusBox.classList.remove(
            'hidden',
            'bg-[#F4F6F9]', 'text-[#0F172A]', 'border-[#E2E8F0]',
            'bg-[#D1FAE5]', 'text-[#065F46]', 'border-[#10B981]/40',
            'bg-[#FEE2E2]', 'text-[#991B1B]', 'border-[#EF4444]/40',
        );
        if (state === 'success') {
            statusBox.classList.add('bg-[#D1FAE5]', 'text-[#065F46]', 'border-[#10B981]/40');
        } else if (state === 'error') {
            statusBox.classList.add('bg-[#FEE2E2]', 'text-[#991B1B]', 'border-[#EF4444]/40');
        } else {
            statusBox.classList.add('bg-[#F4F6F9]', 'text-[#0F172A]', 'border-[#E2E8F0]');
        }
        statusBox.textContent = message;
    };

    const syncStatus = async (syncUrl) => {
        setStatus('loading', 'Memverifikasi status pembayaran ke Midtrans...');
        try {
            const res = await fetch(syncUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const data = await res.json();
            if (res.ok && data.status === 'lunas') {
                setStatus('success', data.message || 'Pembayaran berhasil. Memuat ulang halaman...');
                window.location.reload();
                return;
            }
            setStatus(res.ok ? 'loading' : 'error', data.message || data.error || 'Status pembayaran masih pending.');
            if (res.ok) {
                window.location.reload();
            }
        } catch {
            setStatus('error', 'Gagal memverifikasi status pembayaran. Gunakan tombol Periksa Status Midtrans.');
        }
    };

    payButton.addEventListener('click', async () => {
        const tokenUrl = payButton.getAttribute('data-token-url');
        const syncUrl = payButton.getAttribute('data-sync-url');
        if (!tokenUrl || !syncUrl) return;

        const originalText = payButton.textContent;
        payButton.disabled = true;
        payButton.setAttribute('aria-busy', 'true');
        payButton.textContent = 'Menyiapkan Midtrans Snap...';
        setStatus('loading', 'Menghubungi Midtrans Sandbox untuk membuat Snap Token...');

        try {
            const response = await fetch(tokenUrl, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const payload = await response.json();
            if (!response.ok || !payload.data?.snap_token) {
                throw new Error(payload.error || 'Gagal mendapatkan Snap Token dari Midtrans.');
            }

            const { snap_token: snapToken, redirect_url: redirectUrl } = payload.data;
            if (typeof window.snap !== 'undefined' && typeof window.snap.pay === 'function') {
                setStatus('loading', 'Jendela pembayaran Midtrans Snap terbuka. Selesaikan simulasi pembayaran.');
                window.snap.pay(snapToken, {
                    onSuccess: () => syncStatus(syncUrl),
                    onPending: () => syncStatus(syncUrl),
                    onError: () => setStatus('error', 'Pembayaran gagal diproses di Midtrans. Silakan coba lagi.'),
                    onClose: () => syncStatus(syncUrl),
                });
            } else if (redirectUrl) {
                window.open(redirectUrl, '_blank', 'noopener');
                setStatus('loading', 'Halaman Midtrans dibuka di tab baru. Setelah selesai, klik Periksa Status Midtrans.');
            } else {
                throw new Error('Skrip Midtrans Snap belum siap. Muat ulang halaman lalu coba kembali.');
            }
        } catch (error) {
            const msg = error instanceof Error ? error.message : 'Terjadi gangguan saat menghubungi Midtrans.';
            setStatus('error', msg);
        } finally {
            payButton.disabled = false;
            payButton.removeAttribute('aria-busy');
            payButton.textContent = originalText;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initLoginPortalTabs();
    initLoadingForms();
    initFilePreviews();
    initNjkbLookup();
    initApplyReferenceNjkb();
    initMidtransPayment();
});
