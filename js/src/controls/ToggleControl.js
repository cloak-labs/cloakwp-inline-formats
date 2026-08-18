/**
 * Toggle control for on/off formats with a fixed style declaration.
 */

import { RichTextToolbarButton } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { toggleFormat } from '@wordpress/rich-text';

/**
 * @param {Object} props
 * @param {Object} props.formatConfig
 * @param {boolean} props.isActive
 * @param {Object} props.value
 * @param {Function} props.onChange
 */
export default function ToggleControl({
	formatConfig,
	isActive,
	value,
	onChange,
}) {
	const { name, title, icon, blocks, control } = formatConfig;
	const selectedBlock = useSelect((select) => {
		return select('core/block-editor').getSelectedBlock();
	}, []);

	if (Array.isArray(blocks) && blocks.length > 0) {
		if (!selectedBlock || !blocks.includes(selectedBlock.name)) {
			return null;
		}
	}

	return (
		<RichTextToolbarButton
			icon={icon || 'editor-code'}
			title={title}
			isActive={isActive}
			onClick={() => {
				onChange(
					toggleFormat(value, {
						type: name,
						attributes: {
							style: control.style,
						},
					})
				);
			}}
		/>
	);
}
