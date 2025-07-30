/**
 * =================================================================
 * JavaScript Utama (script.js)
 * =================================================================
 * @version   : 1.4 (Pemisahan Menu Mobile & Desktop)
 * @author    : Gemini
 * @description: Meng-handle semua interaktivitas di sisi klien.
 */

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Mengelola Mode Terang/Gelap
     */
    const ThemeController = (() => {
        const themeToggleButton = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }

        themeToggleButton?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            let theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
        });
    })();

            // Logika untuk membuka/menutup dropdown di mobile
        const mobileDropdownToggle = document.querySelector('.dropdown-toggle-mobile');
        const mobileDropdownItem = document.querySelector('.nav-item-dropdown-mobile');

        if(mobileDropdownToggle && mobileDropdownItem) {
            mobileDropdownToggle.addEventListener('click', (e) => {
                e.preventDefault(); // Mencegah link default
                e.stopPropagation(); // Mencegah menu utama tertutup
                mobileDropdownItem.classList.toggle('active');
            });
        }

    /**
     * Mengelola Menu Navigasi Mobile
     */
    const MobileMenuController = (() => {
        const menuToggleButton = document.getElementById('mobile-menu-toggle');
        const overlay = document.getElementById('page-overlay');

        if (!menuToggleButton || !overlay) return;

        const toggleMenu = () => {
            overlay.classList.toggle('active');
            document.body.style.overflow = overlay.classList.contains('active') ? 'hidden' : '';
        };

        menuToggleButton.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                toggleMenu();
            }
        });
    })();

    /**
     * Menginisialisasi Slider Gambar (Hanya di Halaman Dashboard)
     */
    const SwiperSliderController = (() => {
        const swiperContainer = document.querySelector('.swiper-container');
        if (swiperContainer) {
            const swiper = new Swiper(swiperContainer, {
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
            });
        }
    })();

    /**
     * Memberikan efek animasi saat elemen di-scroll ke dalam viewport
     */
    const AnimateOnScroll = (() => {
        const animatedElements = document.querySelectorAll('.card, .table-container, .stats-container > div, .summary-cards-horizontal > div');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        animatedElements.forEach(el => {
            observer.observe(el);
        });
    })();

});
