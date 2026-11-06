// Generates resources/data/us-map.json: projected US state outlines plus ILLUSTRATIVE opportunity points.
// Run with: node scripts/generate-us-map.mjs
// The points are sample data for the marketing page only; they are not real listings.
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { geoAlbersUsa, geoPath } from 'd3-geo';
import { feature, mesh } from 'topojson-client';

const topo = JSON.parse(readFileSync('node_modules/us-atlas/states-10m.json', 'utf8'));
const W = 960;
const H = 600;
const projection = geoAlbersUsa().scale(1280).translate([W / 2, H / 2]);
const path = geoPath(projection).digits(0);

const states = feature(topo, topo.objects.states);
const lower48 = { ...states, features: states.features.filter((f) => !['02', '15', '72'].includes(f.id)) };

const statesPath = path(lower48);
const bordersPath = path(mesh(topo, topo.objects.states, (a, b) => a !== b && ![a.id, b.id].some((id) => ['02', '15', '72'].includes(id))));

// [city, state, lon, lat, industry, intensity 1-3]
const cities = [
  ['Buffalo', 'NY', -78.88, 42.89, 'HVAC & Restoration', 3],
  ['Rochester', 'NY', -77.61, 43.16, 'Business Services', 2],
  ['Albany', 'NY', -73.76, 42.65, 'Auto Repair & Service', 2],
  ['Syracuse', 'NY', -76.15, 43.05, 'Manufacturing', 2],
  ['New York', 'NY', -74.0, 40.71, 'Business Services', 3],
  ['Boston', 'MA', -71.06, 42.36, 'Healthcare', 3],
  ['Hartford', 'CT', -72.68, 41.76, 'Manufacturing', 2],
  ['Philadelphia', 'PA', -75.16, 39.95, 'Healthcare', 3],
  ['Pittsburgh', 'PA', -79.99, 40.44, 'Manufacturing', 2],
  ['Baltimore', 'MD', -76.61, 39.29, 'HVAC & Restoration', 2],
  ['Washington', 'DC', -77.04, 38.9, 'Business Services', 2],
  ['Richmond', 'VA', -77.44, 37.54, 'Retail', 1],
  ['Charlotte', 'NC', -80.84, 35.23, 'Business Services', 3],
  ['Raleigh', 'NC', -78.64, 35.78, 'Healthcare', 2],
  ['Charleston', 'SC', -79.93, 32.78, 'Retail', 1],
  ['Atlanta', 'GA', -84.39, 33.75, 'HVAC & Restoration', 3],
  ['Jacksonville', 'FL', -81.66, 30.33, 'Auto Repair & Service', 2],
  ['Orlando', 'FL', -81.38, 28.54, 'Retail', 2],
  ['Tampa', 'FL', -82.46, 27.95, 'Healthcare', 3],
  ['Miami', 'FL', -80.19, 25.76, 'Business Services', 3],
  ['Nashville', 'TN', -86.78, 36.16, 'Healthcare', 2],
  ['Memphis', 'TN', -90.05, 35.15, 'Manufacturing', 1],
  ['Birmingham', 'AL', -86.8, 33.52, 'Manufacturing', 1],
  ['New Orleans', 'LA', -90.07, 29.95, 'Retail', 1],
  ['Louisville', 'KY', -85.76, 38.25, 'Manufacturing', 2],
  ['Columbus', 'OH', -82.99, 39.96, 'Business Services', 2],
  ['Cleveland', 'OH', -81.69, 41.5, 'Manufacturing', 2],
  ['Cincinnati', 'OH', -84.51, 39.1, 'HVAC & Restoration', 2],
  ['Detroit', 'MI', -83.05, 42.33, 'Manufacturing', 3],
  ['Grand Rapids', 'MI', -85.67, 42.96, 'Manufacturing', 2],
  ['Indianapolis', 'IN', -86.16, 39.77, 'Business Services', 2],
  ['Chicago', 'IL', -87.63, 41.88, 'Business Services', 3],
  ['Milwaukee', 'WI', -87.91, 43.04, 'Manufacturing', 2],
  ['Minneapolis', 'MN', -93.27, 44.98, 'Healthcare', 3],
  ['Des Moines', 'IA', -93.62, 41.59, 'Retail', 1],
  ['St. Louis', 'MO', -90.2, 38.63, 'Healthcare', 2],
  ['Kansas City', 'MO', -94.58, 39.1, 'HVAC & Restoration', 2],
  ['Omaha', 'NE', -95.93, 41.26, 'Business Services', 1],
  ['Oklahoma City', 'OK', -97.52, 35.47, 'Auto Repair & Service', 2],
  ['Dallas', 'TX', -96.8, 32.78, 'HVAC & Restoration', 3],
  ['Houston', 'TX', -95.37, 29.76, 'Manufacturing', 3],
  ['Austin', 'TX', -97.74, 30.27, 'Business Services', 3],
  ['San Antonio', 'TX', -98.49, 29.42, 'Auto Repair & Service', 2],
  ['El Paso', 'TX', -106.49, 31.76, 'Retail', 1],
  ['Denver', 'CO', -104.99, 39.74, 'HVAC & Restoration', 3],
  ['Salt Lake City', 'UT', -111.89, 40.76, 'Business Services', 2],
  ['Albuquerque', 'NM', -106.65, 35.08, 'Retail', 1],
  ['Phoenix', 'AZ', -112.07, 33.45, 'HVAC & Restoration', 3],
  ['Tucson', 'AZ', -110.97, 32.22, 'Healthcare', 1],
  ['Las Vegas', 'NV', -115.14, 36.17, 'Retail', 2],
  ['Boise', 'ID', -116.2, 43.62, 'Business Services', 1],
  ['Los Angeles', 'CA', -118.24, 34.05, 'Healthcare', 3],
  ['San Diego', 'CA', -117.16, 32.72, 'Business Services', 2],
  ['San Francisco', 'CA', -122.42, 37.77, 'Business Services', 2],
  ['Sacramento', 'CA', -121.49, 38.58, 'Manufacturing', 2],
  ['Portland', 'OR', -122.68, 45.52, 'Manufacturing', 2],
  ['Seattle', 'WA', -122.33, 47.61, 'Healthcare', 3],
  ['Spokane', 'WA', -117.43, 47.66, 'Retail', 1],
  ['Billings', 'MT', -108.5, 45.78, 'Retail', 1],
  ['Fargo', 'ND', -96.79, 46.88, 'Manufacturing', 1],
  ['Sioux Falls', 'SD', -96.73, 43.55, 'Healthcare', 1],
  ['Wichita', 'KS', -97.34, 37.69, 'Manufacturing', 1],
];

const points = cities
  .map(([city, state, lon, lat, industry, intensity], i) => {
    const p = projection([lon, lat]);
    if (!p) return null;
    return { id: i, city, state, industry, intensity, x: +p[0].toFixed(1), y: +p[1].toFixed(1) };
  })
  .filter(Boolean);

mkdirSync('resources/data', { recursive: true });
writeFileSync(
  'resources/data/us-map.json',
  JSON.stringify({ viewBox: `0 0 ${W} ${H}`, statesPath, bordersPath, points, illustrative: true }),
);
console.log(`us-map.json written: ${points.length} illustrative points, ${(statesPath.length / 1024).toFixed(0)} KB of state paths`);
