<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Core\Tests\Pattern;

use LastCall\Mannequin\Core\Pattern\PatternInterface;
use LastCall\Mannequin\Core\Pattern\PatternVariant;
use LastCall\Mannequin\Core\Pattern\TemplateFilePatternInterface;
use PHPUnit\Framework\TestCase;

abstract class PatternTestCase extends TestCase
{
    const PATTERN_ID = 'foo';

    const PATTERN_ALIASES = ['bar'];

    const TEMPLATE_FILE = '/foo/bar/baz';

    public function testGetId()
    {
        $this->assertEquals(static::PATTERN_ID, $this->getPattern()->getId());
    }

    abstract public function getPattern(): PatternInterface;

    public function testGetAliases()
    {
        $this->assertEquals(
            static::PATTERN_ALIASES,
            $this->getPattern()->getAliases()
        );
    }

    public function testGetSetName()
    {
        $pattern = $this->getPattern();
        $this->assertSame($pattern, $pattern->setName('Foobarbaz'));
        $this->assertEquals('Foobarbaz', $pattern->getName());
    }

    public function testPatternTagging()
    {
        $pattern = $this->getPattern();
        $this->assertEquals($pattern, $pattern->addMetadata('foo', 'bar'));
        $this->assertArraySubset(['foo' => 'bar'], $pattern->getMetadata());
        $this->assertTrue($pattern->hasMetadata('foo', 'bar'));
        $this->assertFalse($pattern->hasMetadata('foo', 'baz'));
        $pattern->addMetadata('foo', 'baz');
        $this->assertTrue($pattern->hasMetadata('foo', 'baz'));
    }

    public function testVariants()
    {
        $pattern = $this->getPattern();
        $pattern->createVariant('default', 'Default');
        $this->assertEquals([
            'default' => new PatternVariant('default', 'Default'),
        ], $pattern->getVariants());

        $pattern->createVariant('default', 'Overridden');
        $this->assertEquals([
            'default' => new PatternVariant('default', 'Overridden'),
        ], $pattern->getVariants());
    }

    public function testGetFile()
    {
        $pattern = $this->getPattern();
        if ($pattern instanceof TemplateFilePatternInterface) {
            $this->assertInstanceOf(\SplFileInfo::class, $pattern->getFile());
            $this->assertEquals(
                self::TEMPLATE_FILE,
                $pattern->getFile()->getPathname()
            );
        }
    }
}
