/**
 * GuepardoSys Frontend JavaScript
 * Main frontend functionality
 */

// Utility functions
const GuepardoSys = {
	// Theme management
	theme: {
		init() {
			// Initialize theme based on user preference or system preference
			const theme =
				localStorage.getItem("theme") ||
				(window.matchMedia("(prefers-color-scheme: dark)").matches
					? "dark"
					: "light");
			this.set(theme);
		},

		set(theme) {
			document.documentElement.setAttribute("data-theme", theme);
			localStorage.setItem("theme", theme);
		},

		toggle() {
			const currentTheme = document.documentElement.getAttribute("data-theme");
			const newTheme = currentTheme === "dark" ? "light" : "dark";
			this.set(newTheme);
		},
	},

	// Form utilities
	form: {
		validate(formElement) {
			const inputs = formElement.querySelectorAll(
				"input[required], select[required], textarea[required]",
			);
			let isValid = true;

			inputs.forEach((input) => {
				if (!input.value.trim()) {
					this.showError(input, "Este campo é obrigatório");
					isValid = false;
				} else {
					this.clearError(input);
				}
			});

			return isValid;
		},

		showError(input, message) {
			const errorElement = input.parentElement.querySelector(".error-message");
			if (errorElement) {
				errorElement.textContent = message;
				errorElement.style.display = "block";
			}
			input.classList.add("error");
		},

		clearError(input) {
			const errorElement = input.parentElement.querySelector(".error-message");
			if (errorElement) {
				errorElement.style.display = "none";
			}
			input.classList.remove("error");
		},
	},

	// Toast notifications
	toast: {
		show(message, type = "info") {
			const toast = document.createElement("div");
			toast.className = `toast toast-${type}`;
			toast.textContent = message;

			document.body.appendChild(toast);

			setTimeout(() => {
				toast.classList.add("show");
			}, 100);

			setTimeout(() => {
				toast.classList.remove("show");
				setTimeout(() => {
					document.body.removeChild(toast);
				}, 300);
			}, 3000);
		},
	},

	// Initialize all components
	init() {
		this.theme.init();
		console.log("GuepardoSys Frontend initialized");
	},
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
	GuepardoSys.init();
});

// Export for global use
window.GuepardoSys = GuepardoSys;
