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

namespace Eufony\Inflector\Utils;

use Eufony\Inflector\InflectorInterface;

/**
 * Provides a wrapper around an Inflector to specify hard-coded exceptions to
 * the underlying inflection implementations rules.
 */
class ExceptionAdapter implements InflectorInterface
{
    /**
     * The inflector object used internally to provide the real inflection
     * implementation.
     *
     * @var \Eufony\Inflector\InflectorInterface $inflector
     */
    protected InflectorInterface $inflector;

    /**
     * Stores exceptions to the rules of converting between different
     * capitalizations.
     *
     * Contains an array of string capitalizations for each exception.
     *
     * @var string[][] $cases
     */
    protected array $cases;

    /**
     * Stores exceptions to the pluralization and singularization rules.
     *
     * Contains a key-value pair for each word's singular and plural form.
     *
     * @var string[] $words
     */
    protected array $words;

    /**
     * Class constructor.
     * Wraps an inflection implementation to provide hard-coded exceptions to
     * its returned values.
     *
     * @param \Eufony\Inflector\InflectorInterface $inflector
     * @param string[][] $cases
     * @param string[] $words
     */
    public function __construct(InflectorInterface $inflector, array $cases = [], array $words = [])
    {
        $this->inflector = $inflector;
        $this->cases = $cases;
        $this->words = $words;
    }

    /**
     * Returns the internal inflection implementation.
     *
     * @return \Eufony\Inflector\InflectorInterface
     */
    public function inflector(): InflectorInterface
    {
        return $this->inflector;
    }

    /**
     * Returns the exceptions to the rules of converting between different
     * capitalizations.
     *
     * @return string[][]
     */
    public function cases(): array
    {
        return $this->cases;
    }

    /**
     * Returns the exceptions to the pluralization and singularization rules.
     *
     * @return string[]
     */
    public function words(): array
    {
        return $this->words;
    }

    /**
     * @inheritDoc
     */
    public function toPascalCase(string $string): string
    {
        return $this->checkForCaseExceptions($string, "pascal");
    }

    /**
     * @inheritDoc
     */
    public function toSnakeCase(string $string): string
    {
        return $this->checkForCaseExceptions($string, "snake");
    }

    /**
     * @inheritDoc
     */
    public function toCamelCase(string $string): string
    {
        return $this->checkForCaseExceptions($string, "camel");
    }

    /**
     * @inheritDoc
     */
    public function pluralize(string $string): string
    {
        $exceptions = $this->words;
        return $exceptions[$string] ?? $this->inflector->pluralize($string);
    }

    /**
     * @inheritDoc
     */
    public function singularize(string $string): string
    {
        $exceptions = array_flip($this->words);
        return $exceptions[$string] ?? $this->inflector->singularize($string);
    }

    /**
     * Checks if an exception for the given string is defined, and returns it
     * if it exists.
     * Otherwise, returns the result of the appropriate method for the given
     * target case of the underlying inflection implementation.
     *
     * `$targetCase` must be one of `pascal`, `snake`, or `camel`.
     *
     * @param string $string
     * @param string $targetCase
     * @return string
     */
    protected function checkForCaseExceptions(string $string, string $targetCase): string
    {
        $target_index = match ($targetCase) {
            "pascal" => 0,
            "snake" => 1,
            "camel" => 2,
        };
        $from_indexes = array_values(array_diff([0, 1, 2], [$target_index]));

        $ex1 = array_flip(array_map(fn($case) => $case[$from_indexes[0]], $this->cases));
        $ex2 = array_flip(array_map(fn($case) => $case[$from_indexes[1]], $this->cases));

        // Check for hard-coded exceptions
        if (isset($ex1[$string])) {
            return $this->cases[$ex1[$string]][$target_index];
        } elseif (isset($ex2[$string])) {
            return $this->cases[$ex2[$string]][$target_index];
        }

        // Default to underlying Inflector implementation
        $method = "to" . ucfirst($targetCase) . "Case";
        return $this->inflector->$method($string);
    }
}
