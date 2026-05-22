<?php

namespace App\Libs;

class Util
{
    public static function getFileType(?string $path): string
    {
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp' => 'image',
            'pdf' => 'pdf',
            default => 'file',
        };
    }

    /**
     * Build HTML <option> tags from an array.
     *
     * Usage:
     *   Util::makeHTMLOptions($items, $selectedValue);
     *   Util::makeHTMLOptions($items, $selectedValue, $valueKey, $labelKey);
     *
     * @param array $options
     * @param mixed $selected
     * @param mixed $valueKey
     * @param mixed $labelKey
     * @param mixed $unused
     * @return string
     */
    public static function makeHTMLOptions(array $options, $selected = null, $valueKey = null, $labelKey = null, $unused = null): string
    {
        $selected = is_array($selected) ? $selected : (string) $selected;
        $html = '';

        foreach ($options as $key => $value) {
            if (is_array($value) || is_object($value)) {
                if ($valueKey !== null && is_string($valueKey)) {
                    $optionValue = is_array($value)
                        ? ($value[$valueKey] ?? $key)
                        : ($value->{$valueKey} ?? $key);
                } else {
                    $optionValue = $key;
                }

                if ($labelKey !== null && is_string($labelKey)) {
                    $optionLabel = is_array($value)
                        ? ($value[$labelKey] ?? $optionValue)
                        : ($value->{$labelKey} ?? $optionValue);
                } else {
                    $optionLabel = is_array($value)
                        ? ($value['label'] ?? $optionValue)
                        : ($value->label ?? $optionValue);
                }
            } else {
                $optionValue = $key;
                $optionLabel = $value;
            }

            $optionValue = (string) $optionValue;
            $optionLabel = (string) $optionLabel;
            $selectedAttribute = ((is_array($selected) && in_array($optionValue, $selected, true)) || (!is_array($selected) && $optionValue === $selected))
                ? ' selected'
                : '';

            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                htmlspecialchars($optionValue, ENT_QUOTES, 'UTF-8'),
                $selectedAttribute,
                htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8')
            );
        }

        return $html;
    }
}
