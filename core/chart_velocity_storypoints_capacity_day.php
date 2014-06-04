<?php
echo '
	<VelocityStoryPointsCapacityDay>
		<entry type="SprintCurrentDay" restStoryPoints="'.$capacityDaySprintCurrentDayRest.'" splittedStoryPoints="'.$capacityDaySprintCurrentDayWorkMoved.'" movedStoryPoints="'.$capacityDaySprintCurrentDayStoryPointsMoved.'" />
		<entry type="SprintTotal" restStoryPoints="'.$capacityDaySprintTotalRest.'" splittedStoryPoints="'.$capacityDaySprintTotalWorkMoved.'" movedStoryPoints="'.$capacityDaySprintTotalStoryPointsMoved.'" />
		<entry type="SprintReferenced" restStoryPoints="'.$capacityDaySprintReferencedRest.'" splittedStoryPoints="'.$capacityDaySprintReferencedWorkMoved.'" movedStoryPoints="'.$capacityDaySprintReferencedStoryPointsMoved.'" />
		<entry type="SprintPrevious" restStoryPoints="'.$capacityDaySprintPreviousRest.'" splittedStoryPoints="'.$capacityDaySprintPreviousWorkMoved.'" movedStoryPoints="'.$capacityDaySprintPreviousStoryPointsMoved.'" />
		<entry type="SprintAvarage" restStoryPoints="'.$capacityDaySprintAvarageRest.'" splittedStoryPoints="'.$capacityDaySprintAvarageWorkMoved.'" movedStoryPoints="'.$capacityDaySprintAvarageStoryPointsMoved.'" />
	</VelocityStoryPointsCapacityDay>
';
?>