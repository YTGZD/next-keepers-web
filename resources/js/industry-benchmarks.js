/*
 * Industry benchmarks on the market intelligence page.
 * Data comes from App\Support\IndustryBenchmarks (BizBuySell Insight, businesses sold nationally).
 * The chart is plain SVG with a fixed number of points (one per year), so no chart library is needed.
 */

const W = 640;
const H = 240;
const PAD = { l: 44, r: 16, t: 14, b: 28 };
const TICKS = 5;

const usd = (v) => {
    if (v === null || v === undefined) return '—';
    if (v >= 1e6) return '$' + (v / 1e6).toFixed(v >= 1e7 ? 1 : 2).replace(/\.?0+$/, '') + 'M';
    if (v >= 1e3) return '$' + Math.round(v / 1e3) + 'K';
    return '$' + Math.round(v);
};
const x2 = (v) => (v === null || v === undefined ? '—' : v.toFixed(2) + '×');
const pct = (v) => (v === null || v === undefined ? '—' : Math.round(v * 100) + '%');
const int = (v) => (v === null || v === undefined ? '—' : Number(v).toLocaleString('en-US'));
const chg = (v) => (v === null || v === undefined ? '—' : (v > 0 ? '+' : '') + Math.round(v) + '%');

export default (data) => ({
    years: data.years,
    rows: data.rows,
    median: data.median,
    sectors: data.sectors,
    thin: data.thin,

    industry: data.defaultIndustry,
    pick: data.defaultIndustry ?? '',
    pickMsg: '',
    hover: null,

    q: '',
    sector: '',
    sortKey: 'sales',
    sortDir: -1,

    usd,
    x2,
    pct,
    int,
    chg,

    get current() {
        return this.rows.find((r) => r.subsector === this.industry) ?? null;
    },

    choose(name, scroll = false) {
        const row = this.rows.find((r) => r.subsector === name);
        if (!row) return;
        this.industry = row.subsector;
        this.pick = row.subsector;
        this.pickMsg = '';
        this.hover = null;
        if (scroll) {
            this.$nextTick(() => document.getElementById('benchmark-chart')?.scrollIntoView({ block: 'start' }));
        }
    },

    choosePick() {
        const v = this.pick.trim().toLowerCase();
        if (!v) return;
        const exact = this.rows.find((r) => r.subsector.toLowerCase() === v);
        const hits = exact ? [exact] : this.rows.filter((r) => (r.subsector + ' ' + r.sector).toLowerCase().includes(v));
        if (hits.length === 1) this.choose(hits[0].subsector);
        else this.pickMsg = hits.length ? hits.length + ' matches: pick one from the list' : 'No industry matches';
    },

    /* ---- chart ---- */
    get domain() {
        const vals = [...(this.current?.trend ?? []), ...this.median].filter((v) => v !== null);
        if (!vals.length) return [0, 1];
        // Four even steps of 0.25, 0.5 or 1 so the axis labels stay round.
        const lo = Math.max(0, Math.floor(Math.min(...vals) * 2) / 2);
        const max = Math.max(...vals);
        const step = [0.25, 0.5, 1, 2, 5].find((st) => lo + (TICKS - 1) * st >= max) ?? Math.ceil((max - lo) / (TICKS - 1));
        return [lo, lo + (TICKS - 1) * step];
    },
    ticks() {
        const [lo, hi] = this.domain;
        return Array.from({ length: TICKS }, (_, i) => lo + ((hi - lo) * i) / (TICKS - 1));
    },
    px(i) {
        return PAD.l + (i * (W - PAD.l - PAD.r)) / (this.years.length - 1);
    },
    py(v) {
        const [lo, hi] = this.domain;
        return PAD.t + (1 - (v - lo) / (hi - lo)) * (H - PAD.t - PAD.b);
    },
    path(values) {
        let d = '';
        let pen = false;
        values.forEach((v, i) => {
            if (v === null) {
                pen = false;
                return;
            }
            d += (pen ? 'L' : 'M') + this.px(i).toFixed(1) + ' ' + this.py(v).toFixed(1);
            pen = true;
        });
        return d;
    },
    get line() {
        return this.path(this.current?.trend ?? []);
    },
    get medianLine() {
        return this.path(this.median);
    },
    point(i) {
        return this.current?.trend?.[i] ?? null;
    },
    get readoutIndex() {
        if (this.hover !== null) return this.hover;
        const t = this.current?.trend ?? [];
        for (let i = t.length - 1; i >= 0; i--) if (t[i] !== null) return i;
        return this.years.length - 1;
    },
    get readout() {
        const i = this.readoutIndex;
        const v = this.point(i);
        const sold = this.current?.sold?.[i];
        return {
            year: this.years[i],
            value: v,
            sold,
            thin: sold !== null && sold !== undefined && sold < this.thin,
            median: this.median[i],
        };
    },
    tickLabel(v) {
        return (Number.isInteger(v * 2) ? v.toFixed(1) : v.toFixed(2)) + '×';
    },
    get chartLabel() {
        const r = this.current;
        if (!r) return 'No industry selected';
        const pts = this.years.map((y, i) => (r.trend[i] === null ? null : y + ' ' + x2(r.trend[i]))).filter(Boolean);
        return r.subsector + ', average cash flow multiple by year: ' + pts.join(', ');
    },

    /* ---- sector bars ---- */
    get sectorMax() {
        return Math.max(...this.sectors.map((s) => s.cfx));
    },
    barWidth(s) {
        return Math.max(4, (s.cfx / this.sectorMax) * 100) + '%';
    },
    filterSector(name) {
        this.sector = this.sector === name ? '' : name;
    },

    /* ---- table ---- */
    get sectorNames() {
        return [...new Set(this.rows.map((r) => r.sector))].sort();
    },
    sortBy(key) {
        if (this.sortKey === key) this.sortDir *= -1;
        else {
            this.sortKey = key;
            this.sortDir = key === 'subsector' || key === 'sector' ? 1 : -1;
        }
    },
    ariaSort(key) {
        if (this.sortKey !== key) return 'none';
        return this.sortDir > 0 ? 'ascending' : 'descending';
    },
    get table() {
        const q = this.q.trim().toLowerCase();
        const k = this.sortKey;
        const d = this.sortDir;
        return this.rows
            .filter((r) => (!this.sector || r.sector === this.sector) && (!q || (r.subsector + ' ' + r.sector).toLowerCase().includes(q)))
            .slice()
            .sort((a, b) => {
                const av = a[k];
                const bv = b[k];
                if (av === null && bv === null) return 0;
                if (av === null) return 1;
                if (bv === null) return -1;
                return (typeof av === 'string' ? av.localeCompare(bv) : av - bv) * d;
            });
    },
});
