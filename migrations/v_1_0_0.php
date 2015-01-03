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
	public function update_data()
	{
		return array(
			array('config_text.add', array('postingtemplate', array())),
		);
	}
}
