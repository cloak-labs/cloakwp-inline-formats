/**
 * Parse a CSS style attribute string into a property map.
 *
 * @param {string} [style]
 * @return {Record<string, string>}
 */
export function parseStyleAttribute(style = '') {
	const map = {};
	String(style)
		.split(';')
		.map((part) => part.trim())
		.filter(Boolean)
		.forEach((declaration) => {
			const colon = declaration.indexOf(':');
			if (colon === -1) {
				return;
			}
			const property = declaration.slice(0, colon).trim().toLowerCase();
			const value = declaration.slice(colon + 1).trim();
			if (property) {
				map[property] = value;
			}
		});
	return map;
}

/**
 * @param {string} property
 * @param {string} value
 * @return {string}
 */
export function buildStyleAttribute(property, value) {
	return `${property}: ${value}`;
}

/**
 * @param {string} [style]
 * @param {string} property
 * @return {string|undefined}
 */
export function getStyleProperty(style, property) {
	return parseStyleAttribute(style)[property];
}
