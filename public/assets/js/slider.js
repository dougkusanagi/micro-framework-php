/**
 * Glide.js Slider Component
 * Example implementation for image sliders
 */

// Initialize Glide.js sliders
document.addEventListener("DOMContentLoaded", function () {
	// Basic slider configuration
	const sliders = document.querySelectorAll(".glide");

	sliders.forEach((slider) => {
		const glide = new Glide(slider, {
			type: "carousel",
			startAt: 0,
			perView: 1,
			focusAt: "center",
			gap: 20,
			autoplay: 4000,
			hoverpause: true,
			keyboard: true,
			breakpoints: {
				1024: {
					perView: 3,
				},
				768: {
					perView: 2,
				},
				480: {
					perView: 1,
				},
			},
		});

		glide.mount();
	});

	// Product showcase slider
	const productSliders = document.querySelectorAll(".product-slider");

	productSliders.forEach((slider) => {
		const glide = new Glide(slider, {
			type: "carousel",
			startAt: 0,
			perView: 4,
			focusAt: "center",
			gap: 30,
			autoplay: 5000,
			hoverpause: true,
			keyboard: true,
			breakpoints: {
				1200: {
					perView: 3,
				},
				992: {
					perView: 2,
				},
				576: {
					perView: 1,
				},
			},
		});

		glide.mount();
	});

	// Hero banner slider
	const heroSliders = document.querySelectorAll(".hero-slider");

	heroSliders.forEach((slider) => {
		const glide = new Glide(slider, {
			type: "carousel",
			startAt: 0,
			perView: 1,
			focusAt: "center",
			gap: 0,
			autoplay: 6000,
			hoverpause: true,
			keyboard: true,
			animationDuration: 800,
			animationTimingFunc: "ease-in-out",
		});

		glide.mount();
	});
});

// Utility function to create a slider programmatically
function createSlider(element, options = {}) {
	const defaultOptions = {
		type: "carousel",
		startAt: 0,
		perView: 1,
		focusAt: "center",
		gap: 20,
		autoplay: 4000,
		hoverpause: true,
		keyboard: true,
	};

	const config = { ...defaultOptions, ...options };
	const glide = new Glide(element, config);
	glide.mount();

	return glide;
}

// Export for global use
window.createSlider = createSlider;
