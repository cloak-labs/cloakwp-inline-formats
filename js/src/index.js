/**
 * Inline Formats — block editor entry.
 *
 * Config is localized from PHP as window.inlineFormatsEditor.formats.
 */

import { registerFormat } from './registerFormat';

const config =
	typeof window !== 'undefined' && window.inlineFormatsEditor
		? window.inlineFormatsEditor
		: { formats: [] };

const formats = Array.isArray(config.formats) ? config.formats : [];

formats.forEach((formatConfig) => {
	registerFormat(formatConfig);
});
