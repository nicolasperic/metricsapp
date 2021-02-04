# First Steps

---

- [Getting Started](#getting-started)
    - [Importing your projects](#importing-projects)
    - [Configuring your projects](#configuring-projects)

<a name="getting-started"></a>
## Getting Started

Once you've completed registration and added your Assembla Keys you can start setting up your projects by importing and configuring a few options.

<a name="importing-projects"></a>
### Importing your projects
The application needs to get the required information from Assembla to start gathering amazing metrics : )

So the first step you'll follow is importing your projects from the <a href="{{ route('projects.index') }}" target="_blank">projects index page</a> by selecting the <span class="btn btn-success">Sync pojects</span> option.
A background process will import the available spaces for the added Assembla credentials.


<a name="configuring-projects"></a>
<br>

### Configuring your Projects

Access project settings on the project view page by selecting the "Settings" button.

The following settings will be available for each project:

1. **<u>Starred</u>:** when setting a project as starred you'll have quick access on the left menu.
This will also include the project current milestone on the current milestones view and on the starred menu section for milestones.

2. **<u>Auto Sync</u>:** the project milestones and current milestone data will get synced dynamically. Milestone tickets, relations and tracked time will be synced for the current milestone only.

3. **<u>Shared</u>:** when a space is used at a company level (cross teams) you might want to filter the report hours by a specific group of users (your team) and avoid extra hours that are not of your interest.
Setting a project as shared will impact the Weekly Report and the Hours by User Report. When selecting a shared project from the projects dropdown, a users dropdown will appear in which you might filter tracked time by user (if no selection is made, no filtering will occurr).

4. **<u>Estimate by</u>:** this option should match the space Estimate configuration under tickets settings in Assembla. The possibles values are:
- Points: when estimating with story points
- Time: when estimating with hours
- Size: when estimating with small / medium and large sizes.

This option will mostly impact labels on the frontend i.e "Story Points" vs "Hours".

<br>
On the **currents sprint view** the Estimate by option will be used to group sprints and sum the total stats for each estimate type.
You'll have as many totals sections as different estimate types, meaning if you have 5 current milestones, 3 with Points and 2 with Time you'll have a Points Total and a Time Total.

Note: when using the Size option no average values will be calculated
