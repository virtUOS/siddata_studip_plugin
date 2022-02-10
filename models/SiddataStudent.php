<?php

/**
 * Class SiddataStudent
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 */
class SiddataStudent
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var SiddataRecommender[]
     */
    private $recommenders;
    /**
     * @var string
     */
    private $user_origin_id;

    /**
     * @var SiddataActivity[] alert or teaser activities
     */
    private $activities;

    /**
     * SiddataStudent constructor.
     * @param string $id
     * @param string $user_origin_id
     * @param SiddataRecommender[] $recommenders
     * @param SiddataActivity[] $activities
     */
    public function __construct($id, $user_origin_id, $recommenders=[], $activities=[]) {
        $this->id               = $id;
        $this->user_origin_id   = $user_origin_id;
        $this->recommenders     = $recommenders;
        $this->activities       = $activities;

        foreach ($this->recommenders as $recommender) {
            $recommender->setStudent($this);
        }
    }

    /**
     * @return mixed
     */
    public function getUserOriginId()
    {
        return $this->user_origin_id;
    }

    /**
     * @return SiddataRecommender[]
     */
    public function getRecommenders() {
        return $this->recommenders;
    }

    /**
     * @return SiddataActivity[]
     */
    public function getActivities() {
        return $this->activities;
    }

    /**
     * @return SiddataActivity[]
     */
    public function getAllActivities() {
        $activities = $this->getActivities();
        foreach ($this->getRecommenders() as $recommender) {
            foreach ($recommender->getActivities() as $activity) {
                $activities[] = $activity;
            }
        }

        return $activities;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param SiddataRecommender $recommender
     */
    public function addRecommender(SiddataRecommender& $recommender) {
        $recommender->setStudent($this);
        if (!in_array($recommender, $this->recommenders)) {
            $this->recommenders[] = $recommender;
        }
    }

    /**
     * @param SiddataActivity $activity
     */
    public function addActivity(SiddataActivity& $activity) {
        if (!in_array($activity, $this->activities)) {
            $this->activities[] = $activity;
        }
    }
}
