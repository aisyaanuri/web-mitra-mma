import AOS from 'aos';
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

AOS.init({ once: true, duration: 600, offset: 40 });

window.Swiper = Swiper;
window.SwiperModules = { Navigation, Pagination };
