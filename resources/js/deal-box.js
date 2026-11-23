/*
 * Deal box panel (/buy-box, signed-in accounts). Sliders, multi-selects and
 * switches edit `criteria`; every change re-fetches the listing list (debounced).
 * Saving stores a named box and makes it the account's active buy box.
 */

const range = (from, to, step) => {
    const out = [];
    for (let v = from; v <= to + 1e-9; v += step) out.push(Math.round(v * 100) / 100);
    return out;
};

const money = (v) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(v);

// Each slider maps a position (0..n) to a stored value; null means "no limit" / "any".
const SLIDERS = {
    price: {
        field: 'max_price_ex_re',
        stops: [...range(50000, 1000000, 50000), ...range(1100000, 2000000, 100000), ...range(2250000, 5000000, 250000), ...range(5500000, 10000000, 500000), null],
        text: (v) => (v === null ? 'No limit' : `Up to ${money(v)}`),
    },
    sde: {
        field: 'min_earnings',
        stops: [null, ...range(25000, 750000, 25000)],
        text: (v) => (v === null ? 'Any' : `At least ${money(v)}`),
    },
    multiple: {
        field: 'max_multiple',
        stops: [...range(0.5, 10, 0.25), null],
        text: (v) => (v === null ? 'No limit' : `Up to ${v.toFixed(2).replace(/\.?0+$/, '')}×`),
    },
    years: {
        field: 'min_years_in_business',
        stops: [null, ...range(1, 50, 1)],
        text: (v) => (v === null ? 'Any' : `At least ${v} ${v === 1 ? 'year' : 'years'}`),
    },
};

// Closest stop to a stored value (stored values may come from older forms).
const positionFor = (stops, value) => {
    if (value === null || value === undefined || value === '') {
        const i = stops.indexOf(null);
        return i === -1 ? 0 : i;
    }
    let best = 0;
    stops.forEach((s, i) => {
        if (s !== null && Math.abs(s - value) < Math.abs((stops[best] ?? Infinity) - value)) best = i;
    });
    return best;
};

const clone = (o) => JSON.parse(JSON.stringify(o));

export default (config) => ({
    criteria: clone(config.criteria),
    defaults: config.defaults,
    boxes: config.boxes,
    options: config.options,
    urls: config.urls,
    sliders: SLIDERS,
    positions: {},
    selectedId: '',
    name: '',
    notice: '',
    error: false,
    busy: false,
    loading: false,
    timer: null,
    request: 0,

    init() {
        this.syncPositions();
        // Start on the saved box that matches the active criteria, if any.
        const current = JSON.stringify(this.normalised(this.criteria));
        const match = this.boxes.find((b) => JSON.stringify(this.normalised(b.criteria)) === current);
        if (match) {
            this.selectedId = String(match.id);
            this.name = match.name;
        }

        Object.keys(SLIDERS).forEach((key) => {
            this.$watch(`positions.${key}`, (pos) => {
                this.criteria[SLIDERS[key].field] = SLIDERS[key].stops[pos] ?? null;
            });
        });
        this.$watch('criteria', () => this.refresh());
    },

    syncPositions() {
        Object.entries(SLIDERS).forEach(([key, s]) => {
            this.positions[key] = positionFor(s.stops, this.criteria[s.field]);
        });
    },

    sliderText(key) {
        const s = SLIDERS[key];
        return s.text(s.stops[this.positions[key]] ?? null);
    },

    get industryOptions() {
        return this.options.industries.map((v) => ({ value: v, label: v }));
    },
    get stateOptions() {
        return this.options.states.map((v) => ({ value: v, label: v }));
    },
    get sourceOptions() {
        return this.options.sources.map((v) => ({ value: v, label: v }));
    },
    // Counties in the chosen states (all counties when no state is chosen), plus any already selected.
    get countyOptions() {
        const states = this.criteria.states;
        const seen = new Set();
        return this.options.counties
            .filter((c) => states.length === 0 || c.state === null || states.includes(c.state) || this.criteria.counties.includes(c.county))
            .filter((c) => (seen.has(c.county) ? false : seen.add(c.county)))
            .map((c) => ({ value: c.county, label: c.state ? `${c.county} (${c.state})` : c.county }));
    },

    summary(field, all) {
        const picked = this.criteria[field] || [];
        if (picked.length === 0) return all;
        if (picked.length <= 2) return picked.join(', ');
        return `${picked.length} selected`;
    },

    normalised(c) {
        const out = {};
        Object.keys(this.defaults).forEach((k) => {
            const v = c[k] ?? this.defaults[k];
            out[k] = Array.isArray(v) ? [...v].sort() : v === '' ? null : v;
        });
        return out;
    },

    params() {
        const p = new URLSearchParams();
        Object.entries(this.normalised(this.criteria)).forEach(([k, v]) => {
            if (Array.isArray(v)) v.forEach((x) => p.append(`${k}[]`, x));
            else if (typeof v === 'boolean') p.append(k, v ? '1' : '0');
            else if (v !== null) p.append(k, v);
        });
        return p;
    },

    refresh() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.load(), 250);
    },

    async load() {
        const id = ++this.request;
        this.loading = true;
        try {
            const response = await fetch(`${this.urls.results}?${this.params()}`, { headers: { Accept: 'application/json' } });
            if (id !== this.request) return; // a newer request is on its way
            if (!response.ok) throw new Error();
            const data = await response.json();
            this.$refs.results.innerHTML = data.html;
        } catch {
            if (id === this.request) this.say("Couldn't update the list. Check your connection and try again.", true);
        } finally {
            if (id === this.request) this.loading = false;
        }
    },

    say(text, error = false) {
        this.notice = text;
        this.error = error;
    },

    loadBox() {
        const box = this.boxes.find((b) => String(b.id) === this.selectedId);
        this.criteria = clone(box ? box.criteria : this.defaults);
        this.name = box ? box.name : '';
        this.syncPositions();
        this.say('');
    },

    reset() {
        this.criteria = clone(this.defaults);
        this.syncPositions();
        this.say('Back to the default box. Save to keep it.');
    },

    async send(url, method, body) {
        this.busy = true;
        try {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const first = data.errors ? Object.values(data.errors)[0][0] : data.message;
                this.say(first || 'Something went wrong. Please try again.', true);
                return null;
            }
            this.boxes = data.boxes;
            this.say(data.message);
            return data;
        } finally {
            this.busy = false;
        }
    },

    async save() {
        const name = this.name.trim();
        if (!name) {
            this.say('Give the box a name first.', true);
            return;
        }
        const data = await this.send(this.urls.save, 'POST', { ...this.normalised(this.criteria), name });
        if (data) this.selectedId = String(data.id);
    },

    async remove() {
        if (!this.selectedId) return;
        const data = await this.send(`${this.urls.delete}/${this.selectedId}`, 'DELETE');
        if (data) {
            this.selectedId = '';
            this.name = '';
        }
    },
});
