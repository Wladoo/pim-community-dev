<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Akeneo\Pim\Enrichment\Bundle\tests\Integration\Completeness\AttributeType\AbstractCompletenessPerAttributeTypeTestCase;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for textarea attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextareaAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteTextarea()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text_area',
            AttributeTypes::TEXTAREA
        );

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'foo bar',
                        ],
                    ],
                ],
            ]
        );

        $this->assertComplete($productComplete);
    }

    public function testNotCompleteTextarea()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text_area',
            AttributeTypes::TEXTAREA
        );

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productDataNull);
        $this->assertMissingAttributeForProduct($productDataNull, ['a_text_area']);

        $productDataEmptyString = $this->createProductWithStandardValues(
            $family,
            'product_data_empty_string',
            [
                'values' => [
                    'a_text_area' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '',
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productDataEmptyString);
        $this->assertMissingAttributeForProduct($productDataEmptyString, ['a_text_area']);

        $productWithoutValue = $this->createProductWithStandardValues($family, 'product_without_values');
        $this->assertNotComplete($productWithoutValue);
        $this->assertMissingAttributeForProduct($productWithoutValue, ['a_text_area']);
    }
}
