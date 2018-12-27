<?php

namespace Ninoman\LaravelSortable;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * This property determines initial value of sorting
     *
     * @var string
     */
    public $startSortingFrom = 1;

    /**
     * This property determines column which should be used for sorting
     *
     * @var string
     */
    public $sortIndexColumn = 'sort_index';

    /**
     * This property determines a column by which model sorting should be grouped (`null` stands for no grouping)
     * if you want a grouped sorting, assign a column name, by which you want a grouped sorting
     *
     * @var string
     */
    public $parentColumn = null;

    /**
     * This property determines if the model should be assign an order on creating or not
     * set it to `false` to turn off that functionality
     *
     * @var bool
     */
    public $setSortIndexOnCreating = true;

    /**
     * This property determines if other models should be resorted if this model's sorting index was change
     * we have used it in our functions @see
     *
     * @var bool
     */
    public $resortOthers = true;

    public static function swapSort(self $modelOne, self $modelTwo): void
    {
        $sortingColumn = $modelOne->sortIndexColumn;
        $indexOfOne = $modelOne->$sortingColumn;
        $indexOfTwo = $modelTwo->$sortingColumn;

        $modelOne->updateSortingWithoutResorting($indexOfTwo);
        $modelTwo->updateSortingWithoutResorting($indexOfOne);
    }

    /**
     * This method is used to get next sort index for a new model
     *
     * @return int
     */
    public function getNextSortIndex(): int
    {
        $builder = self::query();

        if ($this->parentColumn) {
            $builder->where($this->parentColumn, $this->{$this->parentColumn});
        }

        return ($builder->latest($this->sortIndexColumn)->first()->{$this->sortIndexColumn} ?? $this->startSortingFrom - 1) + 1;
    }

    protected static function bootSortable()
    {
        self::observe(SortableObserver::class);
    }

    public function scopeSameParentChild(Builder $builder): Builder
    {
        return $this->parentColumn ? $builder->where($this->parentColumn, $this->{$this->parentColumn}) : $builder;
    }

    /**
     * An universal getter mutator for sorting column
     *
     * @return int
     */
    public function getSortIndexAttribute(): int
    {
        return $this->{$this->sortIndexColumn};
    }

    /**
     * An universal getter mutator for sorting column
     *
     * @param int $sortIndex
     * @return void
     */
    public function setSortIndexAttribute(int $sortIndex): void
    {
        $this->{$this->sortIndexColumn} = $sortIndex;
    }

    /**
     * Use this scope to access your models sorted
     *
     * @param Builder $builder
     * @param string $direction
     * @return Builder
     */
    public function scopeSorted(Builder $builder, string $direction = 'asc'): Builder
    {
        return $builder->orderBy($this->sortIndexColumn, $direction);
    }

    /**
     * Use this scope to access your models sorted by desc
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeSortedDesc(Builder $builder): Builder
    {
        return $builder->sorted('desc');
    }

    /**
     * This method will move your model up in sorted list, if it's not already there
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function moveSortIndexUp(): int
    {
        // if model is already on top
        if ($this->{$this->sortIndexColumn} == $this->startSortingFrom) {
            return $this->{$this->sortIndexColumn};
        }

        $this->update([
            $this->sortIndexColumn => $this->{$this->sortIndexColumn} - 1
        ]);

        return $this->{$this->sortIndexColumn};
    }

    /**
     * This method will move your model down in sorted list, if it's not already there
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function moveSortIndexDown(): int
    {
        // if model is already on bottom
        if ($this->{$this->sortIndexColumn} == $this->getNextSortIndex() - 1) {
            return $this->{$this->sortIndexColumn};
        }

        $this->update([
            $this->sortIndexColumn => $this->{$this->sortIndexColumn} + 1
        ]);

        return $this->{$this->sortIndexColumn};
    }

    /**
     * This method will move your model onto bottom of sorting
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function toSortingTop(): int
    {
        $this->update([
            $this->sortIndexColumn => $this->startSortingFrom
        ]);

        return $this->{$this->sortIndexColumn};
    }

    /**
     * This method will move your model onto top of sorting
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function toSortingBottom(): int
    {
        $this->update([
            $this->sortIndexColumn => $this->getNextSortIndex() - 1
        ]);

        return $this->{$this->sortIndexColumn};
    }

    public function turnOffResortingOfOthers(): void
    {
        $this->resortOthers = false;
    }

    public function turnOnResortingOfOthers(): void
    {
        $this->resortOthers = true;
    }

    public function updateSortingWithoutResorting(int $index): void
    {
        $this->turnOffResortingOfOthers();
        $this->update([
            $this->sortIndexColumn => $index
        ]);
        $this->turnOnResortingOfOthers();
    }
}