/**
 * =================================================================
 * JavaScript Panel Admin (admin_script.js)
 * =================================================================
 * @version   : 1.0
 * @author    : Gemini
 * @description: Meng-handle semua interaktivitas di sisi klien untuk
 * panel admin.
 *
 * Daftar Fungsi:
 * 1. DOMContentLoaded: Inisialisasi semua controller.
 * 2. SidebarController: Mengelola toggle sidebar (buka/tutup).
 * 3. ThemeControllerAdmin: Mengelola mode terang/gelap khusus admin.
 * 4. DropdownController: Mengelola dropdown profil admin.
 * =================================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Mengelola toggle sidebar (buka/tutup/mobile)
     */
    const SidebarController = (() => {
        const sidebarToggleButton = document.getElementById('sidebar-toggle');
        const body = document.body;

        // Cek preferensi dari localStorage
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            body.classList.add('sidebar-collapsed');
        }

        sidebarToggleButton?.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                // Perilaku untuk mobile (overlay)
                body.classList.toggle('sidebar-open');
            } else {
                // Perilaku untuk desktop (collapse)
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', body.classList.contains('sidebar-collapsed'));
            }
        });
    })();


    /**
     * Mengelola Mode Terang/Gelap untuk Panel Admin
     */
    const ThemeControllerAdmin = (() => {
        const themeToggleButton = document.getElementById('theme-toggle-admin');
        const currentTheme = localStorage.getItem('admin_theme');

        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }

        themeToggleButton?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            let theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem('admin_theme', theme);
        });
    })();


    /**
     * Mengelola Dropdown Profil Admin
     */
    const DropdownController = (() => {
        const profileBtn = document.querySelector('.profile-btn');
        const dropdown = profileBtn?.closest('.admin-profile-dropdown');

        profileBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });

        // Menutup dropdown jika klik di luar
        document.addEventListener('click', (e) => {
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    })();

});

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Mengelola toggle sidebar (buka/tutup/mobile)
     */
    const SidebarController = (() => {
        const sidebarToggleButton = document.getElementById('sidebar-toggle');
        const body = document.body;

        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            body.classList.add('sidebar-collapsed');
        }

        sidebarToggleButton?.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', body.classList.contains('sidebar-collapsed'));
            }
        });
    })();


    /**
     * Mengelola Mode Terang/Gelap untuk Panel Admin
     */
    const ThemeControllerAdmin = (() => {
        const themeToggleButton = document.getElementById('theme-toggle-admin');
        const currentTheme = localStorage.getItem('admin_theme');

        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }

        themeToggleButton?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            let theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem('admin_theme', theme);
        });
    })();


    /**
     * FUNGSI BARU: Mengelola Dropdown Menu di Sidebar
     */
    const SidebarDropdownController = (() => {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle-admin');

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const parentItem = toggle.closest('.nav-item-dropdown-admin');
                parentItem.classList.toggle('active');
            });
        });
    })();

});