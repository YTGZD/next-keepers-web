import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* Region-aware opportunity map (hero). Data is illustrative and rendered server-side. */
Alpine.data('usMap', (regions) => ({
    regions,
    region: null,
    hover: null,

    select(key) {
        this.region = this.region === key ? null : key;
        this.hover = null;
    },
    isActive(key) {
        return this.region === null || this.region === key;
    },
    get summary() {
        return this.region ? this.regions[this.region] : null;
    },
}));

/* Early-access form: posts as JSON when JS is available, falls back to a normal POST otherwise. */
Alpine.data('earlyAccess', (initial = {}) => ({
    interest: initial.interest ?? 'buyer',
    status: initial.status ?? 'idle', // idle | sending | success | error
    message: '',
    errors: {},

    pick(value) {
        this.interest = value;
    },

    async submit(event) {
        this.status = 'sending';
        this.errors = {};
        this.message = '';
        const form = event.target;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok) {
                this.status = 'success';
                form.reset();
                return;
            }
            this.status = 'error';
            if (response.status === 422) {
                this.errors = data.errors ?? {};
                this.message = 'Please check the highlighted fields.';
            } else if (response.status === 429) {
                this.message = 'Too many attempts. Please wait a minute and try again.';
            } else {
                this.message = 'Something went wrong on our side. Please try again shortly.';
            }
        } catch (e) {
            this.status = 'error';
            this.message = 'We could not reach the server. Check your connection and try again.';
        }
    },
}));

Alpine.start();

/* Scroll reveal (respects reduced motion via CSS; this just toggles the class). */
document.documentElement.classList.add('js');
const targets = document.querySelectorAll('[data-reveal], [data-key]');
if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -8% 0px' },
    );
    targets.forEach((el) => observer.observe(el));
} else {
    targets.forEach((el) => el.classList.add('is-visible'));
}
