<?php

namespace App\Observers;

use App\Projects;

class ProjectsObserver
{
    /**
     * Handle the projects "created" event.
     *
     * @param Projects $projects
     * @return void
     */
    public function created(Projects $projects)
    {
        //
    }

    /**
     * Handle the projects "updated" event.
     *
     * @param Projects $projects
     * @return void
     */
    public function updated(Projects $projects)
    {
        //
    }

    /**
     * Handle the projects "deleted" event.
     *
     * @param Projects $projects
     * @return void
     */
    public function deleted(Projects $projects)
    {
        //
    }

    /**
     * Handle the projects "restored" event.
     *
     * @param Projects $projects
     * @return void
     */
    public function restored(Projects $projects)
    {
        //
    }

    /**
     * Handle the projects "force deleted" event.
     *
     * @param Projects $projects
     * @return void
     */
    public function forceDeleted(Projects $projects)
    {
        //
    }

}
