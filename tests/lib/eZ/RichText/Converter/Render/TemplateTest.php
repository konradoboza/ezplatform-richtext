<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);
/**
 * File containing the RichText Template Render converter test.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\Tests\EzPlatformRichText\eZ\RichText\Converter\Render;

use PHPUnit\Framework\TestCase;
use EzSystems\EzPlatformRichText\eZ\RichText\Converter\Render\Template;
use EzSystems\EzPlatformRichText\eZ\RichText\RendererInterface;
use DOMDocument;

class TemplateTest extends TestCase
{
    public function setUp()
    {
        $this->rendererMock = $this->getRendererMock();
        parent::setUp();
    }

    public function providerForTestConvert()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template1"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template1">
    <ezpayload><![CDATA[template1]]></ezpayload>
  </eztemplate>
</section>',
                [
                    [
                        'name' => 'template1',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'template1',
                            'params' => [],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template2">
    <ezcontent>content2</ezcontent>
    <ezconfig>
      <ezvalue key="size">medium</ezvalue>
      <ezvalue key="offset">10</ezvalue>
      <ezvalue key="limit">5</ezvalue>
      <ezvalue key="hey">
          <ezvalue key="look">
              <ezvalue key="at">
                  <ezvalue key="this">wohoo</ezvalue>
                  <ezvalue key="that">weeee</ezvalue>
              </ezvalue>
          </ezvalue>
          <ezvalue key="what">get to the chopper</ezvalue>
      </ezvalue>
    </ezconfig>
  </eztemplate>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template2">
    <ezcontent>content2</ezcontent>
    <ezconfig>
      <ezvalue key="size">medium</ezvalue>
      <ezvalue key="offset">10</ezvalue>
      <ezvalue key="limit">5</ezvalue>
      <ezvalue key="hey">
          <ezvalue key="look">
              <ezvalue key="at">
                  <ezvalue key="this">wohoo</ezvalue>
                  <ezvalue key="that">weeee</ezvalue>
              </ezvalue>
          </ezvalue>
          <ezvalue key="what">get to the chopper</ezvalue>
      </ezvalue>
    </ezconfig>
    <ezpayload><![CDATA[template2]]></ezpayload>
  </eztemplate>
</section>',
                [
                    [
                        'name' => 'template2',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'template2',
                            'content' => 'content2',
                            'params' => [
                                'size' => 'medium',
                                'offset' => 10,
                                'limit' => 5,
                                'hey' => [
                                    'look' => [
                                        'at' => [
                                            'this' => 'wohoo',
                                            'that' => 'weeee',
                                        ],
                                    ],
                                    'what' => 'get to the chopper',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template3"/>
  <eztemplateinline name="template4"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template3">
    <ezpayload><![CDATA[template3]]></ezpayload>
  </eztemplate>
  <eztemplateinline name="template4">
    <ezpayload><![CDATA[template4]]></ezpayload>
  </eztemplateinline>
</section>',
                [
                    [
                        'name' => 'template3',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'template3',
                            'params' => [],
                        ],
                    ],
                    [
                        'name' => 'template4',
                        'is_inline' => true,
                        'params' => [
                            'name' => 'template4',
                            'params' => [],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template5"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template5">
    <ezpayload><![CDATA[template5]]></ezpayload>
  </eztemplate>
</section>',
                [
                    [
                        'name' => 'template5',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'template5',
                            'params' => [],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplateinline name="template6"/>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplateinline name="template6">
    <ezpayload><![CDATA[template6]]></ezpayload>
  </eztemplateinline>
</section>',
                [
                    [
                        'name' => 'template6',
                        'is_inline' => true,
                        'params' => [
                            'name' => 'template6',
                            'params' => [],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template7">
    <ezcontent>content7<eztemplate name="template8">
    <ezcontent>content8</ezcontent>
  </eztemplate></ezcontent>
  </eztemplate>
</section>',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook">
  <eztemplate name="template7">
    <ezcontent>content7template8</ezcontent>
    <ezpayload><![CDATA[template7]]></ezpayload>
  </eztemplate>
</section>',
                [
                    [
                        'name' => 'template8',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'template8',
                            'content' => 'content8',
                            'params' => [],
                        ],
                    ],
                    [
                        'name' => 'template7',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'template7',
                            'content' => 'content7template8',
                            'params' => [],
                        ],
                    ],
                ],
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <eztemplate name="custom_tag" ezxhtml:align="right">
    <ezcontent><para>Param: value</para></ezcontent>
    <ezconfig>
        <ezvalue key="param">value</ezvalue>
    </ezconfig>
  </eztemplate>
</section>',
                '<?xml version="1.0"?>
 <section xmlns="http://docbook.org/ns/docbook" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml">
  <eztemplate name="custom_tag" ezxhtml:align="right">
    <ezcontent>
      <para>param: value</para>
    </ezcontent>
    <ezconfig>
      <ezvalue key="param">value</ezvalue>
    </ezconfig>
    <ezpayload>custom_tag</ezpayload>
  </eztemplate>
</section>',
                [
                    [
                        'name' => 'custom_tag',
                        'is_inline' => false,
                        'params' => [
                            'name' => 'custom_tag',
                            'content' => '<para>Param: value</para>',
                            'params' => ['param' => 'value'],
                            'align' => 'right',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestConvert
     */
    public function testConvert($xmlString, $expectedXmlString, array $renderParams)
    {
        $this->rendererMock->expects($this->never())->method('renderContentEmbed');
        $this->rendererMock->expects($this->never())->method('renderLocationEmbed');

        if (!empty($renderParams)) {
            foreach ($renderParams as $index => $params) {
                $this->rendererMock
                    ->expects($this->at($index))
                    ->method('renderTag')
                    ->with(
                        $params['name'],
                        $params['params'],
                        $params['is_inline']
                    )
                    ->will($this->returnValue($params['name']));
            }
        } else {
            $this->rendererMock->expects($this->never())->method('renderTag');
        }

        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        $document->loadXML($xmlString);

        $document = $this->getConverter()->convert($document);

        $expectedDocument = new DOMDocument();
        $expectedDocument->preserveWhiteSpace = false;
        $expectedDocument->formatOutput = false;
        $expectedDocument->loadXML($expectedXmlString);

        $this->assertEquals($expectedDocument, $document);
    }

    protected function getConverter()
    {
        return new Template($this->rendererMock);
    }

    /**
     * @var \EzSystems\EzPlatformRichText\eZ\FieldType\RichText\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rendererMock;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRendererMock()
    {
        return $this->createMock(RendererInterface::class);
    }
}
