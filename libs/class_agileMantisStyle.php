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
	
	class gadiv_agileMantisStyle extends gadiv_commonlib {
	
		# collect all status names and colors according to task status
		function agileMantisStatusColorsAndNames($task_status){
			switch($task_status){
				default:
				case '1':
					$status['name']		= 	plugin_lang_get( 'status_new' );
					$status['color']	= 	'#FCBDBD';
				break;
				case '2':
					$status['name']		= 	plugin_lang_get( 'status_assigned' );
					$status['color']	= 	'#C2DFFF';
				break;
				case '3':
					$status['name']		= 	plugin_lang_get( 'status_confirmed' );
					$status['color']	= 	'#FFF494';
				break;
				case '4':
					$status['name'] 	= 	plugin_lang_get( 'status_resolved' );
					$status['color'] 	= 	'#D2F5B0';

				break;
				case '5':
					$status['name'] 	= 	plugin_lang_get( 'status_closed' );
					$status['color']	= 	'#c9ccc4';
				break;
			}
			return $status;
		}
	}
?>