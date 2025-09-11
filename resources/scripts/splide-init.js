document.addEventListener('DOMContentLoaded', function () {
    new Splide('#specialoffers-slider', {
        type   : 'loop',
        perPage: 1,
        autoplay: true,
        interval: 3000,
        pauseOnHover: true,
        pagination: false,
        arrows: false,
    }).mount()
    });
