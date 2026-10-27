const sidebar = document.querySelector('[data-sidebar]');
const backdrop = document.querySelector('[data-sidebar-backdrop]');

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    backdrop?.classList.add('hidden');
};

document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => {
    sidebar?.classList.remove('-translate-x-full');
    backdrop?.classList.remove('hidden');
});

document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
backdrop?.addEventListener('click', closeSidebar);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const showCredentialMessage = (element, message, isError = false) => {
    if (!element) {
        return;
    }

    element.textContent = message;
    element.classList.toggle('text-red-600', isError);
    element.classList.toggle('text-stone-500', !isError);
};

document.querySelectorAll('[data-toggle-favorite]').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.dataset.toggleFavorite;

        button.disabled = true;
        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error('favorite request failed');
            }

            window.location.reload();
        } catch {
            showCredentialMessage(button.closest('[data-credential-card]')?.querySelector('[data-credential-message]'), 'Favorite could not be updated.', true);
        } finally {
            button.disabled = false;
        }
    });
});

document.querySelectorAll('[data-reveal-password]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('[data-credential-card]');
        const password = card?.querySelector('[data-password-value]');
        const message = card?.querySelector('[data-credential-message]');

        if (!password) {
            return;
        }

        if (button.getAttribute('aria-pressed') === 'true') {
            password.textContent = '••••••••';
            button.setAttribute('aria-pressed', 'false');
            button.textContent = 'Reveal';
            return;
        }

        button.disabled = true;
        try {
            const response = await fetch(button.dataset.revealPassword, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error('password request failed');
            }

            const data = await response.json();
            password.textContent = data.password;
            button.setAttribute('aria-pressed', 'true');
            button.textContent = 'Hide';
        } catch {
            password.textContent = '••••••••';
            showCredentialMessage(message, 'Password could not be revealed.', true);
        } finally {
            button.disabled = false;
        }
    });
});

document.querySelectorAll('[data-copy-username]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('[data-credential-card]');
        const username = card?.querySelector('[data-username-value]')?.textContent;
        const message = card?.querySelector('[data-credential-message]');

        if (!username || username === 'Not provided' || !navigator.clipboard) {
            showCredentialMessage(message, 'Username could not be copied.', true);
            return;
        }

        try {
            await navigator.clipboard.writeText(username);
            showCredentialMessage(message, 'Username copied.');
        } catch {
            showCredentialMessage(message, 'Username could not be copied.', true);
        }
    });
});

document.querySelectorAll('[data-copy-password]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('[data-credential-card]');
        const message = card?.querySelector('[data-credential-message]');

        if (!navigator.clipboard) {
            showCredentialMessage(message, 'Password could not be copied.', true);
            return;
        }

        button.disabled = true;
        try {
            const response = await fetch(button.dataset.copyPassword, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error('password request failed');
            }

            const data = await response.json();
            await navigator.clipboard.writeText(data.password);
            showCredentialMessage(message, 'Password copied.');
        } catch {
            showCredentialMessage(message, 'Password could not be copied.', true);
        } finally {
            button.disabled = false;
        }
    });
});

const passwordGenerator = document.querySelector('[data-password-generator]');

if (passwordGenerator && !passwordGenerator.dataset.initialized) {
    passwordGenerator.dataset.initialized = 'true';

    const generatedPassword = passwordGenerator.querySelector('#generated-password');
    const copyButton = passwordGenerator.querySelector('[data-copy-generated-password]');
    const generateButton = passwordGenerator.querySelector('[data-generate-password]');
    const lengthInput = passwordGenerator.querySelector('#password-length');
    const lengthValue = passwordGenerator.querySelector('[data-password-length-value]');
    const status = passwordGenerator.querySelector('[data-password-generator-status]');
    const characterInputs = [...passwordGenerator.querySelectorAll('[data-password-character]')];
    const characterSets = {
        uppercase: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        lowercase: 'abcdefghijklmnopqrstuvwxyz',
        numbers: '0123456789',
        symbols: '!@#$%^&*()-_=+[]{}:,.?',
    };
    let currentPassword = '';

    const showGeneratorStatus = (message, isError = false) => {
        if (!status) {
            return;
        }

        status.textContent = message;
        status.classList.toggle('text-red-600', isError);
        status.classList.toggle('text-stone-500', !isError);
    };

    const secureRandomIndex = (maxExclusive) => {
        if (!window.crypto?.getRandomValues || !Number.isSafeInteger(maxExclusive) || maxExclusive < 1) {
            throw new Error('secure random unavailable');
        }

        const limit = Math.floor(0x100000000 / maxExclusive) * maxExclusive;
        const randomValue = new Uint32Array(1);

        do {
            window.crypto.getRandomValues(randomValue);
        } while (randomValue[0] >= limit);

        return randomValue[0] % maxExclusive;
    };

    const generateSecurePassword = (length, selectedSets) => {
        const characters = selectedSets.map(({ value }) => value);
        const passwordCharacters = selectedSets.map(({ value }) => value[secureRandomIndex(value.length)]);
        const allCharacters = characters.join('');

        while (passwordCharacters.length < length) {
            passwordCharacters.push(allCharacters[secureRandomIndex(allCharacters.length)]);
        }

        for (let index = passwordCharacters.length - 1; index > 0; index -= 1) {
            const swapIndex = secureRandomIndex(index + 1);
            [passwordCharacters[index], passwordCharacters[swapIndex]] = [passwordCharacters[swapIndex], passwordCharacters[index]];
        }

        return passwordCharacters.join('');
    };

    const updateLengthValue = () => {
        const length = Math.min(64, Math.max(8, Number.parseInt(lengthInput?.value ?? '16', 10) || 16));

        if (lengthInput) {
            lengthInput.value = String(length);
            lengthInput.setAttribute('aria-valuenow', String(length));
        }
        if (lengthValue) {
            lengthValue.textContent = `${length} characters`;
        }

        return length;
    };

    lengthInput?.addEventListener('input', updateLengthValue);

    generateButton?.addEventListener('click', () => {
        const length = updateLengthValue();
        const selectedSets = characterInputs
            .filter((input) => input.checked)
            .map((input) => ({ value: characterSets[input.dataset.passwordCharacter] }));

        if (selectedSets.length === 0) {
            currentPassword = '';
            if (generatedPassword) {
                generatedPassword.textContent = 'No password generated';
            }
            if (copyButton) {
                copyButton.disabled = true;
            }
            showGeneratorStatus('Select at least one character set.', true);
            return;
        }

        try {
            currentPassword = generateSecurePassword(length, selectedSets);
            if (generatedPassword) {
                generatedPassword.textContent = currentPassword;
            }
            if (copyButton) {
                copyButton.disabled = false;
            }
            showGeneratorStatus('New password generated.');
        } catch {
            currentPassword = '';
            if (generatedPassword) {
                generatedPassword.textContent = 'No password generated';
            }
            if (copyButton) {
                copyButton.disabled = true;
            }
            showGeneratorStatus('This browser does not support secure random generation.', true);
        }
    });

    copyButton?.addEventListener('click', async () => {
        if (!currentPassword || !navigator.clipboard?.writeText) {
            showGeneratorStatus('No password is available to copy.', true);
            return;
        }

        copyButton.disabled = true;
        try {
            await navigator.clipboard.writeText(currentPassword);
            showGeneratorStatus('Password copied.');
        } catch {
            showGeneratorStatus('Password could not be copied. Check browser clipboard permissions.', true);
        } finally {
            copyButton.disabled = false;
        }
    });
}
