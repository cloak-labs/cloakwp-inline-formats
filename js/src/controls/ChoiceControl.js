/**
 * Choice format control — toolbar (BlockControls) or overflow (RichTextToolbarButton).
 */

import { useState } from '@wordpress/element';
import {
	BlockControls,
	RichTextToolbarButton,
} from '@wordpress/block-editor';
import {
	Popover,
	ToolbarDropdownMenu,
	ToolbarGroup,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { ChoiceMenuItems } from './ChoiceMenuItems';

/**
 * @param {Object} props
 */
export default function ChoiceControl(props) {
	const { formatConfig } = props;
	const { title, icon, blocks, placement = 'dropdown' } = formatConfig;
	const [isOpen, setIsOpen] = useState(false);

	const selectedBlock = useSelect((select) => {
		return select('core/block-editor').getSelectedBlock();
	}, []);

	if (Array.isArray(blocks) && blocks.length > 0) {
		if (!selectedBlock || !blocks.includes(selectedBlock.name)) {
			return null;
		}
	}

	const menuProps = {
		...props,
		onClose: () => setIsOpen(false),
	};

	if (placement === 'toolbar') {
		return (
			<BlockControls group="inline">
				<ToolbarGroup>
					<ToolbarDropdownMenu
						icon={icon || 'editor-bold'}
						label={title}
						toggleProps={{
							describedBy: title,
							isPressed: props.isActive,
						}}
					>
						{({ onClose }) => (
							<ChoiceMenuItems {...props} onClose={onClose} />
						)}
					</ToolbarDropdownMenu>
				</ToolbarGroup>
			</BlockControls>
		);
	}

	// Overflow / "More" formatting menu.
	return (
		<>
			<RichTextToolbarButton
				icon={icon || 'editor-bold'}
				title={title}
				isActive={props.isActive || isOpen}
				onClick={() => setIsOpen((open) => !open)}
			/>
			{isOpen && (
				<Popover
					placement="bottom-start"
					onClose={() => setIsOpen(false)}
				>
					<div style={{ padding: '4px 0', minWidth: '160px' }}>
						<ChoiceMenuItems {...menuProps} />
					</div>
				</Popover>
			)}
		</>
	);
}
