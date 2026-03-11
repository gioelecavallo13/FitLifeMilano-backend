// ------------------- Contatori animati (durata minima 2s) -------------------
const COUNTER_DURATION = 2000; // ms
const counters = document.querySelectorAll('.counter');
counters.forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const startTime = Date.now();

    const updateCount = () => {
        const elapsed = Date.now() - startTime;
        const value = Math.min(target, Math.ceil((elapsed / COUNTER_DURATION) * target));
        counter.innerText = value;

        if (elapsed < COUNTER_DURATION) {
            requestAnimationFrame(updateCount);
        } else {
            counter.innerText = target;
        }
    };
    updateCount();
});

// ------------------- Scroll banner -------------------
const scrollWrapper = document.getElementById('scrollBanner');
const stats = document.querySelectorAll('.stat');

let leftPos;
const speed = 4; // px per frame
let targetPos = null;
let animationId = null;

// Stat da centrare (quella dei "Kg sollevati" → index 1)
const middleStat = stats[1];

function calculateTarget() {
    const screenCenter = window.innerWidth / 2;
    const statOffset = middleStat.offsetLeft + middleStat.offsetWidth / 2;
    return screenCenter - statOffset;
}

function startAnimation() {
    cancelAnimationFrame(animationId);

    leftPos = window.innerWidth;
    targetPos = calculateTarget();

    function animateScrollBanner() {
        leftPos -= speed;

        if (leftPos <= targetPos) {
            leftPos = targetPos;
            scrollWrapper.style.left = leftPos + 'px';
            return;
        }

        scrollWrapper.style.left = leftPos + 'px';
        animationId = requestAnimationFrame(animateScrollBanner);
    }

    animationId = requestAnimationFrame(animateScrollBanner);
}

startAnimation();

window.addEventListener('resize', () => {
    startAnimation();
});
