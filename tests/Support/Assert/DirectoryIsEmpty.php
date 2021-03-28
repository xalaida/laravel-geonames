<?php

namespace Nevadskiy\Geonames\Tests\Support\Assert;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Constraint\Constraint;
use function sprintf;

class DirectoryIsEmpty extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'directory is empty';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        $filesystem = new Filesystem();

        return $filesystem->exists($other) && ! $filesystem->files($other);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        return sprintf(
            'directory "%s" is not empty',
            $other
        );
    }
}
