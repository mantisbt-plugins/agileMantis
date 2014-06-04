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

echo '	<VelocityStoryPointsDeveloperDay>
		<entry type="SprintCurrentDay" restStoryPoints="'.$developerDaySprintCurrentDayRest.'" splittedStoryPoints="'.$developerDaySprintCurrentDayWorkMoved.'" movedStoryPoints="'.$developerDaySprintCurrentDayStoryPointsMoved.'" />
		<entry type="SprintTotal" restStoryPoints="'.$developerDaySprintTotalRest.'" splittedStoryPoints="'.$developerDaySprintTotalWorkMoved.'" movedStoryPoints="'.$developerDaySprintTotalStoryPointsMoved.'" />
		<entry type="SprintReferenced" restStoryPoints="'.$developerDaySprintReferencedRest.'" splittedStoryPoints="'.$developerDaySprintReferencedWorkMoved.'" movedStoryPoints="'.$developerDaySprintReferencedStoryPointsMoved.'" />
		<entry type="SprintPrevious" restStoryPoints="'.$developerDaySprintPreviousRest.'" splittedStoryPoints="'.$developerDaySprintPreviousWorkMoved.'" movedStoryPoints="'.$developerDaySprintPreviousStoryPointsMoved.'" />
		<entry type="SprintAvarage" restStoryPoints="'.$developerDaySprintAvarageRest.'" splittedStoryPoints="'.$developerDaySprintAvarageWorkMoved.'" movedStoryPoints="'.$developerDaySprintAvarageStoryPointsMoved.'" />
	</VelocityStoryPointsDeveloperDay>
';
?>