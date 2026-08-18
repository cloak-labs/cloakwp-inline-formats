/**
 * Dropdown control for choice formats (e.g. font-weight).
 */

import { MenuItem, ToolbarDropdownMenu } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { applyFormat, removeFormat } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';
import { buildStyleAttribute, getStyleProperty } from '../styleUtils';

/**
 * @param {Object} props
 * @param {Object} props.formatConfig
 * @param {boolean} props.isActive
 * @param {Object} props.activeAttributes
 * @param {Object} props.value
 * @param {Function} props.onChange
 */
export default function ChoiceControl({
	formatConfig,
	isActive,
	activeAttributes,
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

	const styleProperty = control.styleProperty;
	const options = control.options || [];
	const activeValue = isActive
		? getStyleProperty(activeAttributes?.style, styleProperty)
		: undefined;

	return (
		<ToolbarDropdownMenu icon={icon || 'editor-bold'} label={title}>
			{({ onClose }) => (
				<>
					<MenuItem
						role="menuitemradio"
						isSelected={!isActive}
						onClick={() => {
							onChange(removeFormat(value, name));
							onClose();
						}}
					>
						{__('Default', 'inline-formats')}
					</MenuItem>
					{options.map((option) => {
						const optionValue = String(option.value);
						const isOptionActive =
							isActive && activeValue === optionValue;
						const previewStyle =
							styleProperty === 'font-weight'
								? { fontWeight: optionValue }
								: undefined;

						return (
							<MenuItem
								key={optionValue}
								role="menuitemradio"
								isSelected={isOptionActive}
								onClick={() => {
									onChange(
										applyFormat(value, {
											type: name,
											attributes: {
												style: buildStyleAttribute(
													styleProperty,
													optionValue
												),
											},
										})
									);
									onClose();
								}}
								style={previewStyle}
							>
								{option.label || optionValue}
							</MenuItem>
						);
					})}
				</>
			)}
		</ToolbarDropdownMenu>
	);
}
