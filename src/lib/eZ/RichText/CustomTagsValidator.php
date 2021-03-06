<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformRichText\eZ\RichText;

use DOMDocument;
use DOMXPath;

/**
 * Validator for Custom Tags input.
 *
 * The Validator checks if the given XML reflects proper Custom Tags configuration,
 * mostly existence of specific Custom Tag and its required attributes.
 */
class CustomTagsValidator
{
    /**
     * Custom Tags global configuration (ezpublish.ezrichtext.custom_tags Semantic Config).
     *
     * @var array
     */
    private $customTagsConfiguration;

    /**
     * @param array $customTagsConfiguration Injectable using %ezplatform.ezrichtext.custom_tags% DI Container parameter.
     */
    public function __construct(array $customTagsConfiguration)
    {
        $this->customTagsConfiguration = $customTagsConfiguration;
    }

    /**
     * Validate Custom Tags found in the document.
     *
     * @param \DOMDocument $xmlDocument
     *
     * @return string[] an array of error messages
     */
    public function validateDocument(DOMDocument $xmlDocument)
    {
        $errors = [];

        $xpath = new DOMXPath($xmlDocument);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');

        foreach ($xpath->query('//docbook:eztemplate') as $tagElement) {
            $tagName = $tagElement->getAttribute('name');
            if (empty($tagName)) {
                $errors[] = 'Missing RichText Custom Tag name';
                continue;
            }

            if (!isset($this->customTagsConfiguration[$tagName])) {
                $errors[] = "Unknown RichText Custom Tag '{$tagName}'";
                continue;
            }

            $nonEmptyAttributes = [];
            $tagAttributes = $this->customTagsConfiguration[$tagName]['attributes'];

            // iterate over all attributes defined in XML document to check if their names match configuration
            $configElements = $xpath->query('.//docbook:ezconfig/docbook:ezvalue', $tagElement);
            foreach ($configElements as $configElement) {
                $attributeName = $configElement->getAttribute('key');
                if (empty($attributeName)) {
                    $errors[] = "Missing attribute name for RichText Custom Tag '{$tagName}'";
                    continue;
                }
                if (!isset($tagAttributes[$attributeName])) {
                    $errors[] = "Unknown attribute '{$attributeName}' of RichText Custom Tag '{$tagName}'";
                }

                // collect information about non-empty attributes
                if (!empty($configElement->textContent)) {
                    $nonEmptyAttributes[] = $attributeName;
                }
            }

            // check if all required attributes are present
            foreach ($tagAttributes as $attributeName => $attributeSettings) {
                if (empty($attributeSettings['required'])) {
                    continue;
                }

                if (!in_array($attributeName, $nonEmptyAttributes)) {
                    $errors[] = "The attribute '{$attributeName}' of RichText Custom Tag '{$tagName}' cannot be empty";
                }
            }
        }

        return $errors;
    }
}
