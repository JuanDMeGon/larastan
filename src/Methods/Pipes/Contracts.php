<?php

declare(strict_types=1);

/**
 * This file is part of Larastan.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\Larastan\Methods\Pipes;

use Closure;
use function get_class;
use NunoMaduro\Larastan\Concerns;
use NunoMaduro\Larastan\Methods\Passable;
use PHPStan\Reflection\ClassReflection;

/**
 * @internal
 */
final class Contracts
{
    use Concerns\HasContainer;

    /**
     * @param \NunoMaduro\Larastan\Methods\Passable $passable
     * @param \Closure $next
     *
     * @return void
     */
    public function handle(Passable $passable, Closure $next): void
    {
        $found = false;

        foreach ($this->concretes($passable->getClassReflection()) as $concrete) {
            if ($found = $passable->sendToPipeline($concrete)) {
                break;
            }
        }

        if (! $found) {
            $next($passable);
        }
    }

    /**
     * @param \PHPStan\Reflection\ClassReflection $classReflection
     *
     * @return array
     */
    private function concretes(ClassReflection $classReflection): array
    {
        if ($classReflection->isInterface()) {
            $concrete = $this->resolve($classReflection->getName());

            if ($concrete !== null) {
                return [get_class($concrete)];
            }
        }

        return [];
    }
}