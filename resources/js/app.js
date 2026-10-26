import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

/* Lightweight scroll-reveal: replaces the AOS library with a native
IntersectionObserver. Elements with class="reveal" fade-and-slide in
once when they enter the viewport. No external dependency needed. */
const observer = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.12 }
);

const observeReveals = () => {
    document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => {
        observer.observe(el);
    });
};

document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));

/* Re-observe after Livewire navigations (wire:navigate) and dynamic component morphs */
document.addEventListener('livewire:navigated', observeReveals);

document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        window.Livewire.hook('commit', () => {
            requestAnimationFrame(observeReveals);
        });
    }
});

window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination };
