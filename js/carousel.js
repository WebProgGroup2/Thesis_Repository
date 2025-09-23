// Creates an infinite-looking loop by cloning slides and setting a duration

document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector(".carousel__track");
    if (!track) return;

    // Grab original slides
    const originalSlides = Array.from(track.children);
    if (originalSlides.length === 0) return;

    // === 1) Clone slides to create a seamless repeat ===
    originalSlides.forEach(slide => {
        const clone = slide.cloneNode(true);
        track.appendChild(clone);
    });

    // === 2) Set animation duration dynamically so speed is reasonable ===
    // Simple heuristic: 3 seconds per original slide (tweak multiplier below)
    const secondsPerSlide = 3; // <- adjust speed (smaller = faster)
    const durationSec = Math.max(8, originalSlides.length * secondsPerSlide);

    // Write to CSS variable used by the animation
    track.style.setProperty("--duration", `${durationSec}s`);

    // Force a reflow so the animation picks up the new duration cleanly
    // (helps avoid jitter on some browsers)
    // eslint-disable-next-line no-unused-expressions
    track.offsetWidth; // read triggers reflow

    // (Optional) make sure animation runs (should by default)
    track.style.animationPlayState = "running";
});
