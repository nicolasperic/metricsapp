<div class="row ">

    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Open Milestones</h6>
            </div>

            <div class="card-body">
                <ul>
                    @forelse ($project->getOpenSprints as $sprint)
                        <li>
                            <a href="{{ route('sprints.show',[$project->wikiname, $sprint->sprint_assembla_id]) }}">{{ $sprint->name}}</a> <?= $sprint->getFormattedPlannerType()?>
                        </li>


                    @empty
                        <p>No milestones assigned to this space yet.</p>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Team Members</h6>
            </div>

            <div class="card-body">
                <ul style="list-style: none;padding-left: 0px;">
                    @forelse ($project->assemblaUsers as $user)
                        <li style="width: 45%; float: left; margin: 5px;">
                            @if($user->picture)
                                <img class="rounded-circle" style="width: 32px; height: 32px;" src="{{$user->picture}}"/>
                            @else
                                <img class="rounded-circle" style="width: 32px; height: 32px;" src="https://assets3.assembla.com/assets/avatars/small/10-34646632626633326534663337306230663564393237353266396538633232383833626339353837396534323061616337666664633662376434376637303134.png"/>
                            @endif
                            {{ Helper::substrNameIf($user->name, 21) }}
                        </li>


                    @empty
                        <p>No assembla users imported yet.</p>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>

    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Closed Milestones</h6>
            </div>
            <div class="card-body">
                <ul>
                    @forelse ($project->getClosedSprints as $sprint)
                        <li>
                            <a href="{{ route('sprints.show',[$project->wikiname, $sprint->sprint_assembla_id]) }}">{{ $sprint->name}}</a>
                        </li>


                    @empty
                        <p>No milestones assigned to this space yet.</p>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">App Users</h6>
            </div>

            <div class="card-body">
                <ul>
                    @forelse ($project->users as $user)
                        <li>
                            {{ $user->name }}
                        </li>


                    @empty
                        <p>No users assigned to this space yet.</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>