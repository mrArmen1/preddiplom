
document.addEventListener("DOMContentLoaded", function () {
    const workshopItems = document.querySelectorAll('.workshop-item');

    workshopItems.forEach(function (item) {
        item.addEventListener('click', function () {
            this.classList.toggle('active');
        });
    });
});

document.addEventListener('mousemove', e => {
    const x = e.clientX;
    const y = e.clientY;
    document.querySelector('.overlay').style.background = `radial-gradient(circle at ${x}px ${y}px, rgb(53, 61, 48) 0%, #18181af8 100%)`;
});


/**/