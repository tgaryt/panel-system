document.addEventListener('DOMContentLoaded', () => {
	const links = document.querySelectorAll('nav ul li a');
	links.forEach(link => {
		link.addEventListener('mouseover', () => {
			link.style.color = '#ff4500';
		});
		link.addEventListener('mouseout', () => {
			link.style.color = '#f5f5f5';
		});
	});

	const menuIcon = document.querySelector('.menu-icon');
	menuIcon.addEventListener('click', () => {
		document.querySelector('nav ul').classList.toggle('show');
	});

	const form = document.querySelector('form');
	if (form) {
		const inputs = form.querySelectorAll('input, select');
		const initialFormValues = Array.from(inputs).reduce((acc, input) => {
			acc[input.name] = input.value;
			return acc;
		}, {});
		const submitButton = form.querySelector('input[type="submit"]');

		function toggleSubmitButton() {
			let formChanged = false;

			inputs.forEach(input => {
				if (input.type === 'select-one' && input.value !== initialFormValues[input.name]) {
					formChanged = true;
				} else if (input.type === 'text' && input.value !== initialFormValues[input.name]) {
					formChanged = true;
				}
			});

			submitButton.disabled = !formChanged;
		}

		inputs.forEach(input => {
			input.addEventListener('change', () => {
				toggleSubmitButton();
				if (submitButton.disabled) {
					submitButton.classList.add('unclickable');
				} else {
					submitButton.classList.remove('unclickable');
				}
			});

			if (input.type === 'text') {
				input.addEventListener('input', () => {
					toggleSubmitButton();
					if (submitButton.disabled) {
						submitButton.classList.add('unclickable');
					} else {
						submitButton.classList.remove('unclickable');
					}
				});
			}
		});

		form.addEventListener('submit', (e) => {
			submitButton.value = 'Processing...';
			submitButton.disabled = true;
		});

		form.addEventListener('reset', () => {
			inputs.forEach(input => {
				input.value = initialFormValues[input.name];
			});
			toggleSubmitButton();
			submitButton.classList.add('unclickable');
		});
	}
});
