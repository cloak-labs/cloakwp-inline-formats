/**
 * Toggle control — toolbar (BlockControls) or overflow (RichTextToolbarButton).
 */

import {
	BlockControls,
	RichTextToolbarButton,
} from '@wordpress/block-editor';
import { ToolbarButton, ToolbarGroup } from '@wordpress/components';
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
	const { name, title, icon, blocks, control, placement = 'dropdown' } =
		formatConfig;
	const selectedBlock = useSelect((select) => {
		return select('core/block-editor').getSelectedBlock();
	}, []);

	if (Array.isArray(blocks) && blocks.length > 0) {
		if (!selectedBlock || !blocks.includes(selectedBlock.name)) {
			return null;
		}
	}

	const onClick = () => {
		onChange(
			toggleFormat(value, {
				type: name,
				attributes: {
					style: control.style,
				},
			})
		);
	};

	if (placement === 'toolbar') {
		return (
			<BlockControls group="inline">
				<ToolbarGroup>
					<ToolbarButton
						icon={icon || 'editor-code'}
						label={title}
						isPressed={isActive}
						onClick={onClick}
					/>
				</ToolbarGroup>
			</BlockControls>
		);
	}

	return (
		<RichTextToolbarButton
			icon={icon || 'editor-code'}
			title={title}
			isActive={isActive}
			onClick={onClick}
		/>
	);
}
