<?php

namespace MUtil\Batch;

class Progress
{

    private int $count;
    private bool $finish = false;
    private float $percent;
    private int $startTime = 0;
    private int $step = 0;

    public function __construct(int $count = 0)
    {
        $this->count = $count;
    }

    public function advance(int $steps = 1)
    {
        $this->setProgress($this->step + $steps);
    }

    public function finish(): void
    {
        $this->finish = true;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getEstimated(): float
    {
        if (!$this->step) {
            return 0;
        }

        return round((time() - $this->startTime) / $this->step * $this->count);
    }

    /**
     * @return float
     */
    public function getPercent(): float
    {
        return $this->percent;
    }

    public function getRemaining(): float
    {
        if (!$this->step) {
            return 0;
        }

        return round((time() - $this->startTime) / $this->step * ($this->count - $this->step));
    }

    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * Returns an iterator that will automatically update the progress bar when iterated.
     *
     * @param int|null $max Number of steps to complete the bar (0 if indeterminate), if null it will be inferred from $iterable
     */
    public function iterate(iterable $iterable, int $max = null): iterable
    {
        $this->count = ($max ?? (is_countable($iterable) ? \count($iterable) : 0));

        foreach ($iterable as $key => $value) {
            yield $key => $value;

            $this->advance();
        }

        $this->finish();
    }

    public function isFinished(): bool
    {
        return $this->finish;
    }

    public function reset(): void
    {
        $this->finish = false;
        $this->setProgress(0);
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function setProgress(int $step): void
    {
        if ($this->count && $step > $this->count) {
            $this->count = $step;
        } elseif ($step < 0) {
            $step = 0;
        }

        $this->step = $step;

        $this->percent = $this->count ? (float) $this->step / $this->count : 0;
    }

    public function start(): void
    {
        $this->startTime = time();
        $this->step = 0;
    }
}
