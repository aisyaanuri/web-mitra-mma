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

document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));

window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination };

// Force reload when navigated via browser back/forward cache (bfcache)
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});
