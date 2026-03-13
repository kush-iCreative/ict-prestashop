 document.addEventListener('DOMContentLoaded', function() {
    // Initialize directly if it's not in a modal
    var bannerSwiper = new Swiper(".swiper", {
        loop: true,
        autoplay: {
            delay: 2000,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
});