/*
 * Regional Insights card on the market intelligence page: four state-level measures
 * (App\Support\RegionalInsights) on a clickable US map. Colours are quintiles of the
 * current measure; hovering or focusing a state previews it, clicking pins it.
 */

const SHADES = ['#dfe9e3', '#b4cfc1', '#7fae97', '#3f8064', '#1b604a'];

export default (data) => ({
    states: data.states,
    metrics: data.metrics,
    tab: 'population',
    hover: null,
    pinned: null,

    get metric() {
        return this.metrics[this.tab];
    },
    get active() {
        return this.hover ?? this.pinned;
    },
    get view() {
        const st = this.active ? this.states[this.active] : null;
        const m = st ? st[this.tab] : this.metric.us;
        return { name: st ? st.name : 'United States', value: m?.value ?? null, rows: m?.rows ?? [] };
    },
    get breaks() {
        const vals = Object.values(this.states)
            .map((s) => s[this.tab]?.value)
            .filter((v) => v !== null && v !== undefined)
            .sort((a, b) => a - b);
        return [0.2, 0.4, 0.6, 0.8].map((q) => vals[Math.floor(q * (vals.length - 1))]);
    },
    get legend() {
        const vals = Object.values(this.states).map((s) => s[this.tab]?.value).filter((v) => v !== null && v !== undefined);
        const lo = Math.min(...vals);
        const hi = Math.max(...vals);
        return [this.fmt(lo), this.fmt(hi)];
    },
    fill(abbr) {
        const v = this.states[abbr]?.[this.tab]?.value;
        if (v === null || v === undefined) return '#eef0ec';
        return SHADES[this.breaks.filter((b) => v > b).length];
    },
    fmt(v) {
        if (v === null || v === undefined) return '—';
        return this.metric.unit === 'pct' ? (v > 0 ? '+' : '') + v.toFixed(1) + '%' : v.toFixed(1);
    },
    setTab(key) {
        this.tab = key;
        this.hover = null;
    },
    toggle(abbr) {
        this.pinned = this.pinned === abbr ? null : abbr;
    },
    label(abbr) {
        const s = this.states[abbr];
        return s ? s.name + ': ' + this.fmt(s[this.tab]?.value) : abbr;
    },
});
