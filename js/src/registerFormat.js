/**
 * Register a single format from PHP editor config.
 */

import {
	registerFormatType,
	unregisterFormatType,
} from '@wordpress/rich-text';
import { createElement } from '@wordpress/element';
import ChoiceControl from './controls/ChoiceControl';
import ToggleControl from './controls/ToggleControl';

/**
 * @param {Object} formatConfig
 */
export function registerFormat(formatConfig) {
	const { name, title, tagName, className, attributes, control, unregister } =
		formatConfig;

	if (!name || !control?.type) {
		return;
	}

	if (Array.isArray(unregister)) {
		unregister.forEach((formatName) => {
			try {
				unregisterFormatType(formatName);
			} catch (e) {
				// Format may not be registered yet / already gone.
			}
		});
	}

	registerFormatType(name, {
		title,
		tagName: tagName || 'span',
		className: className || null,
		attributes: attributes || { style: 'style' },
		edit(props) {
			if (control.type === 'choice') {
				return createElement(ChoiceControl, {
					...props,
					formatConfig,
				});
			}

			if (control.type === 'toggle') {
				return createElement(ToggleControl, {
					...props,
					formatConfig,
				});
			}

			return null;
		},
	});
}
