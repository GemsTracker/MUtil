<?php

namespace MUtil\Batch;

class Progress
{

    private bool $finish = false;
    private int $max;
    private float $percent;
    private int $startTime = 0;
    private int $step = 0;

    public function __construct(int $max = 0)
    {
        $this->max = $max;
    }

    public function advance(int $steps = 1)
    {
        $this->setProgress = ($this->step + $steps);
    }

    public function finish(): void
    {
        $this->finish = true;
    }

    public function getEstimated(): float
    {
        if (!$this->step) {
            return 0;
        }

        return round((time() - $this->startTime) / $this->step * $this->max);
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

        return round((time() - $this->startTime) / $this->step * ($this->max - $this->step));
    }


    public function start(): void
    {
        $this->startTime = time();
        $this->step = 0;
    }

    public function setProgress(int $step)
    {
        if ($this->max && $step > $this->max) {
            $this->max = $step;
        } elseif ($step < 0) {
            $step = 0;
        }

        $this->percent = $this->max ? (float) $this->step / $this->max : 0;
    }

    /**
     * Returns an iterator that will automatically update the progress bar when iterated.
     *
     * @param int|null $max Number of steps to complete the bar (0 if indeterminate), if null it will be inferred from $iterable
     */
    public function iterate(iterable $iterable, int $max = null): iterable
    {
        $this->max = ($max ?? (is_countable($iterable) ? \count($iterable) : 0));

        foreach ($iterable as $key => $value) {
            yield $key => $value;

            $this->advance();
        }

        $this->finish();
    }
}
