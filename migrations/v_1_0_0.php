<?php
/**
* @package phpBB Extension - marttiphpbb posting template
* @copyright (c) 2014 marttiphpbb <info@martti.be>
* @license http://opensource.org/licenses/MIT
*/

namespace marttiphpbb\postingtemplate\migrations;

use phpbb\db\migration\migration;

class v_1_0_0 extends migration
{	
	public function update_schema()
	{
		return array(
			'add_columns'        => array(
				$this->table_prefix . 'forums'        => array(
					'forum_postingtemplate'    	=> array('MTEXT_UNI', ''),				
				),
			),	
		);
	}	
	
	public function revert_schema()
	{
		return array(
			'drop_columns'        => array(
				$this->table_prefix . 'forums'        => array(				
					'forum_postingtemplate',								
				),
			),
	   );
	}	
}
