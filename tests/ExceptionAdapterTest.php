<?php
/*
 * Eufony Inflector Utilities
 * Copyright (c) 2021 Alpin Gencer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Eufony\Inflector\Utils\Tests;

use Eufony\Inflector\InflectorInterface;
use Eufony\Inflector\NullInflector;
use Eufony\Inflector\Tests\AbstractInflectorTest;
use Eufony\Inflector\Utils\ExceptionAdapter;

/**
 * Unit tests for `\Eufony\Inflector\Utils\ExceptionAdapter`.
 */
class ExceptionAdapterTest extends AbstractInflectorTest
{
    /**
     * The internal inflection implementation used to test the `ExceptionAdapter`.
     *
     * @var \Eufony\Inflector\InflectorInterface $internalInflector
     */
    protected InflectorInterface $internalInflector;

    /**
     * Test cases for exceptions to changing between different capitalizations.
     *
     * @var string[][] $cases
     */
    protected array $cases = [
        ["foo", "bar", "baz"],
    ];

    /**
     * Test cases for exception to changing between plural and singular forms.
     *
     * @var string[] $words
     */
    protected array $words = [
        "foo" => "bar",
    ];

    /**
     * @inheritDoc
     */
    public function getInflector(): InflectorInterface
    {
        $this->internalInflector = new NullInflector();
        return new ExceptionAdapter($this->internalInflector, cases: $this->cases, words: $this->words);
    }

    /**
     * @inheritDoc
     */
    public function cases(): array
    {
        return $this->cases;
    }

    /**
     * @inheritDoc
     */
    public function words(): array
    {
        return array_map(fn($key, $value) => [$key, $value], array_keys($this->words), array_values($this->words));
    }

    public function testInflector()
    {
        $this->assertSame($this->internalInflector, $this->inflector->inflector());
    }

    public function testCases()
    {
        $this->assertEquals($this->cases, $this->inflector->cases());
    }

    public function testWords()
    {
        $this->assertEquals($this->words, $this->inflector->words());
    }
}
