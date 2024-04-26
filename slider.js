let currentMerchSlide = 0;
const itemsPerSlide = 3;
let autoScrollInterval;

function showMerchSlide(index) {
    const merchSlidesContainer = document.querySelector('.merchandise-slides-container');
    const totalMerchSlides = document.querySelectorAll('.merch-slide').length;
    const slideWidth = document.querySelector('.merch-slide').offsetWidth;
    const spacing = 12;

    currentMerchSlide = (index + totalMerchSlides) % totalMerchSlides;

    const newMerchTransformValue = -currentMerchSlide * (slideWidth + spacing) + 'px';
    merchSlidesContainer.style.transform = 'translateX(' + newMerchTransformValue + ')';
}

function changeMerchSlide(direction) {
    showMerchSlide(currentMerchSlide + direction * itemsPerSlide);
    resetAutoScroll();
}

function startAutoScroll() {
    autoScrollInterval = setInterval(function () {
        changeMerchSlide(1);
    }, 15000);
}

function stopAutoScroll() {
    clearInterval(autoScrollInterval);
}

function resetAutoScroll() {
    stopAutoScroll();
    startAutoScroll();
}

startAutoScroll();
