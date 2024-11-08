const slides = document.querySelectorAll('.slider img');
let currentSlide = 0;

function changeSlide() {
    slides[currentSlide].classList.remove('active');
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add('active');
}

slides[currentSlide].classList.add('active');
setInterval(changeSlide, 6000);

const navLinks = document.querySelectorAll('.slider-nav a');

navLinks.forEach((link, index) => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        slides[currentSlide].classList.remove('active');
        currentSlide = index;
        slides[currentSlide].classList.add('active');
    });
});
