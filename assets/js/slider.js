let activeIndex = 0;
const items = document.querySelectorAll('.carousel-item');
const bullets = document.querySelectorAll('.carousel-indicator__button');

function updateSlider(index){
    const wrapper = document.querySelector('.carousel-wrapper');
    wrapper.style.transform = `translateX(-${index * 100}%)`;
    bullets.forEach(b => b.classList.remove('carousel-indicator--active'));
    bullets[index].classList.add('carousel-indicator--active');
    activeIndex = index;
}

bullets.forEach((bullet, i) => {
    bullet.addEventListener('click', () => updateSlider(i));
});

// Autoplay opcional
setInterval(() => {
    let nextIndex = (activeIndex + 1) % items.length;
    updateSlider(nextIndex);
}, 5000);