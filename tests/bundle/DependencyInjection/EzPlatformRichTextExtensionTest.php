<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);
/**
 * File containing the EzPublishCoreExtensionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\Tests\EzPlatformRichTextBundle\DependencyInjection;

use EzSystems\EzPlatformRichTextBundle\DependencyInjection\EzPlatformRichTextExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Yaml\Yaml;

class EzPlatformRichTextExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new EzPlatformRichTextExtension();

        parent::setUp();
    }

    protected function getContainerExtensions()
    {
        return [$this->extension];
    }

    /**
     * Test RichText Semantic Configuration.
     */
    public function testRichTextConfiguration()
    {
        $config = Yaml::parse(
            file_get_contents(__DIR__ . '/Fixtures/FieldType/RichText/ezrichtext.yml')
        );
        $this->load($config);

        // Validate Custom Tags
        $this->assertTrue(
            $this->container->hasParameter($this->extension::RICHTEXT_CUSTOM_TAGS_PARAMETER)
        );
        $expectedCustomTagsConfig = [
            'video' => [
                'template' => 'MyBundle:FieldType/RichText/tag:video.html.twig',
                'icon' => '/bundles/mybundle/fieldtype/richtext/video.svg#video',
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'default_value' => 'abc',
                    ],
                    'width' => [
                        'type' => 'number',
                        'required' => true,
                        'default_value' => 360,
                    ],
                    'autoplay' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default_value' => null,
                    ],
                ],
            ],
            'equation' => [
                'template' => 'MyBundle:FieldType/RichText/tag:equation.html.twig',
                'icon' => '/bundles/mybundle/fieldtype/richtext/equation.svg#equation',
                'attributes' => [
                    'name' => [
                        'type' => 'string',
                        'required' => true,
                        'default_value' => 'Equation',
                    ],
                    'processor' => [
                        'type' => 'choice',
                        'required' => true,
                        'default_value' => 'latex',
                        'choices' => ['latex', 'tex'],
                    ],
                ],
            ],
        ];

        $this->assertSame(
            $expectedCustomTagsConfig,
            $this->container->getParameter($this->extension::RICHTEXT_CUSTOM_TAGS_PARAMETER)
        );
    }
}
