<?php
/**
 * Copyright (c) 2016 AlexaCRM.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Lesser Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AlexaCRM\CRMToolkit\Entity;

class OptionSet {

    public $name;

    public $type;

    public $displayName;

    public $description;

    public $options;

    public $isGlobal;

    public function __construct( $optionSetNode ) {
        /* Determine the Name of the OptionSet */
        $this->name = (string) $optionSetNode->Name;

        $this->isGlobal = ( $optionSetNode->IsGlobal == 'true' );

        /* Determine the Type of the OptionSet */
        $this->type = (string) $optionSetNode->OptionSetType;

        $this->description = (string) $optionSetNode->Description->UserLocalizedLabel->Label;

        $this->displayName = (string) $optionSetNode->Description->UserLocalizedLabel->Label;

        /* Array to store the Options for this OptionSet */
        $optionSetValues = Array();

        switch ( $this->type ) {
            case 'Boolean':
                /* Parse the FalseOption */
                $value                     = (int) $optionSetNode->FalseOption->Value;
                $label                     = (String) $optionSetNode->FalseOption->Label->UserLocalizedLabel->Label[0];
                $optionSetValues[ $value ] = $label;
                /* Parse the TrueOption */
                $value                     = (int) $optionSetNode->TrueOption->Value;
                $label                     = (String) $optionSetNode->TrueOption->Label->UserLocalizedLabel->Label[0];
                $optionSetValues[ $value ] = $label;
                break;
            case 'State':
                foreach ( $optionSetNode->Options->OptionMetadata as $option ) {
                    /* Parse the Option */
                    $value = (int) $option->Value;
                    $label = (String) $option->Label->UserLocalizedLabel->Label[0];
                    /* Check for duplicated Values */
                    if ( array_key_exists( $value, $optionSetValues ) ) {
                        trigger_error( 'Option ' . $label . ' of OptionSet ' . $this->name . ' used by field ' . (String) $attribute->SchemaName . ' has the same Value as another Option in this Set', E_USER_WARNING );
                    } else {
                        /* Store the Option */
                        $optionSetValues[ $value ] = $label;
                    }
                }
                break;
            case 'Status':
                foreach ( $optionSetNode->Options->OptionMetadata as $option ) {
                    /* Parse the Option */
                    $value = (int) $option->Value;
                    $label = (String) $option->Label->UserLocalizedLabel->Label[0];
                    /* Check for duplicated Values */
                    if ( array_key_exists( $value, $optionSetValues ) ) {
                        trigger_error( 'Option ' . $label . ' of OptionSet ' . $this->name . ' used by field ' . (String) $attribute->SchemaName . ' has the same Value as another Option in this Set', E_USER_WARNING );
                    } else {
                        /* Store the Option */
                        $optionSetValues[ $value ] = $label;
                    }
                }
                break;
            case 'Picklist':
                /* Loop through the available Options */
                foreach ( $optionSetNode->Options->OptionMetadata as $option ) {
                    /* Parse the Option */
                    $value = (int) $option->Value;
                    $label = (String) $option->Label->UserLocalizedLabel->Label[0];
                    /* Check for duplicated Values */
                    if ( array_key_exists( $value, $optionSetValues ) ) {
                        trigger_error( 'Option ' . $label . ' of OptionSet ' . $this->name . ' used by field ' . (String) $attribute->SchemaName . ' has the same Value as another Option in this Set', E_USER_WARNING );
                    } else {
                        /* Store the Option */
                        $optionSetValues[ $value ] = $label;
                    }
                }
                break;
            default:
                //echo "DEFAULTOPTIONSET";
                /* If we're using Default, Warn user that the OptionSet handling is not defined */
                trigger_error( 'No OptionSet handling implemented for Type ' . $this->type . ' used by field ' . (String) $attribute->SchemaName . ' in Entity ' . $this->entityLogicalName, E_USER_WARNING );
        }

        $this->options = $optionSetValues;
    }

}
