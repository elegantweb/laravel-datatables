<?php

namespace Elegant\DataTables\Engines;

use RuntimeException;
use Elegant\DataTables\Contracts\Engine;
use Elegant\DataTables\Engines\Concerns\InteractsWithQueryBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class EloquentEngine implements Engine
{
    use InteractsWithQueryBuilder {
        search as traitSearch;
        order as traitOrder;
    }

    /**
     * @param Builder $source
     */
    public function __construct($source)
    {
        $this->original = $source;
        $this->source = clone $source;
    }

    /**
     * @inheritdoc
     */
    public function select(array $columns)
    {
        $this->source->addSelect($this->qualifyColumn($this->source, '*'));
    }

    /**
     * @inheritdoc
     */
    protected function qualifyColumn($query, $name)
    {
        return $query->qualifyColumn($name);
    }

    /**
     * Qualify the given column name by the pivot table.
     *
     * @param mixed $query
     * @param string $name Column name
     * @return string
     */
    protected function qualifyPivotColumn($name)
    {
        return $this->source->qualifyPivotColumn($name);
    }

    /**
     * @inheritdoc
     */
    protected function search($query, $name, $value, $regex, $boolean = 'or')
    {
        if ($this->isPivot($name)) {
            $this->searchPivot($query, $name, $value, $regex, $boolean);
        } elseif ($this->isRelated($query->getModel(), $name)) {
            $this->searchRelated($query, $name, $value, $regex, $boolean);
        } else {
            $this->traitSearch($query, $name, $value, $regex, $boolean);
        }
    }

    /**
     * Searches for the related column.
     *
     * @param mixed  $query
     * @param string $name Column name
     * @param string $value
     * @param bool   $regex
     * @param string $boolean
     */
    protected function searchRelated($query, $name, $value, $regex, $boolean = 'or')
    {
        list($relation, $name) = explode('.', $name, 2);

        $query->has($relation, '>=', 1, $boolean, function ($q) use ($name, $value, $regex) {
            $this->search($q, $name, $value, $regex, 'and');
        });
    }

    /**
     * Searches for the pivot column.
     *
     * @param mixed  $query
     * @param string $name Column name
     * @param string $value
     * @param bool   $regex
     * @param string $boolean
     */
    protected function searchPivot($query, $name, $value, $regex, $boolean = 'or')
    {
        list(, $name) = explode('.', $name, 2);

        $name = $this->qualifyPivotColumn($name);

        if ($regex) {
            $query->where($name, 'REGEXP', $value, $boolean);
        } else {
            $query->where($name, 'LIKE', "%{$value}%", $boolean);
        }
    }

    /**
     * @inheritdoc
     */
    protected function order($query, $name, $dir)
    {
        if ($this->isPivot($name)) {
            $this->orderPivot($query, $name, $dir);
        } elseif ($this->isRelated($query->getModel(), $name)) {
            $this->orderRelated($query, $name, $dir);
        } else {
            $this->traitOrder($query, $name, $dir);
        }
    }

    /**
     * Orders the related column.
     *
     * @param mixed  $query
     * @param string $name Column name
     * @param string $dir
     */
    protected function orderRelated($query, $name, $dir)
    {
        $model = $query->getModel();

        order:

        list($relation, $name) = explode('.', $name, 2);

        $relation = $model->{$relation}();

        $this->joinRelated($query, $relation);

        $model = $relation->getRelated();

        if ($this->isRelated($model, $name)) {
            goto order;
        } else {
            $query->orderBy($this->qualifyJoinColumn($model, $this->resolveJsonColumn($name)), $dir);
        }
    }

    protected function getJoinAlias($model)
    {
        return "{$model->getTable()}_join";
    }

    protected function qualifyJoinColumn($model, $name)
    {
        return "{$this->getJoinAlias($model)}.{$name}";
    }

    /**
     * Orders the pivot column.
     *
     * @param mixed  $query
     * @param string $name Column name
     * @param string $dir
     */
    protected function orderPivot($query, $name, $dir)
    {
        list(, $name) = explode('.', $name, 2);

        $query->orderBy($this->qualifyPivotColumn($name), $dir);
    }

    /**
     * Checks soft deletes on the model.
     *
     * @param Model $model
     * @return bool
     */
    protected function checkSoftDeletes(Model $model)
    {
        if (in_array(SoftDeletes::class, class_uses($model))) {
            return $model->getQualifiedDeletedAtColumn();
        } else {
            return false;
        }
    }

    /**
     * Joins the relation.
     *
     * @param mixed $query
     * @param mixed $model
     */
    protected function joinRelated($query, $model)
    {
        switch (true) {
            case $model instanceof BelongsToMany:
                $pivotTb = $model->getTable();
                $pivotPk = $model->getExistenceCompareKey();
                $pivotFk = $model->getQualifiedParentKeyName();
                $this->join($query, $pivotTb, $pivotPk, $pivotFk);

                $related = $model->getRelated();
                $table = $related->getTable();
                $alias = $this->getJoinAlias($related);
                $foreign = sprintf('%s.%s', $pivotTb, $related->getForeignKey());
                $other = $this->qualifyJoinColumn($related, $related->getKeyName());
                $softDeletes = $this->checkSoftDeletes($related);
                $this->join($query, "$table as $alias", $foreign, $other, $softDeletes);
                break;
            case $model instanceof HasOneOrMany:
                $related = $model->getRelated();
                $table = $related->getTable();
                $alias = $this->getJoinAlias($related);
                $foreign = $this->qualifyJoinColumn($related, $model->getForeignKeyName());
                $other = $model->getQualifiedParentKeyName();
                $softDeletes = $this->checkSoftDeletes($related);
                $this->join($query, "$table as $alias", $foreign, $other, $softDeletes);
                break;
            case $model instanceof BelongsTo:
                $related = $model->getRelated();
                $table = $related->getTable();
                $alias = $this->getJoinAlias($related);
                $foreign = $model->getQualifiedForeignKeyName();
                $other = $this->qualifyJoinColumn($related, $model->getOwnerKeyName());
                $softDeletes = $this->checkSoftDeletes($related);
                $this->join($query, "$table as $alias", $foreign, $other, $softDeletes);
                break;
            default:
                throw new RuntimeException('Relation ['.get_class($model).'] is not yet supported.');
        }
    }

    /**
     * Performs join.
     *
     * @param string $table
     * @param string $foreign
     * @param string $other
     * @param string|false $softDeletes
     * @param string $type
     */
    protected function join($query, $table, $foreign, $other, $softDeletes = false)
    {
        $joins = [];
        foreach ((array) $query->getQuery()->joins as $key => $join) {
            $joins[] = $join->table;
        }

        if (!in_array($table, $joins)) {
            $query->leftJoin($table, $foreign, $other);
        }

        if (false !== $softDeletes) {
            $query->whereNull($softDeletes);
        }
    }

    /**
     * Indicates if the column is for a relationship.
     *
     * @param Model $model
     * @param string $name Column name
     * @return bool
     */
    protected function isRelated(Model $model, $name)
    {
        list($relation,) = explode('.', $name);

        if (method_exists($model, $relation)) {
            return $model->{$relation}() instanceof Relation;
        } else {
            return false;
        }
    }

    /**
     * Indicates if the column is for a pivot accessor.
     *
     * @param string $name Column name
     * @return bool
     */
    protected function isPivot($name)
    {
        list($accessor,) = explode('.', $name);

        if ($this->source instanceof BelongsToMany) {
            return $accessor === $this->source->getPivotAccessor();
        } else {
            return false;
        }
    }
}
