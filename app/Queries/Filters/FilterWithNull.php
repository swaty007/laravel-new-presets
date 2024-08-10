<?php

namespace App\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * @template TModelClass of Model
 * @template-implements \Spatie\QueryBuilder\Filters\Filter<TModelClass>
 */
class FilterWithNull implements Filter
{
    protected array $relationConstraints = [];

    /** @var bool */
    protected bool $addRelationConstraint = true;

    public function __construct(bool $addRelationConstraint = true)
    {
        $this->addRelationConstraint = $addRelationConstraint;
    }

    /** {@inheritdoc} */
    public function __invoke(Builder $query, $value, string $property)
    {
        if ($this->addRelationConstraint) {
            if ($this->isRelationProperty($query, $property)) {
                $this->withRelationConstraint($query, $value, $property);

                return;
            }
        }

        if (is_array($value)) {
            if (count($value) === 1 && $value[0] === 'null') {
                $query->whereNull($query->qualifyColumn($property));
                return;
            }

            $query->whereIn($query->qualifyColumn($property), $value);

            return;
        }

        $query->where($query->qualifyColumn($property), '=', $value);
    }

    protected function isRelationProperty(Builder $query, string $property): bool
    {
        if (! Str::contains($property, '.')) {
            return false;
        }

        if (in_array($property, $this->relationConstraints)) {
            return false;
        }

        $firstRelationship = explode('.', $property)[0];

        if (! method_exists($query->getModel(), $firstRelationship)) {
            return false;
        }

        return is_a($query->getModel()->{$firstRelationship}(), Relation::class);
    }

    /**
     * @param Builder $query
     * @param $value
     * @param string $property
     * @return void
     */
    protected function withRelationConstraint(Builder $query, $value, string $property)
    {
        [$relation, $property] = collect(explode('.', $property))
            ->pipe(function (Collection $parts) {
                return [
                    $parts->except(count($parts) - 1)->implode('.'),
                    $parts->last(),
                ];
            });

        if (is_array($value)) {
            if (count($value) === 1 && $value[0] === 'null') {
                $query->whereDoesntHave($relation);
                return;
            }
        }

        $query->whereHas($relation, function (Builder $query) use ($value, $property) {
            $this->relationConstraints[] = $property = $query->qualifyColumn($property);

            $this->__invoke($query, $value, $property);
        });
    }
}
