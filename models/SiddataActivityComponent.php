<?php

/**
 * Class SiddataActivityComponent
 * Abstract base class activity-attached components
 */
abstract class SiddataActivityComponent
{
    protected $id;
    protected $activity;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return SiddataActivity
     */
    public function getActivity(): SiddataActivity
    {
        return $this->activity;
    }

    /**
     * @param SiddataActivity $activity
     */
    public function setActivity(SiddataActivity& $activity)
    {
        $this->activity = $activity;
    }
}
