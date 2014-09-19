<?php
# This file is part of agileMantis.
#
# Developed by: 
# gadiv GmbH
# Bövingen 148
# 53804 Much
# Germany
#
# Email: agilemantis@gadiv.de
#
# Copyright (C) 2012-2014 gadiv GmbH 
#
# agileMantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with agileMantis. If not, see <http://www.gnu.org/licenses/>.



echo '	<VelocityStoryPointsDeveloper>
		<entry type="SprintCurrentDay" restStoryPoints="'.$developerSprintCurrentDayRest.
			'" splittedStoryPoints="'.$developerSprintCurrentDayWorkMoved.'" movedStoryPoints="'.
			$developerSprintCurrentDayStoryPointsMoved.'" />
		<entry type="SprintTotal" restStoryPoints="'.$developerSprintTotalRest.
			'" splittedStoryPoints="'.$developerSprintTotalWorkMoved.'" movedStoryPoints="'.
			$developerSprintTotalStoryPointsMoved.'" />
		<entry type="SprintReferenced" restStoryPoints="'.$developerSprintReferencedRest.
			'" splittedStoryPoints="'.$developerSprintReferencedWorkMoved.'" movedStoryPoints="'.
			$developerSprintReferencedStoryPointsMoved.'" />
		<entry type="SprintPrevious" restStoryPoints="'.$developerSprintPreviousRest.
			'" splittedStoryPoints="'.$developerSprintPreviousWorkMoved.'" movedStoryPoints="'.
			$developerSprintPreviousStoryPointsMoved.'" />
		<entry type="SprintAvarage" restStoryPoints="'.$developerSprintAvarageRest.
			'" splittedStoryPoints="'.$developerSprintAvarageWorkMoved.'" movedStoryPoints="'.
			$developerSprintAvarageStoryPointsMoved.'" />
	</VelocityStoryPointsDeveloper>
';
?>