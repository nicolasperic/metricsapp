<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('home'));
});

//Projects
Breadcrumbs::for('projects', function ($trail) {
    $trail->parent('home');
    $trail->push('Spaces', route('projects.index'));
});


// Home > Projects > [Project]
Breadcrumbs::for('projects.show', function ( $trail, $project) {
    $trail->parent('projects');
    $trail->push($project->name, route('projects.show', $project->wikiname));
});

// Home > Projects > [Project] > [Sprint]
Breadcrumbs::for('sprints.show', function ( $trail, $project, $sprint) {
    $trail->parent('projects.show', $project);
    $trail->push($sprint->name, route('sprints.show', [$project->wikiname, $sprint->id]));
});

//Home > Projects > Sprints
Breadcrumbs::for('sprints', function ($trail) {
    $trail->parent('projects');
    $trail->push('Milestones', route('sprints.index'));
});

