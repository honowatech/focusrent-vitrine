document.getElementById('menu-toggler')?.addEventListener('click', () => {
    const spans = document.querySelectorAll('#menu-toggler span');
    spans[0]?.classList.toggle('rotate-45');
    spans[0]?.classList.toggle('translate-y-2');
    spans[1]?.classList.toggle('opacity-0');
    spans[2]?.classList.toggle('-rotate-45');
    spans[2]?.classList.toggle('-translate-y-2');
    document.getElementById('mobile-menu')?.classList.toggle('hidden');
});

const carouselInner = document.querySelector('.carousel-inner');
if (carouselInner) {
    let activeIndex = 0;
    const updateCarousel = () => {
        [...carouselInner.children].forEach((el, index) => {
            el.classList.toggle('active', index === activeIndex);
        });
    };

    document.getElementById('prev-btn')?.addEventListener('click', () => {
        activeIndex = (activeIndex - 1 + carouselInner.children.length) % carouselInner.children.length;
        updateCarousel();
    });

    document.getElementById('next-btn')?.addEventListener('click', () => {
        activeIndex = (activeIndex + 1) % carouselInner.children.length;
        updateCarousel();
    });
}
