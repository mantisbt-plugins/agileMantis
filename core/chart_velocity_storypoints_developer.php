<?php
	# agileMantis - makes Mantis ready for Scrum

	# agileMantis is free software: you can redistribute it and/or modify
	# it under the terms of the GNU General Public License as published by
	# the Free Software Foundation, either version 2 of the License, or
	# (at your option) any later version.
	#
	# agileMantis is distributed in the hope that it will be useful,
	# but WITHOUT ANY WARRANTY; without even the implied warranty of
	# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	# GNU General Public License for more details.
	#
	# You should have received a copy of the GNU General Public License
	# along with agileMantis. If not, see <http://www.gnu.org/licenses/>.

echo '	<VelocityStoryPointsDeveloper>
		<entry type="SprintCurrentDay" restStoryPoints="'.$developerSprintCurrentDayRest.'" splittedStoryPoints="'.$developerSprintCurrentDayWorkMoved.'" movedStoryPoints="'.$developerSprintCurrentDayStoryPointsMoved.'" />
		<entry type="SprintTotal" restStoryPoints="'.$developerSprintTotalRest.'" splittedStoryPoints="'.$developerSprintTotalWorkMoved.'" movedStoryPoints="'.$developerSprintTotalStoryPointsMoved.'" />
		<entry type="SprintReferenced" restStoryPoints="'.$developerSprintReferencedRest.'" splittedStoryPoints="'.$developerSprintReferencedWorkMoved.'" movedStoryPoints="'.$developerSprintReferencedStoryPointsMoved.'" />
		<entry type="SprintPrevious" restStoryPoints="'.$developerSprintPreviousRest.'" splittedStoryPoints="'.$developerSprintPreviousWorkMoved.'" movedStoryPoints="'.$developerSprintPreviousStoryPointsMoved.'" />
		<entry type="SprintAvarage" restStoryPoints="'.$developerSprintAvarageRest.'" splittedStoryPoints="'.$developerSprintAvarageWorkMoved.'" movedStoryPoints="'.$developerSprintAvarageStoryPointsMoved.'" />
	</VelocityStoryPointsDeveloper>
';
?>